<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BarangMedia extends Model
{
    protected $table = 'barang_medias';
    protected $fillable = ['barang_id', 'jenis', 'path', 'urutan'];

    public function barang() { return $this->belongsTo(Barang::class); }
}