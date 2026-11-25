<?php

namespace App\Console\Commands;

use App\Support\Gambar;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class FotoBersihkan extends Command
{
    protected $signature = 'foto:bersihkan {--cek : Hanya periksa, tidak mengubah berkas}';
    protected $description = 'Memeriksa dan membersihkan metadata GPS/EXIF pada foto publik';

    public function handle(): int
    {
        if (! extension_loaded('gd')) {
            $this->error('Ekstensi GD belum aktif di php.ini.');
            return self::FAILURE;
        }

        $disk    = Storage::disk('public');
        $periksa = $gps = $bersih = 0;

        foreach (['barang', 'profil', 'kebutuhan', 'berita'] as $folder) {
            foreach ($disk->allFiles($folder) as $rel) {
                $jalur = $disk->path($rel);
                $periksa++;

                if (Gambar::punyaGps($jalur)) {
                    $gps++;
                    $this->line("GPS ditemukan: {$rel}");
                }
                if (! $this->option('cek') && Gambar::bersihkanBerkas($jalur)) {
                    $bersih++;
                }
            }
        }

        $this->info("Diperiksa: {$periksa} berkas | berisi GPS: {$gps}".($this->option('cek') ? '' : " | ditulis ulang tanpa metadata: {$bersih}"));

        return self::SUCCESS;
    }
}