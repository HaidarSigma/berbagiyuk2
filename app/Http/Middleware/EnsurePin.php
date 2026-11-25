<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class EnsurePin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
public function handle(Request $request, Closure $next)
{
    $u = $request->user();

    if (! $u->aktif) {
        \App\Models\LogAktivitas::catat('sesi_ditolak_nonaktif');
        auth()->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/masuk')->withErrors(['email' => 'Akun ini dinonaktifkan. Hubungi admin.']);
    }

    if (! $u->hasVerifiedEmail()) return redirect()->route('verification.notice');
    if (! $u->pin) return redirect('/pin/buat');
    if (! $request->session()->get('pin_ok')) return redirect('/pin/masuk');

    // Sesi lama: nomor versi berubah karena password atau PIN diganti
if ((int) $request->session()->get('sesi_versi', -1) !== (int) $u->sesi_versi) {
    Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();

    return redirect('/masuk')->withErrors(['email' => 'Sesimu berakhir karena ada perubahan keamanan pada akunmu. Silakan masuk lagi.']);
}

    return $next($request);
}
}
