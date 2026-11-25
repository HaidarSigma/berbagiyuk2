<?php

namespace App\Http\Controllers;

use App\Models\BarangMedia;
use App\Models\LogAktivitas;
use Illuminate\Support\Facades\Storage;

class MediaController extends Controller
{
    // Video privat: hanya admin atau donatur pemilik barang
    public function video(BarangMedia $media)
    {
        abort_unless($media->jenis === 'video', 404);

        $u = auth()->user();
        abort_unless($u->role === 'admin' || $media->barang->user_id === $u->id, 403);

        $berkas = Storage::disk('local')->path($media->path);
        abort_unless(is_file($berkas), 404);

        // Dicatat sekali per sesi (pemutar mengirim banyak permintaan saat memutar atau menggeser)
        $kunci = 'video_dilog_'.$media->id;
        if ($u->role === 'admin' && ! session()->has($kunci)) {
            LogAktivitas::catat('video_dibuka', 'Barang #'.$media->barang_id);
            session()->put($kunci, true);
        }

        return response()->file($berkas, ['Content-Type' => Storage::disk('local')->mimeType($media->path)]);
    }
}