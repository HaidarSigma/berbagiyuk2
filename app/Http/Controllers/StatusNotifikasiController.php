<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;

class StatusNotifikasiController extends Controller
{
    public function index(Request $request)
    {
        return view('notifikasi.index', [
            'notifikasi' => $request->user()->notifications()->latest()->paginate(25),
        ]);
    }

    public function buka(Request $request, string $id)
    {
        $notifikasi = $request->user()->notifications()->whereKey($id)->firstOrFail();
        $notifikasi->markAsRead();

        return redirect($this->url($notifikasi));
    }

    public function bacaSemua(Request $request)
    {
        $request->user()->unreadNotifications()->update(['read_at' => now()]);

        return back()->with('status', 'Semua notifikasi ditandai sudah dibaca.');
    }

    public function status(Request $request)
    {
        $user = $request->user();
        $terbaru = $user->notifications()->latest()->first();

        return response()->json([
            'jumlah' => $user->unreadNotifications()->count(),
            'terbaru' => $terbaru ? [
                'id' => $terbaru->id,
                'judul' => (string) ($terbaru->data['judul'] ?? 'Pembaruan status'),
                'pesan' => (string) ($terbaru->data['pesan'] ?? ''),
                'url' => $this->url($terbaru),
            ] : null,
        ])->header('Cache-Control', 'no-store, private');
    }

    private function url(DatabaseNotification $notifikasi): string
    {
        $url = $notifikasi->data['url'] ?? '/notifikasi';

        if (! is_string($url) || ! str_starts_with($url, '/') || str_starts_with($url, '//')) {
            return '/notifikasi';
        }

        return $url;
    }
}
