<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedInteger('sesi_versi')->default(0);
        });

        Schema::create('peringatan_keamanan', function (Blueprint $table) {
            $table->id();
            $table->string('jenis', 40);
            $table->string('pesan', 255);
            $table->unsignedInteger('jumlah')->default(0);
            $table->timestamp('dibaca_at')->nullable();
            $table->timestamps();
            $table->index(['jenis', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('peringatan_keamanan');
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('sesi_versi');
        });
    }
};