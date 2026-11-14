<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Donasi extends Model
{
    protected $fillable = ['user_id', 'lokasi', 'latitude', 'longitude'];

    public function user() { return $this->belongsTo(User::class); }
    public function barangs() { return $this->hasMany(Barang::class); }
}