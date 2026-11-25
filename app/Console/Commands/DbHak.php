<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use PDO;

class DbHak extends Command
{
    protected $signature = 'db:hak {--host=127.0.0.1} {--port=3306} {--user=root}';
    protected $description = 'Menata hak akun database aplikasi (hak akses minimal; log audit tidak bisa diubah)';

    private function id(string $nama): string
    {
        return '`'.str_replace('`', '``', $nama).'`';
    }

    public function handle(): int
    {
        $db  = config('database.connections.mysql.database');
        $app = config('database.connections.mysql.username');

        if (! $app || $app === 'root') {
            $this->error('DB_USERNAME di .env masih root. Isi dengan akun aplikasi (contoh: berbagiyuk_app).');
            return self::FAILURE;
        }

        $pass = (string) $this->secret('Password MySQL untuk akun '.$this->option('user').' (Enter bila kosong)');

        try {
            $pdo = new PDO(
                "mysql:host={$this->option('host')};port={$this->option('port')}",
                $this->option('user'),
                $pass,
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );
        } catch (\Throwable $e) {
            $this->error('Gagal masuk ke MySQL: '.$e->getMessage());
            return self::FAILURE;
        }

        $ambil = function (string $sql, array $param) use ($pdo) {
            $s = $pdo->prepare($sql);
            $s->execute($param);
            return $s->fetchAll(PDO::FETCH_COLUMN);
        };

        $hosts = $ambil('SELECT host FROM mysql.user WHERE user = ?', [$app]);
        if (! $hosts) {
            $this->error("Akun '{$app}' tidak ditemukan di MySQL.");
            return self::FAILURE;
        }

        $tabel = $ambil("SELECT table_name FROM information_schema.tables WHERE table_schema = ? AND table_type = 'BASE TABLE' ORDER BY table_name", [$db]);
        $kolom = $ambil('SELECT column_name FROM information_schema.columns WHERE table_schema = ? AND table_name = ?', [$db, 'users']);
        $bolehUbah = implode(', ', array_map(fn ($c) => $this->id($c), array_values(array_diff($kolom, ['id', 'role']))));

        foreach ($hosts as $host) {
            $akun = $pdo->quote($app).'@'.$pdo->quote($host);
            $pdo->exec("REVOKE ALL PRIVILEGES, GRANT OPTION FROM {$akun}");

            foreach ($tabel as $t) {
                $obj = $this->id($db).'.'.$this->id($t);

                if ($t === 'migrations') {
                    continue;
                } elseif ($t === 'log_aktivitas') {
                    $pdo->exec("GRANT SELECT, INSERT ON {$obj} TO {$akun}");
                } elseif ($t === 'users') {
                    $pdo->exec("GRANT SELECT, INSERT, DELETE ON {$obj} TO {$akun}");
                    $pdo->exec("GRANT UPDATE ({$bolehUbah}) ON {$obj} TO {$akun}");
                } else {
                    $pdo->exec("GRANT SELECT, INSERT, UPDATE, DELETE ON {$obj} TO {$akun}");
                }
            }
            $this->info("Hak ditata untuk {$app}@{$host}: ".count($tabel).' tabel diperiksa.');
        }

        $this->line('log_aktivitas: SELECT dan INSERT saja | users: kolom id dan role tidak bisa diubah | migrations: tanpa hak');
        $this->warn('Setelah menambah tabel baru lewat migration, jalankan perintah ini lagi agar akun aplikasi mendapat haknya.');

        return self::SUCCESS;
    }
}