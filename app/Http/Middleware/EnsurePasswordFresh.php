<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePasswordFresh
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user()->passwordKedaluwarsa()) {
            return redirect('/password/kedaluwarsa');
        }

        return $next($request);
    }
}