<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Berita extends Model
{
    protected $table = 'beritas';
    protected $fillable = ['judul', 'ringkasan', 'isi', 'foto', 'tanggal', 'terbit'];
    protected $casts = ['tanggal' => 'date', 'terbit' => 'boolean'];
}