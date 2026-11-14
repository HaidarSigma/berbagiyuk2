<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\Kategori;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\Donasi;
use Illuminate\Support\Facades\DB;
use App\Support\Wilayah;

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

    private function aturan(bool $fotoWajib): array
    {
        return [
            'judul'       => 'required|string|max:150',
            'kategori_id' => 'required|exists:kategoris,id',
            'kondisi'     => 'required|in:Baru,Layak Pakai,Perlu Perbaikan',
            'syarat_penerima'   => 'required|array|min:1',
            'syarat_penerima.*' => 'in:keluarga_kurang_mampu,panti_yayasan,korban_bencana,pelajar',
            'deskripsi'   => 'required|string',
            'lokasi'      => 'required|string|max:100',
            ...Wilayah::aturan(),
            'foto'        => ($fotoWajib ? 'required' : 'nullable') . '|image|max:2048',
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
        'barang'               => 'required|array|min:1|max:10',
        'barang.*.judul'       => 'required|string|max:150',
        'barang.*.kategori_id' => 'required|exists:kategoris,id',
        'barang.*.kondisi'     => 'required|in:Baru,Layak Pakai,Perlu Perbaikan',
        'barang.*.deskripsi'   => 'required|string',
        'barang.*.foto'        => 'required|image|mimes:jpg,jpeg,png,webp|max:2048',
    ], [
        'latitude.required'             => 'Tandai titik pengambilan di peta.',
        'barang.required'               => 'Tambahkan minimal satu barang.',
        'barang.max'                    => 'Maksimal 10 barang dalam satu donasi.',
        'barang.*.judul.required'       => 'Nama setiap barang wajib diisi.',
        'barang.*.kategori_id.required' => 'Pilih kategori untuk setiap barang.',
        'barang.*.deskripsi.required'   => 'Deskripsi setiap barang wajib diisi.',
        'barang.*.foto.required'        => 'Setiap barang wajib punya foto.',
        'barang.*.foto.image'           => 'Berkas foto harus berupa gambar.',
        'barang.*.foto.mimes'           => 'Format foto harus JPG, PNG, atau WEBP.',
        'barang.*.foto.max'             => 'Ukuran tiap foto maksimal 2 MB.',
    ]);

    $tersimpan = [];

    try {
        $jumlah = DB::transaction(function () use ($r, $data, &$tersimpan) {
            $donasi = Donasi::create([
                'user_id'   => $r->user()->id,
                'lokasi'    => $data['lokasi'],
                'latitude'  => $data['latitude'],
                'longitude' => $data['longitude'],
            ]);

            foreach ($data['barang'] as $b) {
                $foto = $b['foto']->store('barang', 'public');
                $tersimpan[] = $foto;

                Barang::create([
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
                    'foto'            => $foto,
                ]);
            }

            \App\Models\LogAktivitas::catat('donasi_dikirim', 'Donasi #'.$donasi->id.' | '.count($data['barang']).' barang');

            return count($data['barang']);
        });
    } catch (\Throwable $e) {
        Storage::disk('public')->delete($tersimpan);
        throw $e;
    }

    return redirect('/barang-saya')->with('status', "{$jumlah} barang terkirim dan menunggu verifikasi admin.");
}

    public function saya()
    {
        $this->izin();
        return view('donatur.saya', [
            'barang' => Barang::with('kategori')->where('user_id', auth()->id())->latest()->get(),
        ]);
    }

    public function edit(Barang $barang)
    {
        $this->izin();
        $this->bolehUbah($barang);
        return view('donatur.ubah', ['barang' => $barang, 'kategori' => Kategori::all()]);
    }

    public function update(Request $r, Barang $barang)
    {
        $this->izin();
        $this->bolehUbah($barang);
        $data = $r->validate($this->aturan(false));

        if ($r->hasFile('foto')) {
            if ($barang->foto) Storage::disk('public')->delete($barang->foto);
            $data['foto'] = $r->file('foto')->store('barang', 'public');
        } else {
            unset($data['foto']);
        }

        $data['verifikasi'] = 'menunggu';
        $data['alasan_tolak'] = null;
        $barang->update($data);

        return redirect('/barang-saya')->with('status', 'Barang diperbarui dan diajukan ulang untuk diverifikasi.');
    }

    public function tarik(Barang $barang)
    {
        $this->izin();
        abort_unless($barang->user_id === auth()->id(), 403);
        $barang->update(['status' => 'ditarik']);
        return back()->with('status', 'Barang ditarik.');
    }

    public function label(Barang $barang)
{
    $this->izin();
    abort_unless(
        $barang->user_id === auth()->id() && $barang->verifikasi === 'disetujui' && $barang->status !== 'ditarik',
        403
    );

    return view('donatur.label', ['barang' => $barang->load('kategori')]);
}
}