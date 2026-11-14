<?php

namespace App\Support;

class Wilayah
{
    private static ?array $poligon = null;

    // Daftar poligon: tiap poligon = daftar cincin ([0] = batas luar, sisanya lubang)
    private static function poligon(): array
    {
        if (self::$poligon !== null) {
            return self::$poligon;
        }

        $berkas = config('wilayah.geojson');
        if (! $berkas || ! is_file($berkas)) {
            return self::$poligon = [];
        }

        $data = json_decode((string) file_get_contents($berkas), true);

        return self::$poligon = is_array($data) ? self::kumpulkan($data) : [];
    }

    private static function kumpulkan(array $n): array
    {
        return match ($n['type'] ?? null) {
            'FeatureCollection' => array_merge([], ...array_map(fn ($f) => self::kumpulkan($f), $n['features'] ?? [])),
            'Feature'           => self::kumpulkan($n['geometry'] ?? []),
            'Polygon'           => [$n['coordinates']],
            'MultiPolygon'      => $n['coordinates'],
            default             => [],
        };
    }

    // Algoritma ray casting: titik di dalam cincin bila garis horizontal memotong sisi ganjil kali
    private static function dalamCincin(float $lat, float $lng, array $cincin): bool
    {
        $di = false;
        $n  = count($cincin);

        for ($i = 0, $j = $n - 1; $i < $n; $j = $i++) {
            $xi = $cincin[$i][0]; $yi = $cincin[$i][1];
            $xj = $cincin[$j][0]; $yj = $cincin[$j][1];

            if (($yi > $lat) !== ($yj > $lat) && $lng < ($xj - $xi) * ($lat - $yi) / ($yj - $yi) + $xi) {
                $di = ! $di;
            }
        }

        return $di;
    }

    public static function didalam(float $lat, float $lng): bool
    {
        $poligon = self::poligon();

        if ($poligon) {
            foreach ($poligon as $p) {
                if (! self::dalamCincin($lat, $lng, $p[0])) {
                    continue;
                }
                foreach (array_slice($p, 1) as $lubang) {
                    if (self::dalamCincin($lat, $lng, $lubang)) {
                        continue 2;
                    }
                }
                return true;
            }

            return false;
        }

        // Cadangan bila berkas GeoJSON tidak ada: kotak batas dari config
        $b = config('wilayah.batas');

        return $lat >= $b['lat_min'] && $lat <= $b['lat_max']
            && $lng >= $b['lng_min'] && $lng <= $b['lng_max'];
    }

    // Aturan untuk kolom latitude dan longitude di semua form lokasi
    public static function aturan(bool $wajib = true): array
    {
        $w = $wajib ? 'required' : 'nullable';

        return [
            'latitude' => [$w, 'bail', 'numeric', function ($atribut, $nilai, $gagal) {
                $lng = request()->input('longitude');

                if (! is_numeric($lng) || ! self::didalam((float) $nilai, (float) $lng)) {
                    $gagal('Titik lokasi harus berada di wilayah layanan '.config('wilayah.nama').'.');
                }
            }],
            'longitude' => [$w, 'numeric'],
        ];
    }
}