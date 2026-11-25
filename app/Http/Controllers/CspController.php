<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CspController extends Controller
{
    public function laporan(Request $r)
    {
        $mentah = (string) $r->getContent();

        if (strlen($mentah) <= 10000) {
            $l = (json_decode($mentah, true)['csp-report'] ?? null);

            if (is_array($l)) {
                Log::channel('csp')->warning('csp', [
                    'dokumen'  => mb_substr((string) ($l['document-uri'] ?? ''), 0, 150),
                    'aturan'   => mb_substr((string) ($l['violated-directive'] ?? $l['effective-directive'] ?? ''), 0, 60),
                    'diblokir' => mb_substr((string) ($l['blocked-uri'] ?? ''), 0, 150),
                    'sumber'   => mb_substr((string) ($l['source-file'] ?? ''), 0, 150),
                    'baris'    => (int) ($l['line-number'] ?? 0),
                    'mode'     => (string) ($l['disposition'] ?? 'enforce'),
                ]);
            }
        }

        return response()->noContent();
    }
}