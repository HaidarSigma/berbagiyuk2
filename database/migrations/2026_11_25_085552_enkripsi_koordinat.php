<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private array $tabel = ['users', 'barangs', 'donasis'];

    public function up(): void
    {
        foreach ($this->tabel as $t) {
            DB::statement("ALTER TABLE `{$t}` MODIFY `latitude` TEXT NULL, MODIFY `longitude` TEXT NULL");

            DB::table($t)->select('id', 'latitude', 'longitude')->orderBy('id')->chunkById(200, function ($baris) use ($t) {
                foreach ($baris as $b) {
                    $ubah = [];
                    foreach (['latitude', 'longitude'] as $k) {
                        $v = $b->$k;
                        // 'eyJ' = awal ciphertext Laravel; mencegah enkripsi ganda bila dijalankan ulang
                        if ($v !== null && $v !== '' && ! str_starts_with((string) $v, 'eyJ')) {
                            $ubah[$k] = Crypt::encryptString((string) $v);
                        }
                    }
                    if ($ubah) {
                        DB::table($t)->where('id', $b->id)->update($ubah);
                    }
                }
            });
        }
    }

    public function down(): void
    {
        foreach ($this->tabel as $t) {
            DB::table($t)->select('id', 'latitude', 'longitude')->orderBy('id')->chunkById(200, function ($baris) use ($t) {
                foreach ($baris as $b) {
                    $ubah = [];
                    foreach (['latitude', 'longitude'] as $k) {
                        $v = $b->$k;
                        if ($v !== null && $v !== '' && str_starts_with((string) $v, 'eyJ')) {
                            $ubah[$k] = Crypt::decryptString($v);
                        }
                    }
                    if ($ubah) {
                        DB::table($t)->where('id', $b->id)->update($ubah);
                    }
                }
            });
            DB::statement("ALTER TABLE `{$t}` MODIFY `latitude` DECIMAL(10,7) NULL, MODIFY `longitude` DECIMAL(10,7) NULL");
        }
    }
};