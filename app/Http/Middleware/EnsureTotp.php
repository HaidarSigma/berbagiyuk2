<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTotp
{
    public function handle(Request $request, Closure $next): Response
    {
        $u = $request->user();
        $wajib = in_array($u->role, ['admin', 'petugas'], true) || $u->totp_aktif;

        if (! $wajib || $request->session()->get('totp_ok')) {
            return $next($request);
        }

        return redirect($u->totp_aktif ? '/totp/verifikasi' : '/totp/aktifkan');
    }
}