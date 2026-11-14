<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\Kategori;
use App\Models\KebutuhanMendesak;
use App\Models\Permintaan;

class BerandaController extends Controller
{
    public function index()
    {
        if (auth()->check() && ! request()->has('lihat')) {
            $peran = auth()->user()->role;
            if ($peran === 'admin') return redirect('/admin');
            if ($peran === 'petugas') return redirect('/petugas');
        }

        $u = auth()->user();
        if ($u && session('pin_ok') && $u->passwordKedaluwarsa()) {
            return redirect('/password/kedaluwarsa');
        }

        return view('beranda', [
            'kategori' => Kategori::all(),
            'barang'   => Barang::with('kategori')->where('status', 'tersedia')->where('verifikasi', 'disetujui')->latest()->take(8)->get(),
            'mendesak' => KebutuhanMendesak::where('status', 'mendukung')->latest()->take(3)->get(),
            'stat'     => [
                'barang'   => Barang::where('status', 'selesai')->count(),
                'penerima' => Permintaan::where('status', 'disetujui')->distinct('penerima_id')->count('penerima_id'),
            ],
        ]);
    }
}