<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class WilayahUnduh extends Command
{
    protected $signature = 'wilayah:unduh {nama? : Nama wilayah yang dicari} {--pilih= : Nomor hasil yang dipakai}';
    protected $description = 'Mengunduh batas wilayah (GeoJSON) dari OpenStreetMap Nominatim';

    public function handle(): int
    {
        $cari = $this->argument('nama') ?: 'Kabupaten Boyolali, Jawa Tengah, Indonesia';

        try {
            $res = Http::withHeaders(['User-Agent' => 'BerbagiYuk-TugasKuliah/1.0'])
                ->timeout(60)
                ->get('https://nominatim.openstreetmap.org/search', [
                    'q'                 => $cari,
                    'format'            => 'geojson',
                    'polygon_geojson'   => 1,
                    'polygon_threshold' => 0.0005,
                    'countrycodes'      => 'id',
                    'limit'             => 5,
                ]);
        } catch (\Throwable $e) {
            $this->error('Gagal terhubung: '.$e->getMessage());
            $this->line('Periksa koneksi internet, atau pakai cara manual di panduan.');
            return self::FAILURE;
        }

        if (! $res->successful()) {
            $this->error('Server menjawab dengan status '.$res->status());
            return self::FAILURE;
        }

        $hasil = collect($res->json('features', []))
            ->filter(fn ($f) => in_array($f['geometry']['type'] ?? '', ['Polygon', 'MultiPolygon']))
            ->values();

        if ($hasil->isEmpty()) {
            $this->error('Tidak ada batas wilayah (poligon) untuk: '.$cari);
            return self::FAILURE;
        }

        $this->info('Hasil pencarian untuk: '.$cari);
        foreach ($hasil as $i => $f) {
            $p = $f['properties'] ?? [];
            $this->line("[{$i}] ".($p['display_name'] ?? '?').' ('.($p['category'] ?? '').'/'.($p['type'] ?? '').')');
        }

        $pilih = $this->option('pilih');
        if ($pilih === null) {
            $pilih = $hasil->count() > 1 ? $this->ask('Ketik nomor hasil yang sesuai', '0') : '0';
        }

        $dipilih = $hasil[(int) $pilih] ?? null;
        if (! $dipilih) {
            $this->error('Nomor tidak valid.');
            return self::FAILURE;
        }

        $geo = [
            'type'     => 'FeatureCollection',
            'features' => [[
                'type'       => 'Feature',
                'properties' => [
                    'nama'   => $dipilih['properties']['display_name'] ?? $cari,
                    'sumber' => 'OpenStreetMap contributors (ODbL)',
                ],
                'geometry'   => $dipilih['geometry'],
            ]],
        ];

        $dir = public_path('geojson');
        if (! is_dir($dir)) {
            mkdir($dir, 0775, true);
        }
        $berkas = $dir.DIRECTORY_SEPARATOR.'wilayah.geojson';
        file_put_contents($berkas, json_encode($geo, JSON_UNESCAPED_UNICODE));

        // Ringkasan supaya mudah dicocokkan dengan kotak batas di config
        $titik = 0;
        $b = ['lat' => [INF, -INF], 'lng' => [INF, -INF]];
        $telusur = function ($n) use (&$telusur, &$titik, &$b) {
            if (isset($n[0]) && is_numeric($n[0])) {
                $titik++;
                $b['lng'] = [min($b['lng'][0], $n[0]), max($b['lng'][1], $n[0])];
                $b['lat'] = [min($b['lat'][0], $n[1]), max($b['lat'][1], $n[1])];
                return;
            }
            foreach ($n as $x) {
                $telusur($x);
            }
        };
        $telusur($dipilih['geometry']['coordinates']);

        $kb = round(filesize($berkas) / 1024, 1);
        $this->info("Tersimpan: {$berkas} ({$kb} KB, {$titik} titik)");
        $this->line(sprintf('Kotak pembatas: lat %.3f sampai %.3f, lng %.3f sampai %.3f', $b['lat'][0], $b['lat'][1], $b['lng'][0], $b['lng'][1]));

        return self::SUCCESS;
    }
}