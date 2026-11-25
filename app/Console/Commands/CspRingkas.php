<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CspRingkas extends Command
{
    protected $signature = 'csp:ringkas {--hari=7}';
    protected $description = 'Merangkum pelanggaran Content Security Policy dari log';

    public function handle(): int
    {
        $batas  = now()->subDays((int) $this->option('hari'))->startOfDay()->timestamp;
        $hitung = [];

        foreach (glob(storage_path('logs/csp-*.log')) ?: [] as $berkas) {
            if (filemtime($berkas) < $batas) {
                continue;
            }
            foreach (file($berkas, FILE_IGNORE_NEW_LINES) ?: [] as $baris) {
                if (! preg_match('/ csp (\{.*\})(?: \[\])?\s*$/', $baris, $m)) {
                    continue;
                }
                $d = json_decode($m[1], true);
                if (! is_array($d)) {
                    continue;
                }
                $kunci = implode(' | ', [$d['mode'] ?? '', $d['aturan'] ?? '', $d['diblokir'] ?? '', basename((string) ($d['sumber'] ?? '')).':'.($d['baris'] ?? 0)]);
                $hitung[$kunci] = ($hitung[$kunci] ?? 0) + 1;
            }
        }

        if (! $hitung) {
            $this->info('Belum ada pelanggaran tercatat.');
            return self::SUCCESS;
        }

        arsort($hitung);
        $this->table(['Jumlah', 'Mode | Aturan | Diblokir | Sumber:baris'], collect($hitung)->take(40)->map(fn ($n, $k) => [$n, $k])->values()->all());
        $this->line('Mode report = hanya dilaporkan (akan diblokir bila CSP_KETAT=true). Mode enforce = sudah diblokir sekarang.');

        return self::SUCCESS;
    }
}