<?php

namespace App\Jobs;

use App\Models\Outbox;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class SyncAuthUserJob implements ShouldQueue {
    use Dispatchable, InteractsWithQueue, Queueable;

    public int $tries = 5;
    public function backoff(): array {
        return [10, 30, 60, 120];
    }

    /**
     * Create a new job instance.
     */
    public function __construct(
        public array $payload
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void {
        //* Validation payload
        if (empty($this->payload['id'])) {
            Log::error('SyncAuthUserJob: Missing user ID in payload', ['payload' => $this->payload]);
            $this->delete();

            return;
        }

        //* Publish ke Redis Channel agar di-subscribe/consume oleh Control Center atau jika menggunakan default Laravel Queue Redis:
        Log::info("Syncing user {$this->payload['id']} to message broker...");

        //* Update Table Outbox
        $updated = Outbox::where('payload->id', $this->payload['id'])
            ->whereNull('processed_at')
            ->update(['processed_at' => now()->format('Y-m-d H:i:s.u')]);

        //* Verification Update Table Outbox
        if ($updated) {
            Log::info('SyncAuthUserJob: Outbox marked processed', ['user_id' => $this->payload['id']]);
        } else {
            // Jika data tidak ditemukan, kemungkinan dikirim oleh Command ProcessOutbox
            // yang barusan sudah sukses ditandai oleh worker lain.
            Log::notice('SyncAuthUserJob: Outbox already processed or not found', ['user_id' => $this->payload['id']]);
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void {
        Log::critical('SyncAuthUserJob FATAL ERROR: Could not update outbox table.', [
            'user_id' => $this->payload['id'] ?? 'unknown',
            'error'   => $exception->getMessage(),
            'context' => 'Auth Service internal sync'
        ]);

        // Catatan: Karena processed_at tetap NULL, Command `php artisan app:process-outbox` akan mencoba mengirim ulang di masa depan saat DB sudah pulih.
        // Di sini Anda bisa mengirim notifikasi ke Slack/Telegram
    }
}
