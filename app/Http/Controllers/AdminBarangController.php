<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\User;
use Illuminate\Http\Request;
use App\Models\Pengambilan;
use App\Models\LogAktivitas;
use App\Notifications\StatusDonasiBerubah;

class AdminBarangController extends Controller
{
    private function antrean()
    {
        return Barang::with('kategori', 'user', 'fotos', 'video', 'donasi.kebutuhanMendesak')
            ->where('verifikasi', 'menunggu')
            ->where('status', '!=', 'ditarik');
    }

    public function dashboard()
    {
        return view('admin.dashboard', [
            'menunggu' => $this->antrean()->count(),
            'tersedia' => Barang::where('verifikasi', 'disetujui')->where('status', 'tersedia')->count(),
            'ditolak'  => Barang::where('verifikasi', 'ditolak')->count(),
            'donatur'  => User::where('role', 'donatur')->count(),
            'penerima' => User::where('role', 'penerima')->count(),
            'terbaru'  => $this->antrean()->latest()->take(5)->get(),
        ]);
    }

    public function index()
    {
        return view('admin.barang', ['barang' => $this->antrean()->latest()->get()]);
    }

public function setujui(Barang $barang)
{
    $barang->update(['verifikasi' => 'disetujui', 'alasan_tolak' => null, 'status' => 'menunggu_ambil']);

    // Tugas pengambilan dibuat saat barang disetujui, tanpa menunggu penerima
    Pengambilan::firstOrCreate(['barang_id' => $barang->id]);
    $barang->user->notify(new StatusDonasiBerubah(
        'Donasi disetujui',
        "Barang '{$barang->judul}' disetujui dan menunggu dijemput petugas.",
        '/barang-saya#barang-'.$barang->id
    ));

    LogAktivitas::catat('barang_disetujui', $barang->judul);

    return back()->with('status', "Barang '{$barang->judul}' disetujui. Tugas pengambilan dibuat.");
}
    public function tolak(Request $r, Barang $barang)
    {
        $data = $r->validate(['alasan_tolak' => 'required|string|max:500']);
        $barang->update(['verifikasi' => 'ditolak', 'alasan_tolak' => $data['alasan_tolak']]);
        $barang->user->notify(new StatusDonasiBerubah(
            'Donasi perlu diperbaiki',
            "Barang '{$barang->judul}' belum disetujui. Buka Barang Saya untuk melihat alasannya.",
            '/barang-saya#barang-'.$barang->id
        ));
        return back()->with('status', "Barang '{$barang->judul}' ditolak.");
    }
}