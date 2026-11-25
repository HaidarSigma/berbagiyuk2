<?php

namespace App\Support;

use App\Models\LogAktivitas;
use App\Models\PeringatanKeamanan;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class Pantau
{
    private static ?string $kolomIp = null;
    private static bool $kolomIpDicek = false;

    private static function gagal()
    {
        return LogAktivitas::where(fn ($q) => $q
            ->where('aksi', 'like', '%gagal%')
            ->orWhere('aksi', 'like', '%salah%')
            ->orWhere('aksi', 'like', '%terkunci%'));
    }

    private static function kolomIp(): ?string
    {
        if (! self::$kolomIpDicek) {
            self::$kolomIp = collect(['ip', 'ip_address', 'alamat_ip'])
                ->first(fn ($k) => Schema::hasColumn('log_aktivitas', $k));
            self::$kolomIpDicek = true;
        }

        return self::$kolomIp;
    }

    // Dipanggil setiap ada baris baru di Log Aktivitas
    public static function periksa(LogAktivitas $log): void
    {
        try {
            $aksi = (string) $log->aksi;
            if (! Str::contains($aksi, ['gagal', 'salah', 'terkunci'])) {
                return;
            }

            // 1. Lonjakan percobaan gagal secara keseluruhan
            $n = self::gagal()->where('created_at', '>=', now()->subMinutes(10))->count();
            if ($n >= 10) {
                self::angkat('lonjakan_gagal', "{$n} percobaan gagal (login, PIN, atau kode) dalam 10 menit terakhir.", $n);
            }

            // 2. Banyak PIN terkunci
            if ($aksi === 'pin_terkunci') {
                $k = LogAktivitas::where('aksi', 'pin_terkunci')->where('created_at', '>=', now()->subHour())->count();
                if ($k >= 3) {
                    self::angkat('banyak_terkunci', "{$k} kali PIN terkunci dalam 1 jam terakhir.", $k);
                }
            }

            // 3. Satu alamat IP (bila log menyimpan IP)
            $kolom = self::kolomIp();
            if ($kolom && $log->{$kolom}) {
                $i = self::gagal()->where($kolom, $log->{$kolom})->where('created_at', '>=', now()->subMinutes(10))->count();
                if ($i >= 8) {
                    self::angkat('satu_ip', "{$i} percobaan gagal dari satu alamat IP ({$log->{$kolom}}) dalam 10 menit.", $i);
                }
            }
        } catch (\Throwable $e) {
            report($e);   // pemantauan tidak boleh mengganggu pencatatan log
        }
    }

    private static function angkat(string $jenis, string $pesan, int $jumlah): void
    {
        // Jenis yang sama tidak diulang dalam 30 menit
        if (PeringatanKeamanan::where('jenis', $jenis)->where('created_at', '>=', now()->subMinutes(30))->exists()) {
            return;
        }

        PeringatanKeamanan::create(compact('jenis', 'pesan', 'jumlah'));

        // Email dikirim setelah respons selesai supaya tidak memperlambat permintaan
        dispatch(function () use ($pesan) {
            foreach (User::where('role', 'admin')->where('aktif', true)->pluck('email') as $email) {
                try {
                    Mail::raw("[BerbagiYuk] Peringatan keamanan\n\n{$pesan}\n\nPeriksa Log Aktivitas di panel admin.", fn ($m) => $m
                        ->to($email)->subject('Peringatan keamanan BerbagiYuk'));
                } catch (\Throwable $e) {
                    report($e);
                }
            }
        })->afterResponse();
    }
}