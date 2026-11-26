<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\LogAktivitas;
use App\Models\Permintaan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Tugas;
use App\Notifications\StatusDonasiBerubah;

class AdminPermintaanController extends Controller
{
    public function index()
    {
        return view('admin.permintaan', [
            'permintaan' => Permintaan::with('barang.kategori', 'barang.donasi.kebutuhanMendesak', 'penerima')->where('status', 'menunggu')->latest()->get(),
        ]);
    }

    public function setujui(Permintaan $permintaan)
    {
        $hasil = DB::transaction(function () use ($permintaan) {
            // Kunci baris barang dan permintaan: persetujuan lain yang bersamaan harus menunggu
            $barang = Barang::lockForUpdate()->find($permintaan->barang_id);
            $p      = Permintaan::lockForUpdate()->find($permintaan->id);

            if (! $barang || $p->status !== 'menunggu' || $barang->status !== 'tersedia') {
                return false;
            }

            $p->update(['status' => 'disetujui', 'alasan_tolak' => null]);
            $barang->update(['status' => 'diproses']);
            $p->loadMissing('penerima');
            $p->penerima->notify(new StatusDonasiBerubah(
                'Permintaan disetujui',
                "Permintaan untuk '{$barang->judul}' disetujui. Petugas akan mengantar barang.",
                '/permintaan-saya#permintaan-'.$p->id
            ));

            Tugas::create([
            'permintaan_id' => $p->id,
            'kode_serah'    => str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT),
     ]);

            $permintaanLain = Permintaan::with('penerima')->where('barang_id', $barang->id)
                ->where('status', 'menunggu')->where('id', '!=', $p->id)
                ->get();
            foreach ($permintaanLain as $permintaanDitolak) {
                $permintaanDitolak->update(['status' => 'ditolak', 'alasan_tolak' => 'Barang sudah diberikan kepada penerima lain.']);
                $permintaanDitolak->penerima->notify(new StatusDonasiBerubah(
                    'Permintaan tidak disetujui',
                    "Barang '{$barang->judul}' sudah diberikan kepada penerima lain.",
                    '/permintaan-saya#permintaan-'.$permintaanDitolak->id
                ));
            }

            return [$barang->judul, $p->penerima->name];
        });

        if (! $hasil) {
            return back()->with('status', 'Permintaan ini sudah tidak bisa diproses.');
        }

        LogAktivitas::catat('permintaan_disetujui', $hasil[0].' | '.$hasil[1]);

        return back()->with('status', "Permintaan {$hasil[1]} untuk '{$hasil[0]}' disetujui.");
    }

    public function tolak(Request $r, Permintaan $permintaan)
    {
        $data = $r->validate(['alasan_tolak' => 'required|string|max:500']);
        abort_unless($permintaan->status === 'menunggu', 403);

        $permintaan->update(['status' => 'ditolak', 'alasan_tolak' => $data['alasan_tolak']]);
        $permintaan->loadMissing('penerima', 'barang');
        $permintaan->penerima->notify(new StatusDonasiBerubah(
            'Permintaan tidak disetujui',
            "Permintaan untuk '{$permintaan->barang->judul}' tidak disetujui. Buka Permintaan Saya untuk melihat alasannya.",
            '/permintaan-saya#permintaan-'.$permintaan->id
        ));
        LogAktivitas::catat('permintaan_ditolak', $permintaan->barang->judul.' | '.$data['alasan_tolak']);

        return back()->with('status', 'Permintaan ditolak.');
    }
}