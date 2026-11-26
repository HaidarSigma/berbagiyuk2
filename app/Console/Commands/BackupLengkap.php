<?php

namespace App\Console\Commands;

use App\Models\LogAktivitas;
use App\Models\PeringatanKeamanan;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use ZipArchive;
use App\Support\StatusBackup;

class BackupLengkap extends Command
{
    protected $signature = 'backup:lengkap {--bersihkan-sql-lama : Hapus dump .sql tanpa enkripsi yang tersisa di storage/app/backup}';
    protected $description = 'Backup lengkap terenkripsi (database + berkas unggahan) beserta salinan kunci, dengan manifest checksum';

    // Berkas yang sudah terkompres tidak dikompres lagi (lebih cepat)
    private const TANPA_KOMPRESI = ['jpg', 'jpeg', 'png', 'webp', 'mp4', 'webm', 'mov', 'enc'];
    private string $hasil = '';

    // nama di dalam arsip => lokasi asli
    private function sumber(): array
    {
        return [
            'berkas/public'       => storage_path('app/public'),
            'berkas/video-barang' => storage_path('app/video-barang'),
            'berkas/dokumen'      => storage_path('app/dokumen'),
            'berkas/bukti'        => storage_path('app/bukti'),
            'berkas/hero'         => public_path('img/hero'),
        ];
    }

    public function handle(): int
{
    StatusBackup::mulai('lengkap');

    try {
        $this->kerjakan();
        StatusBackup::selesai('lengkap', $this->hasil);

        return self::SUCCESS;
    } catch (\Throwable $e) {
        StatusBackup::gagal('lengkap', $e->getMessage());
        $this->error('Backup gagal: '.$e->getMessage());
        report($e);
        LogAktivitas::catat('backup_gagal', mb_substr($e->getMessage(), 0, 150));
        PeringatanKeamanan::create([
            'jenis'  => 'backup_gagal',
            'pesan'  => 'Backup lengkap gagal: '.mb_substr($e->getMessage(), 0, 180),
            'jumlah' => 1,
        ]);

        return self::FAILURE;
    }
}

    private function putar(string $dir, string $pola, int $simpan): void
    {
        $daftar = glob($dir.DIRECTORY_SEPARATOR.$pola) ?: [];
        rsort($daftar);   // nama berisi tanggal, jadi yang terbaru di depan

        foreach (array_slice($daftar, max(1, $simpan)) as $lama) {
            File::delete($lama);
        }
    }

