<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
protected $fillable = ['name', 'email', 'password', 'no_hp', 'kota', 'role', 'terverifikasi', 'alamat', 'latitude', 'longitude', 'jenis_penerima', 'dokumen', 'status_penerima'];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */

protected $hidden = [
    'password',
    'pin',
    'remember_token',
    'totp_rahasia', 'totp_kode_pulih'
];

protected $casts = [
    'email_verified_at' => 'datetime',
    'password' => 'hashed',
    'pin_terkunci_sampai' => 'datetime',
    'no_hp' => 'encrypted',
    'alamat' => 'encrypted',
    'latitude' => 'encrypted',
    'longitude' => 'encrypted',
    'password_changed_at' => 'datetime',
    'totp_rahasia' => 'encrypted', 'totp_aktif' => 'boolean', 'totp_kode_pulih' => 'array', 'totp_kunci_sampai' => 'datetime',
];

public function passwordBerlakuSampai(): \Illuminate\Support\Carbon
{
    return ($this->password_changed_at ?? $this->created_at)->copy()->addMonths(3);
}

public function passwordKedaluwarsa(): bool
{
    return now()->gte($this->passwordBerlakuSampai());
}

public function penghalangHapusAkun(): ?string
{
    if ($this->role === 'donatur'
        && Barang::where('user_id', $this->id)->where('verifikasi', 'disetujui')
            ->whereIn('status', ['menunggu_ambil', 'tersedia', 'diproses'])->exists()) {
        return 'Masih ada barangmu yang menunggu diambil, tersimpan di posko, atau sedang diantar. Akun bisa dihapus setelah semuanya selesai atau barang ditarik (hanya sebelum diambil).';
    }

    if ($this->role === 'penerima'
        && Permintaan::where('penerima_id', $this->id)->where('status', 'disetujui')
            ->whereHas('barang', fn ($q) => $q->where('status', 'diproses'))->exists()) {
        return 'Masih ada permintaanmu yang disetujui dan sedang diproses. Akun bisa dihapus setelah barang diterima.';
    }

    return null;
}

// Mengakhiri semua sesi lain: sesi dengan nomor versi lama ditolak oleh EnsurePin
public function putusSesi(bool $pertahankanSesiIni = false): void
{
    $this->forceFill(['sesi_versi' => (int) $this->sesi_versi + 1])->save();

    if ($pertahankanSesiIni) {
        session(['sesi_versi' => (int) $this->sesi_versi]);
        session()->regenerate();
    }
}
}
