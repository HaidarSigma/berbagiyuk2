<?php

namespace App\Http\Controllers;

use App\Models\Berita;
use App\Models\LogAktivitas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Support\Gambar;

class AdminBeritaController extends Controller
{
    private function aturan(): array
    {
        return [
            'judul'     => 'required|string|max:150',
            'ringkasan' => 'required|string|max:255',
            'isi'       => 'nullable|string|max:10000',
            'tanggal'   => 'required|date',
            'foto'      => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ];
    }

    public function index()
    {
        return view('admin.berita', ['daftar' => Berita::orderByDesc('tanggal')->orderByDesc('id')->paginate(10)]);
    }

    public function store(Request $r)
    {
        $data = $r->validate($this->aturan());
        $data['foto']   = $r->hasFile('foto') ? Gambar::simpan($r->file('foto'), 'berita') : null;
        $data['terbit'] = true;

        Berita::create($data);
        LogAktivitas::catat('berita_ditambah', $data['judul']);

        return back()->with('status', 'Berita ditambahkan.');
    }

    public function update(Request $r, Berita $berita)
    {
        $data = $r->validate($this->aturan());

        if ($r->hasFile('foto')) {
            if ($berita->foto) {
                Storage::disk('public')->delete($berita->foto);
            }
            $data['foto'] = Gambar::simpan($r->file('foto'), 'berita');
        } else {
            unset($data['foto']);
        }

        $berita->update($data);
        LogAktivitas::catat('berita_diubah', $berita->judul);

        return back()->with('status', 'Berita diperbarui.');
    }

    public function tampil(Berita $berita)
    {
        $berita->update(['terbit' => ! $berita->terbit]);
        LogAktivitas::catat('berita_tampil', $berita->judul.' | '.($berita->terbit ? 'tampil' : 'disembunyikan'));

        return back()->with('status', $berita->terbit ? 'Berita ditampilkan.' : 'Berita disembunyikan.');
    }

    public function hapus(Berita $berita)
    {
        if ($berita->foto) {
            Storage::disk('public')->delete($berita->foto);
        }
        LogAktivitas::catat('berita_dihapus', $berita->judul);
        $berita->delete();

        return back()->with('status', 'Berita dihapus.');
    }
}