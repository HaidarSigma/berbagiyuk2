<?php

namespace App\Http\Controllers;

use App\Models\LogAktivitas;
use App\Models\Pengambilan;
use App\Models\Tugas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PetugasController extends Controller
{
    private const REL_AMBIL = ['barang.kategori', 'barang.user'];
    private const REL_KIRIM = ['permintaan.barang.kategori', 'permintaan.penerima'];

    // Barang dari satu donasi diambil dalam satu kunjungan
    private function kelompok($koleksi)
    {
        return $koleksi->groupBy(fn ($p) => $p->barang->donasi_id ?? 'b'.$p->barang_id);
    }

    public function dashboard()
    {
        $id    = auth()->id();
        $bulan = fn ($q, $kolom) => $q->whereMonth($kolom, now()->month)->whereYear($kolom, now()->year);

        return view('petugas.dashboard', [
            'ambilBaru'  => Pengambilan::where('status', 'baru')->count(),
            'ambilSaya'  => Pengambilan::where('petugas_id', $id)->where('status', 'ditugaskan')->count(),
            'kirimBaru'  => Tugas::where('status', 'baru')->count(),
            'kirimSaya'  => Tugas::where('petugas_id', $id)->where('status', 'ditugaskan')->count(),
            'ambilBulan' => $bulan(Pengambilan::where('petugas_id', $id)->where('status', 'selesai'), 'diambil_at')->count(),
            'kirimBulan' => $bulan(Tugas::where('petugas_id', $id)->where('status', 'selesai'), 'selesai_at')->count(),
        ]);
    }

    // ---------- Pengambilan: donatur ke posko ----------
    public function pengambilan()
    {
        return view('petugas.pengambilan', [
            'kelompok' => $this->kelompok(Pengambilan::with(self::REL_AMBIL)
                ->where('petugas_id', auth()->id())->where('status', 'ditugaskan')->latest('ditugaskan_at')->get()),
            'tersedia' => $this->kelompok(Pengambilan::with(self::REL_AMBIL)
                ->where('status', 'baru')->oldest()->get()),
        ]);
    }

    public function ambilPengambilan(Request $r)
    {
        $r->validate(['id' => 'required|array|min:1', 'id.*' => 'integer']);

        $jumlah = DB::transaction(function () use ($r) {
            $n = 0;
            foreach (Pengambilan::whereIn('id', $r->id)->where('status', 'baru')->lockForUpdate()->get() as $p) {
                $p->update(['status' => 'ditugaskan', 'petugas_id' => auth()->id(), 'ditugaskan_at' => now()]);
                $n++;
            }
            return $n;
        });

        if ($jumlah === 0) {
            return back()->with('status', 'Tugas ini sudah diambil petugas lain.');
        }

        LogAktivitas::catat('pengambilan_ditugaskan', $jumlah.' barang | Pengambilan #'.implode(', #', $r->id));

        return back()->with('status', "{$jumlah} barang masuk ke tugasmu. Alamat donatur sekarang terlihat.");
    }

    public function lepasPengambilan(Pengambilan $pengambilan)
    {
        abort_unless($pengambilan->petugas_id === auth()->id() && $pengambilan->status === 'ditugaskan', 403);

        $pengambilan->update(['status' => 'baru', 'petugas_id' => null, 'ditugaskan_at' => null]);
        LogAktivitas::catat('pengambilan_dilepas', 'Pengambilan #'.$pengambilan->id);

        return back()->with('status', 'Tugas dilepas dan kembali ke daftar tersedia.');
    }

    // Satu kunjungan: beberapa barang diambil sekaligus, tiap barang dikonfirmasi lewat label QR
    public function selesaiPengambilan(Request $r)
    {
        $r->validate([
            'pengambilan_id'   => 'required|array|min:1',
            'pengambilan_id.*' => 'integer',
            'token_barang'     => 'required|array|min:1',
            'token_barang.*'   => 'string|max:60',
            'foto'             => 'required|image|mimes:jpg,jpeg,png,webp|max:4096',
        ], [
            'pengambilan_id.required' => 'Centang minimal satu barang yang diambil.',
            'token_barang.required'   => 'Pindai label QR pada setiap barang yang diambil.',
        ]);

        $ids    = array_unique($r->pengambilan_id);
        $daftar = Pengambilan::with('barang')->whereIn('id', $ids)
            ->where('petugas_id', auth()->id())->where('status', 'ditugaskan')->get();

        abort_if($daftar->count() !== count($ids), 403);

        $dipindai = array_map(fn ($t) => Str::upper(trim($t)), $r->token_barang);

        $belum = $daftar->filter(function ($p) use ($dipindai) {
            foreach ($dipindai as $x) {
                if (hash_equals((string) $p->barang->token, $x)) {
                    return false;
                }
            }
            return true;
        });

        if ($belum->isNotEmpty()) {
            LogAktivitas::catat('token_barang_salah', 'Pengambilan #'.$belum->pluck('id')->implode(', #'));

            return back()->withErrors([
                'token_barang' => 'Label belum dipindai atau tidak cocok untuk: '.$belum->map(fn ($p) => $p->barang->judul)->implode(', ').'.',
            ]);
        }

        $path = $r->file('foto')->store('bukti');

        DB::transaction(function () use ($daftar, $path) {
            foreach ($daftar as $p) {
                $p->update(['status' => 'selesai', 'foto_ambil' => $path, 'diambil_at' => now()]);
                $p->barang->update(['status' => 'tersedia']);   // sekarang tersimpan di posko dan tampil untuk penerima
            }
        });

        LogAktivitas::catat('barang_diambil', $daftar->count().' barang | Pengambilan #'.$daftar->pluck('id')->implode(', #'));

        return redirect('/petugas/riwayat')->with('status', "{$daftar->count()} barang tercatat di Riwayat Pengambilan dan kini tersimpan di posko.");
    }

    // ---------- Pengiriman: posko ke penerima ----------
    public function pengiriman()
    {
        return view('petugas.pengiriman', [
            'saya'     => Tugas::with(self::REL_KIRIM)->where('petugas_id', auth()->id())->where('status', 'ditugaskan')->oldest('ditugaskan_at')->get(),
            'tersedia' => Tugas::with(self::REL_KIRIM)->where('status', 'baru')->oldest()->get(),
        ]);
    }

    public function ambilPengiriman(Tugas $tugas)
    {
        $ok = DB::transaction(function () use ($tugas) {
            $t = Tugas::lockForUpdate()->find($tugas->id);
            if (! $t || $t->status !== 'baru') {
                return false;
            }
            $t->update(['status' => 'ditugaskan', 'petugas_id' => auth()->id(), 'ditugaskan_at' => now()]);
            return true;
        });

        if (! $ok) {
            return back()->with('status', 'Tugas ini sudah diambil petugas lain.');
        }

        LogAktivitas::catat('pengiriman_ditugaskan', 'Tugas #'.$tugas->id);

        return back()->with('status', 'Tugas pengiriman diambil. Alamat penerima sekarang terlihat.');
    }

    public function lepasPengiriman(Tugas $tugas)
    {
        abort_unless($tugas->petugas_id === auth()->id() && $tugas->status === 'ditugaskan', 403);

        $tugas->update(['status' => 'baru', 'petugas_id' => null, 'ditugaskan_at' => null]);
        LogAktivitas::catat('pengiriman_dilepas', 'Tugas #'.$tugas->id);

        return back()->with('status', 'Tugas dilepas dan kembali ke daftar tersedia.');
    }

    public function selesaiPengiriman(Request $r, Tugas $tugas)
    {
        abort_unless($tugas->petugas_id === auth()->id() && $tugas->status === 'ditugaskan', 403);

        $r->validate([
            'kode'    => 'required|digits:6',
            'foto'    => 'required|image|mimes:jpg,jpeg,png,webp|max:4096',
            'catatan' => 'nullable|string|max:300',
        ], [
            'kode.digits' => 'Kode serah terima terdiri dari 6 angka.',
        ]);

        if (! hash_equals((string) $tugas->kode_serah, (string) $r->kode)) {
            $tugas->increment('kode_salah');
            LogAktivitas::catat('kode_serah_salah', 'Tugas #'.$tugas->id);
            return back()->withErrors(['kode' => 'Kode serah terima salah. Minta penerima memeriksa kodenya di halaman Permintaan Saya.']);
        }

        $path = $r->file('foto')->store('bukti');

        DB::transaction(function () use ($tugas, $path, $r) {
            $tugas->update(['status' => 'selesai', 'foto_serah' => $path, 'catatan' => $r->catatan, 'selesai_at' => now()]);
            $tugas->permintaan->barang->update(['status' => 'selesai']);
        });
        LogAktivitas::catat('tugas_selesai', 'Tugas #'.$tugas->id);

        return redirect('/petugas/riwayat?tab=pengiriman')->with('status', 'Pengantaran selesai. Terima kasih!');
    }

    // ---------- Riwayat ----------
    public function riwayat(Request $r)
    {
        $tab = $r->query('tab') === 'pengiriman' ? 'pengiriman' : 'pengambilan';

        if ($tab === 'pengambilan') {
            $data = Pengambilan::with(self::REL_AMBIL)->where('petugas_id', auth()->id())->where('status', 'selesai')
                ->latest('diambil_at')->take(300)->get()
                ->groupBy(fn ($p) => $p->diambil_at->toDateString());
        } else {
            $data = Tugas::with(self::REL_KIRIM)->where('petugas_id', auth()->id())->where('status', 'selesai')
                ->latest('selesai_at')->paginate(15)->withQueryString();
        }

        return view('petugas.riwayat', ['tab' => $tab, 'data' => $data]);
    }

    // Foto bukti serah terima: hanya admin atau petugas yang mengerjakannya
    public function bukti(Tugas $tugas, string $jenis)
    {
        abort_unless(in_array($jenis, ['ambil', 'serah']), 404);

        $u = auth()->user();
        abort_unless($u->role === 'admin' || $tugas->petugas_id === $u->id, 403);

        $path = $jenis === 'ambil' ? $tugas->foto_ambil : $tugas->foto_serah;
        abort_unless($path, 404);

        if ($u->role === 'admin') {
            LogAktivitas::catat('bukti_dibuka', 'Tugas #'.$tugas->id.' | '.$jenis);
        }

        return Storage::disk('local')->response($path);
    }

    // Foto bukti pengambilan dari donatur
    public function buktiPengambilan(Pengambilan $pengambilan)
    {
        $u = auth()->user();
        abort_unless($u->role === 'admin' || $pengambilan->petugas_id === $u->id, 403);
        abort_unless($pengambilan->foto_ambil, 404);

        if ($u->role === 'admin') {
            LogAktivitas::catat('bukti_dibuka', 'Pengambilan #'.$pengambilan->id);
        }

        return Storage::disk('local')->response($pengambilan->foto_ambil);
    }
}