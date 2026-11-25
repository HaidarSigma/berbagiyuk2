<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use PDO;

class BackupBandingkan extends Command
{
    protected $signature = 'backup:bandingkan {db_uji=berbagiyuk_uji} {--host=127.0.0.1} {--port=3306} {--user=root}';
    protected $description = 'Membandingkan jumlah baris tiap tabel antara database asli dan hasil pemulihan percobaan';

    private function id(string $nama): string
    {
        return '`'.str_replace('`', '``', $nama).'`';
    }

    public function handle(): int
    {
        $asli = config('database.connections.mysql.database');
        $uji  = $this->argument('db_uji');
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

        $s = $pdo->prepare("SELECT table_name FROM information_schema.tables WHERE table_schema = ? AND table_type = 'BASE TABLE' ORDER BY table_name");
        $s->execute([$asli]);

        $baris = [];
        $beda  = 0;
        foreach ($s->fetchAll(PDO::FETCH_COLUMN) as $t) {
            $a = (int) $pdo->query('SELECT COUNT(*) FROM '.$this->id($asli).'.'.$this->id($t))->fetchColumn();

            try {
                $b = (int) $pdo->query('SELECT COUNT(*) FROM '.$this->id($uji).'.'.$this->id($t))->fetchColumn();
            } catch (\Throwable $e) {
                $b = null;
            }

            $status = $b === null ? 'TABEL HILANG' : ($a === $b ? 'sama' : 'selisih '.($b - $a));
            if ($status !== 'sama') {
                $beda++;
            }
            $baris[] = [$t, $a, $b ?? '-', $status];
        }

        $this->table(['Tabel', 'Database asli', 'Hasil pemulihan', 'Status'], $baris);
        $this->line($beda === 0
            ? 'Semua tabel sama.'
            : "{$beda} tabel berbeda. Selisih kecil wajar bila ada aktivitas setelah backup dibuat; tabel hilang atau selisih besar berarti pemulihan gagal.");

        return self::SUCCESS;
    }
}