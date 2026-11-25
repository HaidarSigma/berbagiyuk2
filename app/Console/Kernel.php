<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
{
    $schedule->command('backup:lengkap')->dailyAt('02:00')->withoutOverlapping();
    $schedule->command('backup:verifikasi')->weeklyOn(0, '03:30');   // setiap Minggu
        $schedule->command('aset:lokal --periksa')->dailyAt('03:00');
}

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
