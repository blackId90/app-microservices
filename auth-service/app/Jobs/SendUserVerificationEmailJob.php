<?php

namespace App\Jobs;

use App\Mail\UserVerificationMail;
use App\Models\AuthUser;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendUserVerificationEmailJob implements ShouldQueue {
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * Number of seconds to wait before retrying the job.
     */
    public int $backoff = 30;

    /**
     * The number of seconds the job can run before timing out.
     */
    public int $timeout = 60;

    public function __construct(
        public AuthUser $authUser,
        public array $company,
        public array $user
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void {
        $mail = Mail::to($this->authUser->auth_user_email);
        $mail->send(new UserVerificationMail(
            authUser: $this->authUser,
            company: $this->company,
            user: $this->user,
        ));

        /*
        Log::info('[SendUserEmailJob] User email dispatched.', [
            'auth_user_id'    => $this->authUser->getKey(),
            'auth_user_email' => $this->authUser->auth_user_email,
            'company_id'      => $this->company['company_id'] ?? null,
            'company_email'   => $this->company['company_email'] // $companyEmail,
        ]);
        */
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void {
        Log::error('[SendUserEmailJob] Failed to send user email.', [
            'auth_user_id'    => $this->authUser->getKey(),
            'auth_user_email' => $this->authUser->auth_user_email,
            'error'           => $exception->getMessage(),
        ]);
    }
}
