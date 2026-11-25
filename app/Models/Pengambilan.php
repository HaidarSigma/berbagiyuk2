<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pengambilan extends Model
{
    protected $fillable = ['barang_id', 'petugas_id', 'status', 'foto_ambil', 'ditugaskan_at', 'diambil_at'];

    protected $casts = ['ditugaskan_at' => 'datetime', 'diambil_at' => 'datetime'];

    public function barang() { return $this->belongsTo(Barang::class); }
    public function petugas() { return $this->belongsTo(User::class, 'petugas_id'); }
}