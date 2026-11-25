<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $nonce = base64_encode(random_bytes(16));
        view()->share('cspNonce', $nonce);

        $response = $next($request);
        $h = $response->headers;

        $h->set('X-Content-Type-Options', 'nosniff');
        $h->set('X-Frame-Options', 'DENY');
        $h->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $h->set('Permissions-Policy', 'camera=(self), microphone=(), geolocation=(), payment=(), usb=()');
        $h->set('Cross-Origin-Opener-Policy', 'same-origin');

        if ($request->isSecure()) {
            $h->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        $dasar = [
            "default-src 'self'",
            "img-src 'self' data: blob: https://tile.openstreetmap.org https://*.tile.openstreetmap.org",
            "media-src 'self' blob:",
            "font-src 'self'",
            "style-src 'self' 'unsafe-inline'",
            "connect-src 'self'",
            "object-src 'none'",
            "base-uri 'self'",
            "form-action 'self'",
            "frame-ancestors 'none'",
            'report-uri /csp-laporan',
        ];
        if ($request->isSecure() && app()->environment('production')) {
            $dasar[] = 'upgrade-insecure-requests';
        }

        // Ketat: skrip inline hanya yang bernonce. Longgar: skrip inline masih diizinkan.
        $ketat  = implode('; ', array_merge($dasar, ["script-src 'self' 'nonce-{$nonce}'"]));
        $longgar = implode('; ', array_merge($dasar, ["script-src 'self' 'unsafe-inline'"]));

        if (config('keamanan.csp_ketat')) {
            $h->set('Content-Security-Policy', $ketat);
        } else {
            $h->set('Content-Security-Policy', $longgar);
            $h->set('Content-Security-Policy-Report-Only', $ketat);
        }

        return $response;
    }
}