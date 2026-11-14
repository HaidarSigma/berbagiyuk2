<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tabel penghubung dihapus lebih dulu karena merujuk ke tabel komunitas
        Schema::dropIfExists('komunitas_anggota');
        Schema::dropIfExists('favorit');
        Schema::dropIfExists('komunitas');
    }

    public function down(): void
    {
        // Fitur dihapus permanen, tidak ada yang dipulihkan
    }
};