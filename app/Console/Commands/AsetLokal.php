<?php

namespace App\Console\Commands;

use App\Models\PeringatanKeamanan;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;

class AsetLokal extends Command
{
    protected $signature = 'aset:lokal {--cek : Hanya menampilkan rencana, tidak mengubah apa pun} {--periksa : Memeriksa integritas berkas vendor terhadap manifest}';
    protected $description = 'Mengunduh pustaka CDN dan font ke server sendiri, lalu menulis ulang tampilan agar memakai aset lokal';

    private const FONT_BAWAAN = 'https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap';

    private const VENDOR = [
        'leaflet/leaflet.css'               => 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css',
        'leaflet/leaflet.js'                => 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js',
        'leaflet/images/marker-icon.png'    => 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-icon.png',
        'leaflet/images/marker-icon-2x.png' => 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-icon-2x.png',
        'leaflet/images/marker-shadow.png'  => 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-shadow.png',
        'leaflet/images/layers.png'         => 'https://unpkg.com/leaflet@1.9.4/dist/images/layers.png',
        'leaflet/images/layers-2x.png'      => 'https://unpkg.com/leaflet@1.9.4/dist/images/layers-2x.png',
        'html5-qrcode/html5-qrcode.min.js'  => 'https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js',
        'qrcodejs/qrcode.min.js'            => 'https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js',
    ];

    // [pola, pengganti, label]
    private function aturan(): array
    {
        return [
            ['#<script\s+src="https://cdn\.tailwindcss\.com[^"]*"\s*></script>#i', "@include('partials.head-asset')", 'Tailwind CDN'],
            ['#<link[^>]+(?:fonts\.googleapis\.com|fonts\.gstatic\.com)[^>]*>[ \t]*\R?#i', '', 'Link font Google'],
            ['#https://unpkg\.com/leaflet@1\.9\.4/dist/(leaflet\.css|leaflet\.js)#', '{{ asset("vendor/leaflet/$1") }}', 'Leaflet'],
            ['#https://unpkg\.com/html5-qrcode@2\.3\.8/html5-qrcode\.min\.js#', '{{ asset("vendor/html5-qrcode/html5-qrcode.min.js") }}', 'html5-qrcode'],
            ['#https://cdnjs\.cloudflare\.com/ajax/libs/qrcodejs/1\.0\.0/qrcode\.min\.js#', '{{ asset("vendor/qrcodejs/qrcode.min.js") }}', 'qrcode.js'],
            // Atribut inline yang diblokir CSP ketat
            ['#onsubmit="return confirm\(\'([^\']*)\'\)"#', 'data-konfirmasi="$1"', 'onsubmit confirm'],
            ['#onclick="window\.print\(\)"#', 'data-cetak', 'onclick print'],
            ['#href="javascript:history\.back\(\)"#', 'href="#" data-kembali', 'javascript:back'],
            ['#onclick="this\.parentElement\.remove\(\)"#', 'data-tutup', 'onclick tutup'],
            ['#onclick="navigator\.clipboard\.writeText\(\'([^\']*)\'\);[^"]*"#', 'data-salin="$1"', 'onclick salin'],
            ['#onclick="document\.getElementById\(\'([^\']+)\'\)\.src = this\.src"#', 'data-galeri="$1"', 'onclick galeri'],
            ['#onclick="tambah\(\)"#', 'data-aksi="tambah"', 'onclick tambah'],
            ['#onclick="hapus\(this\)"#', 'data-aksi="hapus"', 'onclick hapus'],
            // Setiap <script> inline mendapat nonce CSP
            ['#<script(?![^>]*\bsrc=)(?![^>]*\bnonce=)([^>]*)>#i', '<script nonce="{{ $cspNonce ?? \'\' }}"$1>', 'nonce skrip inline'],
        ];
    }

