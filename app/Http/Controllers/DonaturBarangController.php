<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\BarangMedia;
use App\Models\Kategori;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\Donasi;
use Illuminate\Support\Facades\DB;
use App\Support\Wilayah;
use App\Models\Pengambilan;
use App\Support\Gambar;

class DonaturBarangController extends Controller
{
    private function izin(): void
    {
        abort_unless(auth()->user()->role === 'donatur', 403);
    }

    private function bolehUbah(Barang $barang): void
    {
        abort_unless($barang->user_id === auth()->id(), 403);
        abort_unless($barang->status !== 'ditarik' && in_array($barang->verifikasi, ['menunggu', 'ditolak']), 403);
    }

    private function aturan(): array
    {
        return [
            'judul'             => 'required|string|max:150',
            'kategori_id'       => 'required|exists:kategoris,id',
            'kondisi'           => 'required|in:Baru,Layak Pakai,Perlu Perbaikan',
            'syarat_penerima'   => 'required|array|min:1',
            'syarat_penerima.*' => 'in:'.implode(',', array_keys(Barang::JENIS)),
            'deskripsi'         => 'required|string',
            'lokasi'            => 'required|string|max:100',
            ...Wilayah::aturan(),
            'foto'              => 'nullable|array|size:3',
            'foto.*'            => 'image|mimes:jpg,jpeg,png,webp|max:2048',
            'video'             => 'nullable|file|mimetypes:video/mp4,video/webm,video/quicktime|max:20480',
        ];
    }

    private function pesanMedia(): array
    {
        return [
            'foto.size'        => 'Untuk mengganti foto, pilih ketiga foto baru (wajib 3) atau kosongkan semuanya.',
            'foto.*.image'     => 'Berkas foto harus berupa gambar.',
            'foto.*.mimes'     => 'Format foto harus JPG, PNG, atau WEBP.',
            'foto.*.max'       => 'Ukuran tiap foto maksimal 2 MB.',
            'video.mimetypes'  => 'Video harus berformat MP4, WEBM, atau MOV.',
            'video.max'        => 'Ukuran video maksimal 20 MB.',
        ];
    }

    public function create()
    {
        $this->izin();
        return view('donatur.berbagi', ['kategori' => Kategori::all()]);
    }

