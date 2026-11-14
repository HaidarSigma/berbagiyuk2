<?php

namespace App\Http\Controllers;

use App\Models\KebutuhanMendesak;
use App\Models\LogAktivitas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AdminKebutuhanController extends Controller
{
    private function aturan(): array
    {
        return [
            'judul'      => 'required|string|max:150',
            'keterangan' => 'nullable|string|max:255',
            'lokasi'     => 'required|string|max:100',
            'foto'       => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ];
    }

    public function index()
    {
        return view('admin.kebutuhan', ['daftar' => KebutuhanMendesak::latest()->paginate(15)]);
    }

    public function store(Request $r)
    {
        $data = $r->validate($this->aturan());
        $data['foto']   = $r->hasFile('foto') ? $r->file('foto')->store('kebutuhan', 'public') : null;
        $data['status'] = 'mendukung';

        KebutuhanMendesak::create($data);
        LogAktivitas::catat('kebutuhan_ditambah', $data['judul']);

        return back()->with('status', 'Kebutuhan ditambahkan.');
    }

    public function update(Request $r, KebutuhanMendesak $kebutuhan)
    {
        $data = $r->validate($this->aturan());

        if ($r->hasFile('foto')) {
            if ($kebutuhan->foto) {
                Storage::disk('public')->delete($kebutuhan->foto);
            }
            $data['foto'] = $r->file('foto')->store('kebutuhan', 'public');
        } else {
            unset($data['foto']);
        }

        $kebutuhan->update($data);
        LogAktivitas::catat('kebutuhan_diubah', $kebutuhan->judul);

        return back()->with('status', 'Kebutuhan diperbarui.');
    }

    public function status(KebutuhanMendesak $kebutuhan)
    {
        $kebutuhan->update(['status' => $kebutuhan->status === 'mendukung' ? 'selesai' : 'mendukung']);
        LogAktivitas::catat('kebutuhan_status', $kebutuhan->judul.' | '.$kebutuhan->status);

        return back()->with('status', 'Status kebutuhan diperbarui.');
    }

    public function hapus(KebutuhanMendesak $kebutuhan)
    {
        if ($kebutuhan->foto) {
            Storage::disk('public')->delete($kebutuhan->foto);
        }
        LogAktivitas::catat('kebutuhan_dihapus', $kebutuhan->judul);
        $kebutuhan->delete();

        return back()->with('status', 'Kebutuhan dihapus.');
    }
}