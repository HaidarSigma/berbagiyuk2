<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PeringatanKeamanan extends Model
{
    protected $table = 'peringatan_keamanan';
    protected $fillable = ['jenis', 'pesan', 'jumlah', 'dibaca_at'];
    protected $casts = ['dibaca_at' => 'datetime'];
}