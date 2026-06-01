<?php

namespace App\Jobs;

use App\Enums\UserStatusEnum;
use App\Models\SyncAuthUser;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\{InteractsWithQueue, SerializesModels};
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\{Cache, Log};

class SyncAuthUserJob implements ShouldQueue {
    use InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Jumlah percobaan maksimal jika job gagal.
     */
    public int $tries = 5;

    /**
     * Berapa lama (detik) menunggu sebelum mencoba lagi (Backoff).
     * Menggunakan array untuk Exponential Backoff: 10 detik, 30 detik, dst.
     */
    public function backoff(): array {
        return [10, 30, 60, 120];
    }

    /**
     * Berapa lama job boleh berjalan sebelum dianggap timeout.
     */
    public int $timeout = 60;

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
        $data = $this->payload;
        $lockKey = 'sync_user_lock_' . $data['id'];

        //* Menggunakan Atomic Lock untuk mencegah Race Condition antar Worker
        //* block(10) artinya menunggu antrian lock maksimal 10 detik sebelum lempar exception
        Cache::lock($lockKey, 15)->block(10, function () use ($data) {
            //* 1. Find Existing Data
            $existingUser = SyncAuthUser::withTrashed()
                ->where('auth_user_id', $data['id'])
                ->first();

            //* 2. Force Delete
            if ($data['action'] === 'force_delete') {
                $existingUser?->forceDelete();

                return;
            }

            //* 3. Check Race Condition (Data Versioning)
            //* Jika data di DB sudah lebih baru/sama dengan payload, abaikan.
            if ($existingUser && $existingUser->updated_at->format('Y-m-d H:i:s.u') >= $data['updated_at']) {
                Log::info('SyncAuthUserJob: Data skipped (stale message)', ['id' => $data['id']]);

                return;
            }

            //* 4. Upsert (Update or Insert)
            $parseDate = fn($date) => $date ? Carbon::parse($date)->format('Y-m-d H:i:s.u') : null;

            SyncAuthUser::withTrashed()->updateOrCreate(
                ['auth_user_id' => $data['id']],
                [
                    'auth_user_email' => $data['email'],
                    'auth_user_username' => $data['username'],
                    'auth_user_company_id' => $data['company'] ?? null,
                    'auth_user_is_admin' => $data['is_admin'] ?? false,
                    'auth_user_is_status' => $data['status'] ?? UserStatusEnum::PENDING,
                    'created_at' => $parseDate($data['created_at']),
                    'updated_at' => $parseDate($data['updated_at']),
                    'deleted_at' => $parseDate($data['deleted_at'])
                ]
            );

            /*
            $user = SyncAuthUser::withTrashed()->updateOrCreate(
                ['auth_user_id' => $data['id']],
                [
                    'auth_user_email' => $data['email'],
                    'auth_user_username' => $data['username'],
                    'auth_user_company_id' => $data['company'] ?? null,
                    'auth_user_is_admin' => $data['is_admin'] ?? false,
                    'auth_user_is_status' => $data['status'] ?? UserStatusEnum::PENDING,
                    'created_at' => $parseDate($data['created_at']), // $data['created_at'],
                    'updated_at' => $parseDate($data['updated_at']), // $data['updated_at'],
                    'deleted_at' => $parseDate($data['deleted_at']) // $data['deleted_at'] ?? null,
                ]
            );

            //* Handle manual restore jika status di payload `upsert` bukan `delete`
            if (empty($data['deleted_at']) && $user->trashed()) {
                $user->restore();

                return;
            }
            */
        });
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void {
        Log::error('SyncAuthUserJob Final Failure', [
            'id' => $this->payload['id'] ?? 'unknown',
            'error' => $exception->getMessage(),
            'payload' => $this->payload
        ]);

        //* Di sini Anda bisa mengirim notifikasi ke Slack/Telegram
    }
}
