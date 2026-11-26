<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('donasis', 'kebutuhan_mendesak_id')) {
            Schema::table('donasis', function (Blueprint $table) {
                $table->foreignId('kebutuhan_mendesak_id')
                    ->nullable()
                    ->after('user_id')
                    ->constrained('kebutuhan_mendesaks')
                    ->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('donasis', 'kebutuhan_mendesak_id')) {
            Schema::table('donasis', function (Blueprint $table) {
                $table->dropConstrainedForeignId('kebutuhan_mendesak_id');
            });
        }
    }
};
