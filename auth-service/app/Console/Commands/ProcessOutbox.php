<?php

namespace App\Console\Commands;

use App\Jobs\SyncAuthUserJob;
use App\Models\Outbox;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

#[Signature('app:process-outbox')]
#[Description('Reprocess pending messages from the outbox table to ensure data consistency across services')]
class ProcessOutbox extends Command {

    /**
     * Execute the console command.
     */
    public function handle() {
        //* Ambil pesan yang belum diproses (lebih dari 2 menit untuk menghindari race condition dengan Job utama)
        $pendingMessages = Outbox::whereNull('processed_at')
            ->where('created_at', '<', now()->subMinutes(2))
            ->limit(100)
            ->get();

        if ($pendingMessages->isEmpty()) {
            $this->info('No pending outbox messages found.');

            return Command::SUCCESS;
        }

        $this->info("Processing {$pendingMessages->count()} pending messages...");

        foreach ($pendingMessages as $message) {
            try {
                //* Dispatch kembali ke antrean Redis
                SyncAuthUserJob::dispatch($message->payload)
                    ->onQueue('sync_auth_queue');

                //* Opsional: Jika Anda ingin langsung menandai sukses saat di-dispatch ulang:
                // $message->update(['processed_at' => now()]);
            } catch (\Exception $e) {
                Log::error("Failed to re-process outbox ID: {$message->id}", [
                    'error' => $e->getMessage()
                ]);
            }
        }

        $this->info('Outbox processing completed.');

        return Command::SUCCESS;
    }
}
