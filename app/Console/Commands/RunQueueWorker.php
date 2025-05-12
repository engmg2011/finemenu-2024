<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class RunQueueWorker extends Command
{
    protected $signature = 'queue:work-shared';
    protected $description = 'Runs queue worker for shared hosting (limited time)';
    public function handle()
    {
        $this->info('Starting queue worker for 5 minutes...');
        \Illuminate\Support\Facades\Artisan::call('queue:work', [
            '--stop-when-empty' => true, // Stops if no jobs left
            '--max-time' => 300,        // Max 5 minutes (adjust as needed)
        ]);
    }
}