    private function kerjakan(): void
    {
        $pass = (string) config('cadangan.passphrase');
        if (strlen($pass) < 24) {
            throw new \RuntimeException('BACKUP_PASSPHRASE di .env belum diisi (minimal 24 karakter).');
        }
        if (! class_exists(ZipArchive::class) || ! defined('ZipArchive::EM_AES_256')) {
            throw new \RuntimeException('Ekstensi zip dengan enkripsi AES-256 tidak tersedia di PHP ini.');
        }

        $mulai    = time();
        $kode     = now()->format('Ymd-His');
        $dir      = storage_path('app/backup');
        $dirKunci = storage_path('app/backup-kunci');
        File::ensureDirectoryExists($dir);
        File::ensureDirectoryExists($dirKunci);

        // 1. Dump database memakai perintah db:backup yang sudah ada
        Artisan::call('db:backup');
        $sql = collect(File::files($dir))
            ->filter(fn ($f) => $f->getExtension() === 'sql' && $f->getMTime() >= $mulai - 2)
            ->sortByDesc(fn ($f) => $f->getMTime())
            ->first();
        if (! $sql) {
            throw new \RuntimeException('Dump database tidak ditemukan. Jalankan php artisan db:backup untuk melihat errornya.');
        }

        // 2. Arsip data terenkripsi: database + berkas unggahan + manifest
        $manifest = ['dibuat' => now()->toIso8601String(), 'laravel' => app()->version(), 'berkas' => []];
        $namaZip  = "data-{$kode}.zip";
        $jalurZip = $dir.DIRECTORY_SEPARATOR.$namaZip;

        $zip = new ZipArchive();
        if ($zip->open($jalurZip, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new \RuntimeException('Tidak dapat membuat berkas arsip.');
        }
        $zip->setPassword($pass);

        $tambah = function (string $jalur, string $nama) use ($zip, &$manifest) {
            $zip->addFile($jalur, $nama);
            $zip->setEncryptionName($nama, ZipArchive::EM_AES_256);
            if (in_array(strtolower(pathinfo($jalur, PATHINFO_EXTENSION)), self::TANPA_KOMPRESI, true)) {
                $zip->setCompressionName($nama, ZipArchive::CM_STORE);
            }
            $manifest['berkas'][$nama] = ['ukuran' => filesize($jalur), 'sha256' => hash_file('sha256', $jalur)];
        };

        $tambah($sql->getRealPath(), 'database.sql');

        $hitung = [];
        foreach ($this->sumber() as $nama => $asal) {
            $hitung[$nama] = 0;
            if (! is_dir($asal)) {
                continue;
            }
            foreach (File::allFiles($asal) as $f) {
                $tambah($f->getRealPath(), $nama.'/'.str_replace('\\', '/', $f->getRelativePathname()));
                $hitung[$nama]++;
            }
        }
        $manifest['jumlah'] = $hitung;

        $zip->addFromString('manifest.json', json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        $zip->setEncryptionName('manifest.json', ZipArchive::EM_AES_256);

        if (! $zip->close() || ! is_file($jalurZip)) {
            throw new \RuntimeException('Gagal menulis arsip (ruang disk habis?).');
        }

        // 3. Kunci dan konfigurasi: arsip terpisah, disimpan di folder lain
        $namaKunci  = "kunci-{$kode}.zip";
        $jalurKunci = $dirKunci.DIRECTORY_SEPARATOR.$namaKunci;

        $zk = new ZipArchive();
        if ($zk->open($jalurKunci, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new \RuntimeException('Tidak dapat membuat arsip kunci.');
        }
        $zk->setPassword($pass);
        if (is_file(base_path('.env'))) {
            $zk->addFile(base_path('.env'), 'env.txt');
            $zk->setEncryptionName('env.txt', ZipArchive::EM_AES_256);
        }
        $zk->addFromString('APP_KEY.txt', (string) config('app.key'));
        $zk->setEncryptionName('APP_KEY.txt', ZipArchive::EM_AES_256);
        if (! $zk->close()) {
            throw new \RuntimeException('Gagal menulis arsip kunci.');
        }

        // 4. Dump tanpa enkripsi tidak boleh tertinggal
        File::delete($sql->getRealPath());
        if ($this->option('bersihkan-sql-lama')) {
            foreach (File::files($dir) as $f) {
                if ($f->getExtension() === 'sql') {
                    File::delete($f->getRealPath());
                }
            }
        }

        // 5. Rotasi
        $simpan = (int) config('cadangan.simpan');
        $this->putar($dir, 'data-*.zip', $simpan);
        $this->putar($dirKunci, 'kunci-*.zip', 5);

        // 6. Salinan di lokasi lain
        $salinan = config('cadangan.salinan_dir');
        if ($salinan) {
            File::ensureDirectoryExists($salinan);
            foreach ([[$jalurZip, $namaZip], [$jalurKunci, $namaKunci]] as [$asal, $nama]) {
                if (! File::copy($asal, $salinan.DIRECTORY_SEPARATOR.$nama)) {
                    throw new \RuntimeException("Gagal menyalin {$nama} ke {$salinan}.");
                }
            }
            $this->putar($salinan, 'data-*.zip', $simpan);
            $this->putar($salinan, 'kunci-*.zip', 5);
        }

        $mb = round(filesize($jalurZip) / 1048576, 1);
        $this->hasil = "{$namaZip} | {$mb} MB | ".array_sum($hitung).' berkas';
        $this->info("Backup selesai: {$namaZip} ({$mb} MB)");
        foreach ($hitung as $n => $j) {
            $this->line("  {$n}: {$j} berkas");
        }
        $this->line("  Kunci: {$namaKunci} | Salinan luar lokasi: ".($salinan ?: 'belum diatur (isi BACKUP_SALINAN_DIR)'));

        LogAktivitas::catat('backup_lengkap', "{$namaZip} | {$mb} MB | ".array_sum($hitung).' berkas');
    }
}