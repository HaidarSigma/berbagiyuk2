<?php

namespace App\Console\Commands;

use App\Models\LogAktivitas;
use App\Models\User;
use Illuminate\Console\Command;

class TotpReset extends Command
{
    protected $signature = 'totp:reset {email}';
    protected $description = 'Mengatur ulang verifikasi 2 langkah sebuah akun (pemulihan oleh atasan)';

    public function handle(): int
    {
        $u = User::where('email', $this->argument('email'))->first();
        if (! $u) {
            $this->error('Akun tidak ditemukan.');
            return self::FAILURE;
        }
        if (! $this->confirm("Reset 2 langkah untuk {$u->email} ({$u->role})? Pastikan identitas pemilik sudah dicek di luar sistem.")) {
            return self::SUCCESS;
        }

        $u->forceFill([
            'totp_rahasia' => null, 'totp_aktif' => false, 'totp_terakhir' => 0,
            'totp_kode_pulih' => null, 'totp_gagal' => 0, 'totp_kunci_sampai' => null,
        ])->save();
        $u->putusSesi();
        LogAktivitas::catat('totp_direset', "Akun #{$u->id}");

        $this->info('Selesai. Pemilik akan diminta mendaftarkan ulang aplikasi autentikator saat login berikutnya.');

        return self::SUCCESS;
    }
}