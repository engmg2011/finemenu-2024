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
        // $schedule->command('inspire')->hourly();
        $schedule->command('app:cancel-pending-reservations')->everyMinute();
        // Add queue worker (runs for 5 minutes, then stops)
        $schedule->command('queue:work --max-time=300')
            ->everyMinute()
            ->withoutOverlapping() // Prevents duplicate workers
            ->appendOutputTo(storage_path('logs/queue-worker.log')); // Logs output
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
