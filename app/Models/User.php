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
];

protected $casts = [
    'email_verified_at' => 'datetime',
    'password' => 'hashed',
    'pin_terkunci_sampai' => 'datetime',
    'no_hp' => 'encrypted',
    'alamat' => 'encrypted',
    'password_changed_at' => 'datetime',
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
        && Barang::where('user_id', $this->id)->where('status', 'diproses')->exists()) {
        return 'Masih ada barangmu yang sedang diproses untuk penerima. Akun bisa dihapus setelah proses selesai.';
    }

    if ($this->role === 'penerima'
        && Permintaan::where('penerima_id', $this->id)->where('status', 'disetujui')
            ->whereHas('barang', fn ($q) => $q->where('status', 'diproses'))->exists()) {
        return 'Masih ada permintaanmu yang disetujui dan sedang diproses. Akun bisa dihapus setelah barang diterima.';
    }

    return null;
}
}
