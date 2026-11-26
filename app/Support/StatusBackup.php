<?php

namespace App\Support;

use Carbon\Carbon;

class StatusBackup
{
    private static function jalur(): string
    {
        return storage_path('app/backup-status.json');
    }

    public static function baca(): array
    {
        $j = is_file(self::jalur()) ? json_decode((string) file_get_contents(self::jalur()), true) : null;

        return is_array($j) ? $j : [];
    }

    private static function tulis(string $jenis, array $data): void
    {
        $semua = self::baca();
        $semua[$jenis] = array_merge($semua[$jenis] ?? [], $data);
        file_put_contents(self::jalur(), json_encode($semua, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), LOCK_EX);
    }

    // Diminta dari halaman admin; prosesnya belum benar-benar mulai
    public static function minta(string $jenis): void
    {
        self::tulis($jenis, ['status' => 'berjalan', 'diminta' => now()->toIso8601String(), 'mulai' => now()->toIso8601String(), 'akhir' => null, 'pesan' => null]);
    }

    public static function mulai(string $jenis): void
    {
        self::tulis($jenis, ['status' => 'berjalan', 'diminta' => null, 'mulai' => now()->toIso8601String(), 'akhir' => null, 'pesan' => null]);
    }

    public static function selesai(string $jenis, string $pesan): void
    {
        self::tulis($jenis, ['status' => 'selesai', 'akhir' => now()->toIso8601String(), 'sukses' => now()->toIso8601String(), 'pesan' => $pesan]);
    }

    public static function gagal(string $jenis, string $pesan): void
    {
        self::tulis($jenis, ['status' => 'gagal', 'diminta' => null, 'akhir' => now()->toIso8601String(), 'pesan' => mb_substr($pesan, 0, 200)]);
    }

    // Status "berjalan" lebih dari 2 jam dianggap proses yang terhenti
    public static function berjalan(string $jenis): bool
    {
        $s = self::baca()[$jenis] ?? null;

        return $s && ($s['status'] ?? '') === 'berjalan' && isset($s['mulai'])
            && Carbon::parse($s['mulai'])->diffInMinutes(now()) < 120;
    }

    // Belum ada backup lengkap yang berhasil dalam $jam jam terakhir
    public static function basi(int $jam = 26): bool
    {
        $s = self::baca()['lengkap']['sukses'] ?? null;

        return ! $s || Carbon::parse($s)->diffInHours(now()) >= $jam;
    }

    // Menjalankan perintah artisan di latar belakang. Hanya perintah daftar putih yang boleh dikirim ke sini.
    public static function lepas(string $perintah): void
    {
        $php     = config('cadangan.php') ?: PHP_BINARY;
        $artisan = base_path('artisan');

        if (PHP_OS_FAMILY === 'Windows') {
            pclose(popen('start /B "" "'.$php.'" "'.$artisan.'" '.$perintah.' > NUL 2>&1', 'r'));
        } else {
            exec(escapeshellarg($php).' '.escapeshellarg($artisan).' '.$perintah.' > /dev/null 2>&1 &');
        }
    }
}