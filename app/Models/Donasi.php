<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Donasi extends Model
{
    protected $fillable = ['user_id', 'kebutuhan_mendesak_id', 'lokasi', 'latitude', 'longitude'];

    protected $casts = [
        'latitude' => 'encrypted',
        'longitude' => 'encrypted',
    ];

    public function user() { return $this->belongsTo(User::class); }
    public function barangs() { return $this->hasMany(Barang::class); }
    public function kebutuhanMendesak() { return $this->belongsTo(KebutuhanMendesak::class); }
}