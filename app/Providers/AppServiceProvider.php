<?php

namespace App\Providers;

use App\Models\Barang;
use App\Models\LogAktivitas;
use App\Models\PeringatanKeamanan;
use App\Models\Permintaan;
use App\Models\Tugas;
use App\Models\User;
use App\Support\Pantau;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Setiap baris log baru diperiksa terhadap ambang peringatan
        LogAktivitas::created(fn ($log) => Pantau::periksa($log));

        View::composer('admin.layout', function ($view) {
            $baru = PeringatanKeamanan::whereNull('dibaca_at')->count();

            $view->with('peringatanBaru', $baru);
            $view->with('badge', [
                '/admin/barang'      => Barang::where('verifikasi', 'menunggu')->where('status', '!=', 'ditarik')->count(),
                '/admin/penerima'    => User::where('role', 'penerima')->where('status_penerima', 'menunggu')->count(),
                '/admin/permintaan'  => Permintaan::where('status', 'menunggu')->count(),
                '/admin/pengiriman'  => Tugas::where('status', 'baru')->count(),
                '/admin/peringatan'  => $baru,
            ]);
        });
    }
}