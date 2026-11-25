<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->text('totp_rahasia')->nullable();
            $table->boolean('totp_aktif')->default(false);
            $table->unsignedBigInteger('totp_terakhir')->default(0);   // langkah waktu terakhir yang dipakai (anti pemakaian ulang)
            $table->text('totp_kode_pulih')->nullable();
            $table->unsignedTinyInteger('totp_gagal')->default(0);
            $table->timestamp('totp_kunci_sampai')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['totp_rahasia', 'totp_aktif', 'totp_terakhir', 'totp_kode_pulih', 'totp_gagal', 'totp_kunci_sampai']);
        });
    }
};