    public function handle(): int
    {
        if ($this->option('periksa')) {
            return $this->periksa();
        }

        $views = collect(File::allFiles(resource_path('views')))
            ->filter(fn ($f) => str_ends_with($f->getFilename(), '.blade.php'));

        $urlFont = $views->flatMap(function ($f) {
            preg_match_all('#https://fonts\.googleapis\.com/css2?\?[^"\'\s>]+#', File::get($f->getRealPath()), $m);
            return $m[0];
        })->map(fn ($u) => html_entity_decode($u))->unique()->values();
        if ($urlFont->isEmpty()) {
            $urlFont = collect([self::FONT_BAWAAN]);
        }

        $cek = $this->option('cek');
        if (! $cek) {
            if (! $this->unduhVendor() || ! $this->unduhFont($urlFont)) {
                return self::FAILURE;
            }
        } else {
            $this->line('Font yang akan diunduh: '.$urlFont->count().' sumber CSS');
            $this->line('Pustaka yang akan diunduh: '.count(self::VENDOR).' berkas');
        }

        // Menulis ulang tampilan
        $cadangan   = storage_path('app/aset-cadangan/'.now()->format('Ymd-His'));
        $konfigurasi = [];
        $laporan    = [];
        $eksternal  = [];

        foreach ($views as $f) {
            $asli = File::get($f->getRealPath());
            $baru = preg_replace_callback('#<script[^>]*>\s*tailwind\.config\s*=(.*?)</script>#is', function ($m) use (&$konfigurasi, $f) {
                $konfigurasi[trim(preg_replace('/\s+/', ' ', $m[1]))][] = $f->getRelativePathname();
                return '';
            }, $asli, -1, $nConfig);

            $perFile = $nConfig ? ['tailwind.config dihapus' => $nConfig] : [];
            foreach ($this->aturan() as [$pola, $ganti, $label]) {
                $baru = preg_replace($pola, $ganti, $baru, -1, $n);
                if ($n) {
                    $perFile[$label] = $n;
                }
            }

            if ($perFile) {
                $laporan[] = [$f->getRelativePathname(), collect($perFile)->map(fn ($n, $l) => "{$l} ×{$n}")->implode(', ')];
                if (! $cek && $baru !== $asli) {
                    File::ensureDirectoryExists(dirname($cadangan.'/'.$f->getRelativePathname()));
                    File::put($cadangan.'/'.$f->getRelativePathname(), $asli);
                    File::put($f->getRealPath(), $baru);
                }
            }

            // Sumber eksternal yang masih tersisa pada <script src> dan <link href>
            preg_match_all('#<(?:script|link)[^>]+(?:src|href)="(https?://[^"]+)"#i', $baru, $m);
            foreach ($m[1] as $u) {
                $eksternal[$u][] = $f->getRelativePathname();
            }
        }

        $laporan ? $this->table(['Tampilan', 'Perubahan'], $laporan) : $this->info('Tidak ada tampilan yang perlu diubah.');

        if ($konfigurasi) {
            $this->warn('Konfigurasi tailwind.config yang ditemukan. Salin bagian di luar fontFamily ke tailwind.config.js bila ada:');
            foreach ($konfigurasi as $isi => $berkas) {
                $this->line('  '.mb_substr($isi, 0, 300).'  ('.count($berkas).' berkas)');
            }
        }
        if ($eksternal) {
            $this->warn('Sumber eksternal yang MASIH dipakai (akan diblokir CSP, kecuali dimasukkan ke daftar izin):');
            foreach ($eksternal as $u => $berkas) {
                $this->line("  {$u}  <- ".implode(', ', array_unique($berkas)));
            }
        }

        if (! $cek && $laporan) {
            $this->info("Tampilan asli dicadangkan di {$cadangan}");
            $this->line('Langkah berikut: jalankan build-css.bat, lalu muat ulang halaman dengan Ctrl+F5.');
        }

        return self::SUCCESS;
    }

