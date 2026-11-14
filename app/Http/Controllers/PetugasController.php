<?php

namespace App\Http\Controllers;

use App\Models\LogAktivitas;
use App\Models\Tugas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PetugasController extends Controller
{
    private const RELASI = ['permintaan.barang.kategori', 'permintaan.barang.user', 'permintaan.penerima'];

    private function mine()
    {
        return Tugas::with(self::RELASI)->where('petugas_id', auth()->id());
    }

    private function milikku(Tugas $tugas): void
    {
        abort_unless($tugas->petugas_id === auth()->id(), 403);
    }

    public function dashboard()
    {
        $hitung = fn () => Tugas::where('petugas_id', auth()->id());

        return view('petugas.dashboard', [
            'tersedia'  => Tugas::where('status', 'baru')->count(),
            'menunggu'  => $hitung()->where('status', 'ditugaskan')->count(),
            'antar'     => $hitung()->where('status', 'diambil')->count(),
            'bulanIni'  => $hitung()->where('status', 'selesai')->whereMonth('selesai_at', now()->month)->whereYear('selesai_at', now()->year)->count(),
            'siapAntar' => $this->mine()->where('status', 'diambil')->latest('diambil_at')->take(3)->get(),
        ]);
    }

    // ---------- Pengambilan ----------
    public function pengambilan()
    {
        // Tugasku yang belum diambil, dikelompokkan per donasi (satu kunjungan)
        $kelompok = $this->mine()->where('status', 'ditugaskan')->latest('ditugaskan_at')->get()
            ->groupBy(fn ($t) => $t->permintaan->barang->donasi_id ?? 'b'.$t->permintaan->barang_id);

        return view('petugas.pengambilan', [
            'kelompok' => $kelompok,
            'tersedia' => Tugas::with(self::RELASI)->where('status', 'baru')->oldest()->get(),
        ]);
    }

    public function ambil(Tugas $tugas)
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

        LogAktivitas::catat('tugas_diambil', 'Tugas #'.$tugas->id);

        return back()->with('status', 'Tugas diambil. Alamat dan kontak donatur sekarang terlihat.');
    }

    public function lepas(Tugas $tugas)
    {
        $this->milikku($tugas);
        abort_unless($tugas->status === 'ditugaskan', 403);

        $tugas->update(['status' => 'baru', 'petugas_id' => null, 'ditugaskan_at' => null]);
        LogAktivitas::catat('tugas_dilepas', 'Tugas #'.$tugas->id);

        return back()->with('status', 'Tugas dilepas dan kembali ke daftar tersedia.');
    }

    // Satu kunjungan: beberapa barang diambil sekaligus dengan satu foto bukti
    public function diambil(Request $r)
    {
        $r->validate([
            'tugas_id'   => 'required|array|min:1',
            'tugas_id.*' => 'integer',
            'foto'       => 'required|image|mimes:jpg,jpeg,png,webp|max:4096',
        ], [
            'tugas_id.required' => 'Centang minimal satu barang yang diambil.',
        ]);

        $ids    = array_unique($r->tugas_id);
        $daftar = Tugas::whereIn('id', $ids)->where('petugas_id', auth()->id())->where('status', 'ditugaskan')->get();

        abort_if($daftar->count() !== count($ids), 403);

        $path = $r->file('foto')->store('bukti');

        DB::transaction(function () use ($daftar, $path) {
            foreach ($daftar as $t) {
                $t->update(['status' => 'diambil', 'foto_ambil' => $path, 'diambil_at' => now()]);
            }
        });

        LogAktivitas::catat('barang_diambil', $daftar->count().' barang | Tugas #'.$daftar->pluck('id')->implode(', #'));

        return redirect('/petugas/pengiriman')->with('status', "{$daftar->count()} barang tercatat di Riwayat Pengambilan. Lanjutkan pengiriman ke penerima.");
    }

    // ---------- Pengiriman ----------
    public function pengiriman()
    {
        return view('petugas.pengiriman', [
            'tugas' => $this->mine()->where('status', 'diambil')->oldest('diambil_at')->get(),
        ]);
    }

    public function selesai(Request $r, Tugas $tugas)
    {
        $this->milikku($tugas);
        abort_unless($tugas->status === 'diambil', 403);

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
            $tugas->update([
                'status'     => 'selesai',
                'foto_serah' => $path,
                'catatan'    => $r->catatan,
                'selesai_at' => now(),
            ]);
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
            $tugas = $this->mine()->whereNotNull('diambil_at')->latest('diambil_at')->take(300)->get()
                ->groupBy(fn ($t) => $t->diambil_at->toDateString());
        } else {
            $tugas = $this->mine()->where('status', 'selesai')->latest('selesai_at')->paginate(15)->withQueryString();
        }

        return view('petugas.riwayat', ['tab' => $tab, 'tugas' => $tugas]);
    }

    // Foto bukti: hanya admin atau petugas yang mengerjakan tugas itu
    public function bukti(Tugas $tugas, string $jenis)
    {
        abort_unless(in_array($jenis, ['ambil', 'serah']), 404);

        $u = auth()->user();
        abort_unless($u->role === 'admin' || $tugas->petugas_id === $u->id, 403);

        $path = $jenis === 'ambil' ? $tugas->foto_ambil : $tugas->foto_serah;
        abort_unless($path, 404);

        return Storage::disk('local')->response($path);
    }
}