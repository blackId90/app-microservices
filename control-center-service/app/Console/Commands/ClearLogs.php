<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class ClearLogs extends Command {
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'logs:clear';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete all log files in storage/logs';

    /**
     * Execute the console command.
     */
    public function handle(): int {
        $logPath = storage_path('logs');
        if (!File::exists($logPath)) {
            $this->error("Log directory not found: {$logPath}");

            return Command::FAILURE;
        }

        $files = File::files($logPath);
        if (empty($files)) {
            $this->info('No log files to delete.');

            return Command::SUCCESS;
        }

        foreach ($files as $file) {
            File::delete($file);
        }

        $this->info('All log files have been deleted.');

        return Command::SUCCESS;
    }
}
