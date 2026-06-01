<?php

namespace App\Mail;

use App\Models\AuthUser;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\URL;

class UserVerificationMail extends Mailable implements ShouldQueue {
    use Queueable, SerializesModels;

    public function __construct(
        public AuthUser $authUser,
        public array $company,
        public array $user,
    ) {
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope {
        return new Envelope(
            // subject: 'Welcome to ' . config('app.name') . ' – Your account is ready!',
            subject: 'User Verification Mail',
            replyTo: 'noreply@multitenant.com'
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content {
        //* Force set host from config app.url
        // URL::forceRootUrl(config('app.url'));

        $params = [
            'type' => 'user',
            'id' => $this->authUser->getKey(),
            'hash' => sha1($this->authUser->getKeyEmail())
        ];

        //* Create signed URL
        $verifyUrl = URL::temporarySignedRoute(
            'verification.verify',
            \Illuminate\Support\Carbon::now()->addMinutes(60),
            $params
        );

        /*
        \Illuminate\Support\Facades\Log::info('User email verification url', [
            'url' => request()->url(),
            'verifyUrl' => $verifyUrl
        ]);
        */

        return new Content(
            view: 'emails.user-verification',
            with: [
                'authUser' => $this->authUser,
                'company' => $this->company,
                'user' => $this->user,
                'appName' => 'Multi Tenant',
                //* Arahkan user ke halaman Frontend "https://url-frontend.com" . urlencode($verifyUrl)
                'verifyUrl' => $verifyUrl,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array {
        return [];
    }
}
