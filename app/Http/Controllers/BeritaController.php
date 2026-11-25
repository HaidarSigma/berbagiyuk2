<?php

namespace App\Http\Controllers;

use App\Models\Berita;

class BeritaController extends Controller
{
    public function index()
    {
        return view('berita.index', [
            'daftar' => Berita::where('terbit', true)->orderByDesc('tanggal')->orderByDesc('id')->paginate(9),
        ]);
    }

    public function show(Berita $berita)
    {
        abort_unless($berita->terbit, 404);

        return view('berita.show', [
            'berita' => $berita,
            'lain'   => Berita::where('terbit', true)->where('id', '!=', $berita->id)->orderByDesc('tanggal')->take(3)->get(),
        ]);
    }
}