   public function store(Request $r)
{
    $this->izin();

    $data = $r->validate([
        'lokasi'            => 'required|string|max:100',
        ...Wilayah::aturan(),
        'syarat_penerima'   => 'required|array|min:1',
        'syarat_penerima.*' => 'in:'.implode(',', array_keys(Barang::JENIS)),
        'barang'               => 'required|array|min:1|max:5',
        'barang.*.judul'       => 'required|string|max:150',
        'barang.*.kategori_id' => 'required|exists:kategoris,id',
        'barang.*.kondisi'     => 'required|in:Baru,Layak Pakai,Perlu Perbaikan',
        'barang.*.deskripsi'   => 'required|string',
        'barang.*.foto'        => 'required|array|size:3',
        'barang.*.foto.*'      => 'required|image|mimes:jpg,jpeg,png,webp|max:2048',
        'barang.*.video'       => 'required|file|mimetypes:video/mp4,video/webm,video/quicktime|max:20480',
    ], [
        'latitude.required'             => 'Tandai titik pengambilan di peta.',
        'barang.required'               => 'Tambahkan minimal satu barang.',
        'barang.max'                    => 'Maksimal 5 barang dalam satu donasi.',
        'barang.*.judul.required'       => 'Nama setiap barang wajib diisi.',
        'barang.*.kategori_id.required' => 'Pilih kategori untuk setiap barang.',
        'barang.*.deskripsi.required'   => 'Deskripsi setiap barang wajib diisi.',
        'barang.*.foto.required'        => 'Setiap barang wajib punya 3 foto.',
        'barang.*.foto.size'            => 'Setiap barang wajib punya tepat 3 foto.',
        'barang.*.foto.*.required'      => 'Ketiga foto setiap barang wajib diisi.',
        'barang.*.foto.*.image'         => 'Berkas foto harus berupa gambar.',
        'barang.*.foto.*.mimes'         => 'Format foto harus JPG, PNG, atau WEBP.',
        'barang.*.foto.*.max'           => 'Ukuran tiap foto maksimal 2 MB.',
        'barang.*.video.required'       => 'Setiap barang wajib punya 1 video.',
        'barang.*.video.mimetypes'      => 'Video harus berformat MP4, WEBM, atau MOV.',
        'barang.*.video.max'            => 'Ukuran tiap video maksimal 20 MB.',
    ]);

    $publik = [];
    $privat = [];

    try {
        $jumlah = DB::transaction(function () use ($r, $data, &$publik, &$privat) {
            $donasi = Donasi::create([
                'user_id'   => $r->user()->id,
                'lokasi'    => $data['lokasi'],
                'latitude'  => $data['latitude'],
                'longitude' => $data['longitude'],
            ]);

            foreach ($data['barang'] as $b) {
                $paths = [];
                foreach ($b['foto'] as $f) {
                    $paths[] = $publik[] = Gambar::simpan($f, 'barang');
                }
                $video = $privat[] = $b['video']->store('video-barang');

                $barang = Barang::create([
                    'user_id'         => $r->user()->id,
                    'donasi_id'       => $donasi->id,
                    'kategori_id'     => $b['kategori_id'],
                    'judul'           => $b['judul'],
                    'deskripsi'       => $b['deskripsi'],
                    'kondisi'         => $b['kondisi'],
                    'syarat_penerima' => $data['syarat_penerima'],
                    'lokasi'          => $data['lokasi'],
                    'latitude'        => $data['latitude'],
                    'longitude'       => $data['longitude'],
                    'foto'            => $paths[0],
                ]);

                foreach ($paths as $i => $p) {
                    BarangMedia::create(['barang_id' => $barang->id, 'jenis' => 'foto', 'path' => $p, 'urutan' => $i]);
                }
                BarangMedia::create(['barang_id' => $barang->id, 'jenis' => 'video', 'path' => $video, 'urutan' => 0]);
            }

            \App\Models\LogAktivitas::catat('donasi_dikirim', 'Donasi #'.$donasi->id.' | '.count($data['barang']).' barang');

            return count($data['barang']);
        });
    } catch (\Throwable $e) {
        Storage::disk('public')->delete($publik);
        Storage::disk('local')->delete($privat);
        throw $e;
    }

    return redirect('/barang-saya')->with('status', "{$jumlah} barang terkirim dan menunggu verifikasi admin.");
}

public function update(Request $r, Barang $barang)
{
    $this->izin();
    $this->bolehUbah($barang);

    $data = $r->validate($this->aturan(), $this->pesanMedia());

    if ($r->hasFile('foto')) {
        $lama = $barang->fotos->pluck('path')->push($barang->foto)->filter()->unique()->all();
        Storage::disk('public')->delete($lama);
        $barang->fotos()->delete();

        $paths = [];
        foreach ($data['foto'] as $i => $f) {
            $paths[] = $p = Gambar::simpan($f, 'barang');
            BarangMedia::create(['barang_id' => $barang->id, 'jenis' => 'foto', 'path' => $p, 'urutan' => $i]);
        }
        $data['foto'] = $paths[0];
    } else {
        unset($data['foto']);
    }

    if ($r->hasFile('video')) {
        if ($barang->video) {
            Storage::disk('local')->delete($barang->video->path);
            $barang->video->delete();
        }
        BarangMedia::create(['barang_id' => $barang->id, 'jenis' => 'video', 'path' => $r->file('video')->store('video-barang'), 'urutan' => 0]);
    }
    unset($data['video']);

    $data['verifikasi']   = 'menunggu';
    $data['alasan_tolak'] = null;
    $barang->update($data);

    return redirect('/barang-saya')->with('status', 'Barang diperbarui dan diajukan ulang untuk diverifikasi.');
}

    public function saya()
{
    $this->izin();

    return view('donatur.saya', [
        'barang' => Barang::with('kategori', 'fotos', 'video')->where('user_id', auth()->id())->latest()->get(),
    ]);
}

    public function edit(Barang $barang)
    {
        $this->izin();
        $this->bolehUbah($barang);
        return view('donatur.ubah', ['barang' => $barang, 'kategori' => Kategori::all()]);
    }

    public function tarik(Barang $barang)
{
    $this->izin();
    abort_unless($barang->user_id === auth()->id(), 403);
    abort_unless($barang->status === 'menunggu_ambil', 403);   // setelah diambil petugas tidak bisa ditarik

    Pengambilan::where('barang_id', $barang->id)->whereIn('status', ['baru', 'ditugaskan'])->delete();
    $barang->update(['status' => 'ditarik']);
    \App\Models\LogAktivitas::catat('barang_ditarik', $barang->judul);

    return back()->with('status', 'Barang ditarik.');
}

    private function layakLabel($koleksi)
{
    $daftar = $koleksi
        ->filter(fn ($b) => $b->verifikasi === 'disetujui' && $b->status === 'menunggu_ambil')
        ->values();

    abort_if($daftar->isEmpty(), 403, 'Label hanya untuk barang yang sudah disetujui dan belum diambil petugas.');

    return $daftar;
}

public function label(Barang $barang)
{
    $this->izin();
    abort_unless($barang->user_id === auth()->id(), 403);

    return view('donatur.label', [
        'barangs' => $this->layakLabel(Barang::with('kategori')->whereKey($barang->id)->get()),
        'judul'   => $barang->judul,
    ]);
}

public function labelDonasi(Donasi $donasi)
{
    $this->izin();
    abort_unless($donasi->user_id === auth()->id(), 403);

    return view('donatur.label', [
        'barangs' => $this->layakLabel($donasi->barangs()->with('kategori')->get()),
        'judul'   => 'Donasi #'.$donasi->id,
    ]);
}
}