<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\Berita;
use App\Models\Kategori;
use App\Models\KebutuhanMendesak;
use App\Models\Permintaan;
use Illuminate\Support\Facades\File;

class BerandaController extends Controller
{
    // Gambar pertama (urut abjad) di public/img/hero jadi latar hero. ?v= supaya gambar pengganti langsung tampil.
    private function hero(): ?string
    {
        $dir = public_path('img/hero');
        if (! is_dir($dir)) {
            return null;
        }

        $f = collect(File::files($dir))
            ->filter(fn ($f) => in_array(strtolower($f->getExtension()), ['jpg', 'jpeg', 'png', 'webp']))
            ->sortBy(fn ($f) => $f->getFilename(), SORT_NATURAL | SORT_FLAG_CASE)
            ->first();

        return $f ? asset('img/hero/'.rawurlencode($f->getFilename())).'?v='.$f->getMTime() : null;
    }

    public function index()
    {
        if (auth()->check() && ! request()->has('lihat')) {
            $peran = auth()->user()->role;
            if (session('pin_ok') && auth()->user()->passwordKedaluwarsa()) {
                return redirect('/password/kedaluwarsa');
            }
            if ($peran === 'admin') return redirect('/admin');
            if ($peran === 'petugas') return redirect('/petugas');
        }

        $u = auth()->user();
        if ($u && session('pin_ok') && $u->passwordKedaluwarsa()) {
            return redirect('/password/kedaluwarsa');
        }

        return view('beranda', [
            'hero'     => $this->hero(),
            'berita'   => Berita::where('terbit', true)->orderByDesc('tanggal')->orderByDesc('id')->take(5)->get(),
            'kategori' => Kategori::all(),
            'barang'   => Barang::with('kategori', 'donasi.kebutuhanMendesak')->where('status', 'tersedia')->where('verifikasi', 'disetujui')->latest()->take(8)->get(),
            'mendesak' => KebutuhanMendesak::where('status', 'mendukung')->latest()->take(3)->get(),
            'stat'     => [
                'barang'   => Barang::where('status', 'selesai')->count(),
                'penerima' => Permintaan::where('status', 'disetujui')->distinct('penerima_id')->count('penerima_id'),
            ],
        ]);
    }
}