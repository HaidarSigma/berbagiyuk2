<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('barangs', 'jumlah')) {
            Schema::table('barangs', function (Blueprint $table) {
                $table->decimal('jumlah', 10, 2)->nullable()->after('kondisi');
            });
        }

        if (! Schema::hasColumn('barangs', 'satuan')) {
            Schema::table('barangs', function (Blueprint $table) {
                $table->string('satuan', 30)->nullable()->after('jumlah');
            });
        }
    }

    public function down(): void
    {
        $columns = array_values(array_filter(
            ['jumlah', 'satuan'],
            fn ($column) => Schema::hasColumn('barangs', $column)
        ));

        if ($columns) {
            Schema::table('barangs', function (Blueprint $table) use ($columns) {
                $table->dropColumn($columns);
            });
        }
    }
};