    private function unduhVendor(): bool
    {
        $manifest = [];
        try {
            foreach (self::VENDOR as $rel => $url) {
                $res = Http::timeout(60)->get($url);
                if (! $res->successful()) {
                    $this->error("Gagal mengunduh {$url} (status {$res->status()})");
                    return false;
                }
                $tujuan = public_path('vendor/'.$rel);
                File::ensureDirectoryExists(dirname($tujuan));
                File::put($tujuan, $res->body());
                $manifest[$rel] = ['url' => $url, 'sha256' => hash('sha256', $res->body()), 'ukuran' => strlen($res->body())];
                $this->line("✔ vendor/{$rel}");
            }
        } catch (\Throwable $e) {
            $this->error('Gagal mengunduh: '.$e->getMessage());
            $this->line('Bila pesannya "SSL certificate problem", set curl.cainfo di php.ini ke berkas cacert.pem.');
            return false;
        }

        File::put(public_path('vendor/manifest.json'), json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        return true;
    }

    private function unduhFont($urlFont): bool
    {
        File::ensureDirectoryExists(public_path('fonts'));
        File::ensureDirectoryExists(public_path('css'));

        $css   = "/* Dibuat oleh php artisan aset:lokal. Jangan diedit manual. */\n";
        $sudah = [];
        $ua    = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0 Safari/537.36';

        try {
            foreach ($urlFont as $u) {
                $res = Http::withHeaders(['User-Agent' => $ua])->timeout(60)->get($u);
                if (! $res->successful()) {
                    $this->error("Gagal mengambil CSS font: {$u}");
                    return false;
                }

                preg_match_all('#/\*\s*([a-z0-9-]+)\s*\*/\s*(@font-face\s*\{[^}]*\})#i', $res->body(), $blok, PREG_SET_ORDER);
                foreach ($blok as $b) {
                    if (! in_array($b[1], ['latin', 'latin-ext'], true)) {
                        continue;
                    }
                    $isi = preg_replace_callback('#url\((https://fonts\.gstatic\.com[^)]+)\)#', function ($m) use (&$sudah) {
                        $nama = basename(parse_url($m[1], PHP_URL_PATH));
                        if (! isset($sudah[$nama])) {
                            $f = Http::timeout(60)->get($m[1]);
                            if ($f->successful()) {
                                File::put(public_path('fonts/'.$nama), $f->body());
                                $sudah[$nama] = true;
                            }
                        }
                        return 'url(/fonts/'.$nama.')';
                    }, $b[2]);
                    $css .= "/* {$b[1]} */\n{$isi}\n";
                }
            }
        } catch (\Throwable $e) {
            $this->error('Gagal mengunduh font: '.$e->getMessage());
            return false;
        }

        File::put(public_path('css/fonts.css'), $css);
        $this->line('✔ css/fonts.css ('.count($sudah).' berkas font)');

        return true;
    }

    private function periksa(): int
    {
        $berkas = public_path('vendor/manifest.json');
        if (! is_file($berkas)) {
            $this->error('Manifest belum ada. Jalankan php artisan aset:lokal dulu.');
            return self::FAILURE;
        }

        $rusak = [];
        foreach (json_decode(File::get($berkas), true) as $rel => $info) {
            $p = public_path('vendor/'.$rel);
            if (! is_file($p) || hash_file('sha256', $p) !== $info['sha256']) {
                $rusak[] = $rel;
                $this->line("BERUBAH ATAU HILANG: vendor/{$rel}");
            }
        }

        if ($rusak) {
            PeringatanKeamanan::create([
                'jenis'  => 'aset_berubah',
                'pesan'  => count($rusak).' berkas pustaka berubah atau hilang: '.mb_substr(implode(', ', $rusak), 0, 150),
                'jumlah' => count($rusak),
            ]);
            return self::FAILURE;
        }

        $this->info('Semua berkas vendor cocok dengan manifest.');

        return self::SUCCESS;
    }
}