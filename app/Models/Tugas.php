<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tugas extends Model
{
    protected $table = 'tugas';

    protected $fillable = ['permintaan_id', 'petugas_id', 'status', 'kode_serah', 'foto_ambil', 'foto_serah', 'catatan', 'kode_salah', 'ditugaskan_at', 'diambil_at', 'selesai_at'];

    protected $casts = [
        'kode_serah'    => 'encrypted',
        'ditugaskan_at' => 'datetime',
        'diambil_at'    => 'datetime',
        'selesai_at'    => 'datetime',
    ];

    public function permintaan() { return $this->belongsTo(Permintaan::class); }
    public function petugas() { return $this->belongsTo(User::class, 'petugas_id'); }

 // Jarak garis lurus dari posko (atau dari donatur bila posko belum diisi) ke penerima, dalam km
public function jarakKm(): ?float
{
    $b = $this->permintaan->barang;
    $p = $this->permintaan->penerima;

    $lat = config('posko.latitude') ?? $b->latitude;
    $lng = config('posko.longitude') ?? $b->longitude;
    if (! $lat || ! $p->latitude) {
        return null;
    }

    $dLat = deg2rad((float) $p->latitude - (float) $lat);
    $dLng = deg2rad((float) $p->longitude - (float) $lng);
    $a = sin($dLat / 2) ** 2
       + cos(deg2rad((float) $lat)) * cos(deg2rad((float) $p->latitude)) * sin($dLng / 2) ** 2;

    return round(2 * 6371 * asin(sqrt($a)), 1);
}
}