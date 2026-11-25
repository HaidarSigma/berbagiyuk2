<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pengambilans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('barang_id')->unique()->constrained('barangs')->cascadeOnDelete();
            $table->foreignId('petugas_id')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('status', ['baru', 'ditugaskan', 'selesai'])->default('baru');
            $table->string('foto_ambil')->nullable();
            $table->timestamp('ditugaskan_at')->nullable();
            $table->timestamp('diambil_at')->nullable();
            $table->timestamps();
        });

        // Status barang: tambah 'menunggu_ambil'. Barang yang belum disetujui ikut statusnya.
        // Barang lama yang sudah disetujui dianggap sudah ada di posko ('tersedia').
        DB::statement("ALTER TABLE barangs MODIFY status ENUM('menunggu_ambil','tersedia','diproses','selesai','ditarik') NOT NULL DEFAULT 'menunggu_ambil'");
        DB::table('barangs')->where('status', 'tersedia')->whereIn('verifikasi', ['menunggu', 'ditolak'])->update(['status' => 'menunggu_ambil']);

        // Tugas lama tahap 'diambil' digabung ke 'ditugaskan' (tugas sekarang hanya untuk pengiriman)
        DB::table('tugas')->where('status', 'diambil')->update(['status' => 'ditugaskan']);
        DB::statement("ALTER TABLE tugas MODIFY status ENUM('baru','ditugaskan','selesai') NOT NULL DEFAULT 'baru'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE tugas MODIFY status ENUM('baru','ditugaskan','diambil','selesai') NOT NULL DEFAULT 'baru'");

        DB::table('barangs')->where('status', 'menunggu_ambil')->update(['status' => 'tersedia']);
        DB::statement("ALTER TABLE barangs MODIFY status ENUM('tersedia','diproses','selesai','ditarik') NOT NULL DEFAULT 'tersedia'");

        Schema::dropIfExists('pengambilans');
    }
};