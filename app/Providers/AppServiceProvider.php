<?php

namespace App\Providers;

use App\Models\Barang;
use App\Models\Permintaan;
use App\Models\User;
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
        View::composer('admin.layout', function ($view) {
            $view->with('badge', [
                '/admin/barang'     => Barang::where('verifikasi', 'menunggu')->where('status', '!=', 'ditarik')->count(),
                '/admin/penerima'   => User::where('role', 'penerima')->where('status_penerima', 'menunggu')->count(),
                '/admin/permintaan' => Permintaan::where('status', 'menunggu')->count(),
            ]);
        });
    }
}