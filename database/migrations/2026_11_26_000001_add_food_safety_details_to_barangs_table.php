<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('barangs', 'tanggal_kedaluwarsa')) {
            Schema::table('barangs', function (Blueprint $table) {
                $table->date('tanggal_kedaluwarsa')->nullable()->after('kondisi');
            });
        }

        if (! Schema::hasColumn('barangs', 'cara_penyimpanan')) {
            Schema::table('barangs', function (Blueprint $table) {
                $table->string('cara_penyimpanan', 200)->nullable()->after('tanggal_kedaluwarsa');
            });
        }
    }

    public function down(): void
    {
        $columns = array_values(array_filter(
            ['tanggal_kedaluwarsa', 'cara_penyimpanan'],
            fn ($column) => Schema::hasColumn('barangs', $column)
        ));

        if ($columns) {
            Schema::table('barangs', function (Blueprint $table) use ($columns) {
                $table->dropColumn($columns);
            });
        }
    }
};
