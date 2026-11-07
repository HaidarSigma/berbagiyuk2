<?php

namespace App\Console\Commands;

use App\Models\LogAktivitas;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class DbBackup extends Command
{
    protected $signature = 'db:backup {--simpan=14 : Jumlah berkas cadangan yang disimpan}';
    protected $description = 'Membuat cadangan database BerbagiYuk dan menghapus cadangan lama';

    public function handle(): int
    {
        $dir = storage_path('app/backup');
        if (! is_dir($dir)) {
            mkdir($dir, 0775, true);
        }

        $file = $dir.DIRECTORY_SEPARATOR.'berbagiyuk_'.now()->format('Ymd_His').'.sql';
        $out  = null;

        try {
            $db  = DB::connection('mysql_backup');
            $pdo = $db->getPdo();

            // Satu gambaran data yang konsisten selama proses berjalan
            $pdo->exec('START TRANSACTION WITH CONSISTENT SNAPSHOT');

            $out = fopen($file, 'w');
            fwrite($out, '-- Cadangan BerbagiYuk, '.now()->toDateTimeString()."\n");
            fwrite($out, "SET NAMES utf8mb4;\nSET FOREIGN_KEY_CHECKS=0;\n\n");

            $tabel = array_map(
                fn ($r) => array_values((array) $r)[0],
                $db->select("SHOW FULL TABLES WHERE Table_type = 'BASE TABLE'")
            );

            foreach ($tabel as $t) {
                $ddl = (array) $db->selectOne("SHOW CREATE TABLE `{$t}`");
                fwrite($out, "DROP TABLE IF EXISTS `{$t}`;\n".$ddl['Create Table'].";\n\n");

                $kolom = null;
                foreach ($db->table($t)->cursor() as $baris) {
                    $baris = (array) $baris;
                    $kolom ??= implode(',', array_map(fn ($k) => "`{$k}`", array_keys($baris)));
                    $nilai = implode(',', array_map(
                        fn ($v) => $v === null ? 'NULL' : $pdo->quote((string) $v),
                        $baris
                    ));
                    fwrite($out, "INSERT INTO `{$t}` ({$kolom}) VALUES ({$nilai});\n");
                }
                fwrite($out, "\n");
            }

            fwrite($out, "SET FOREIGN_KEY_CHECKS=1;\n");
            fclose($out);
            $pdo->exec('ROLLBACK');
        } catch (\Throwable $e) {
            if (is_resource($out)) {
                fclose($out);
            }
            @unlink($file);

            $pesan = mb_substr($e->getMessage(), 0, 200);
            $this->error('Backup gagal: '.$pesan);
            LogAktivitas::catat('backup_gagal', $pesan);

            return self::FAILURE;
        }

        $kb = round(filesize($file) / 1024, 1);

        $semua = glob($dir.DIRECTORY_SEPARATOR.'berbagiyuk_*.sql');
        rsort($semua);
        foreach (array_slice($semua, (int) $this->option('simpan')) as $lama) {
            unlink($lama);
        }

        LogAktivitas::catat('backup_berhasil', basename($file).' | '.$kb.' KB');
        $this->info("Backup berhasil: {$file} ({$kb} KB)");

        return self::SUCCESS;
    }
}