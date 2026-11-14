<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('barangs', function (Blueprint $table) {
            $table->string('token', 40)->nullable()->unique()->after('id');
        });

        // Barang yang sudah ada ikut diberi ID
        foreach (DB::table('barangs')->whereNull('token')->pluck('id') as $id) {
            DB::table('barangs')->where('id', $id)->update(['token' => 'BY-'.Str::upper(Str::random(24))]);
        }
    }

    public function down(): void
    {
        Schema::table('barangs', function (Blueprint $table) {
            $table->dropUnique(['token']);
            $table->dropColumn('token');
        });
    }
};