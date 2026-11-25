<?php

namespace App\Console\Commands;

use App\Models\LogAktivitas;
use App\Models\PeringatanKeamanan;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use ZipArchive;

class BackupVerifikasi extends Command
{
    protected $signature = 'backup:verifikasi {berkas? : Nama arsip data (bawaan: yang terbaru)} {--ekstrak= : Ekstrak isi arsip ke folder ini setelah lolos verifikasi}';
    protected $description = 'Memeriksa arsip backup: bisa dibuka, dan checksum semua berkas cocok dengan manifest';

    public function handle(): int
    {
        try {
            return $this->periksa();
        } catch (\Throwable $e) {
            $this->error('Verifikasi gagal: '.$e->getMessage());
            LogAktivitas::catat('backup_gagal', 'Verifikasi: '.mb_substr($e->getMessage(), 0, 130));
            PeringatanKeamanan::create([
                'jenis'  => 'backup_gagal',
                'pesan'  => 'Verifikasi backup gagal: '.mb_substr($e->getMessage(), 0, 180),
                'jumlah' => 1,
            ]);

            return self::FAILURE;
        }
    }

    private function periksa(): int
    {
        $pass = (string) config('cadangan.passphrase');
        $dir  = storage_path('app/backup');
        $arg  = $this->argument('berkas');

        if ($arg) {
            $jalur = is_file($arg) ? $arg : $dir.DIRECTORY_SEPARATOR.basename($arg);
        } else {
            $daftar = glob($dir.DIRECTORY_SEPARATOR.'data-*.zip') ?: [];
            sort($daftar);
            $jalur = end($daftar) ?: null;
        }
        if (! $jalur || ! is_file($jalur)) {
            throw new \RuntimeException('Arsip backup tidak ditemukan.');
        }

        $zip = new ZipArchive();
        if ($zip->open($jalur) !== true) {
            throw new \RuntimeException('Arsip tidak dapat dibuka (rusak).');
        }
        $zip->setPassword($pass);

        $json = $zip->getFromName('manifest.json');
        if ($json === false) {
            throw new \RuntimeException('Manifest tidak terbaca: kata sandi salah atau arsip rusak.');
        }
        $manifest = json_decode($json, true);

        $ok = $rusak = 0;
        foreach ($manifest['berkas'] as $nama => $info) {
            $s = $zip->getStream($nama);
            if (! $s) {
                $rusak++;
                $this->line("TIDAK TERBACA: {$nama}");
                continue;
            }

            $h = hash_init('sha256');
            $ukuran = 0;
            while (! feof($s)) {
                $buf = fread($s, 1048576);
                if ($buf === false) {
                    break;
                }
                hash_update($h, $buf);
                $ukuran += strlen($buf);
            }
            fclose($s);

            if (hash_final($h) === $info['sha256'] && $ukuran === $info['ukuran']) {
                $ok++;
            } else {
                $rusak++;
                $this->line("CHECKSUM BERBEDA: {$nama}");
            }
        }

        if ($rusak > 0) {
            throw new \RuntimeException("{$rusak} berkas rusak atau tidak cocok dari ".($ok + $rusak).' berkas.');
        }

        $this->info(basename($jalur).": {$ok} berkas terverifikasi (dibuat {$manifest['dibuat']}).");
        LogAktivitas::catat('backup_diverifikasi', basename($jalur).' | '.$ok.' berkas');

        if ($tujuan = $this->option('ekstrak')) {
            File::ensureDirectoryExists($tujuan);
            $zip->extractTo($tujuan);
            $this->warn("Diekstrak ke {$tujuan}. Isinya data Rahasia: hapus folder itu setelah selesai.");
        }

        $zip->close();

        return self::SUCCESS;
    }
}