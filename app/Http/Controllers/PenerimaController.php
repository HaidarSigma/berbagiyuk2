<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\LogAktivitas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PenerimaController extends Controller
{
    public function status()
    {
        abort_unless(auth()->user()->role === 'penerima', 403);

        return view('penerima.status');
    }

    public function ajukanUlang(Request $r)
    {
        $u = $r->user();
        abort_unless($u->role === 'penerima' && $u->status_penerima === 'ditolak', 403);

        $data = $r->validate([
            'jenis_penerima' => 'required|in:'.implode(',', array_keys(Barang::JENIS)),
            'dokumen'        => 'required|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ], [
            'dokumen.required' => 'Unggah surat keterangan yang baru.',
            'dokumen.mimes'    => 'Surat harus berformat JPG, PNG, atau PDF.',
            'dokumen.max'      => 'Ukuran surat maksimal 2 MB.',
        ]);

        $lama = $u->dokumen;
        $nama = 'dokumen/'.Str::uuid().'.'.$r->file('dokumen')->extension().'.enc';
        Storage::put($nama, Crypt::encrypt(file_get_contents($r->file('dokumen')->getRealPath())));

        $u->forceFill([
            'jenis_penerima'        => $data['jenis_penerima'],
            'dokumen'               => $nama,
            'status_penerima'       => 'menunggu',
            'alasan_tolak_penerima' => null,
        ])->save();

        if ($lama) {
            Storage::delete($lama);
        }

        LogAktivitas::catat('penerima_ajukan_ulang');

        return redirect('/status-penerima')->with('status', 'Surat baru terkirim. Admin akan memeriksa ulang.');
    }
}