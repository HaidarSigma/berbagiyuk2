<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tugas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('permintaan_id')->unique()->constrained('permintaans')->cascadeOnDelete();
            $table->foreignId('petugas_id')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('status', ['baru', 'ditugaskan', 'diambil', 'selesai'])->default('baru');
            $table->text('kode_serah');
            $table->string('foto_ambil')->nullable();
            $table->string('foto_serah')->nullable();
            $table->text('catatan')->nullable();
            $table->unsignedTinyInteger('kode_salah')->default(0);
            $table->timestamp('ditugaskan_at')->nullable();
            $table->timestamp('diambil_at')->nullable();
            $table->timestamp('selesai_at')->nullable();
            $table->timestamps();
        });

        // Permintaan yang sudah disetujui sebelum fitur ini ada
        $ids = DB::table('permintaans')
            ->join('barangs', 'barangs.id', '=', 'permintaans.barang_id')
            ->where('permintaans.status', 'disetujui')
            ->where('barangs.status', 'diproses')
            ->pluck('permintaans.id');

        foreach ($ids as $id) {
            DB::table('tugas')->insert([
                'permintaan_id' => $id,
                'kode_serah'    => Crypt::encryptString(str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT)),
                'created_at'    => now(),
                'updated_at'    => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('tugas');
    }
};