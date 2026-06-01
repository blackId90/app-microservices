<?php

namespace App\Mail;

use App\Models\AuthUser;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\URL;

class CompanyVerificationMail extends Mailable implements ShouldQueue {
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
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
            subject: 'Company Verification Mail',
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
            'type' => 'company',
            'id' => $this->company['company_id'],
            'hash' => sha1($this->company['company_key_email'])
        ];

        //* Create signed URL
        $verifyUrl = URL::temporarySignedRoute(
            'verification.verify',
            \Illuminate\Support\Carbon::now()->addMinutes(60),
            $params
        );

        /*
        \Illuminate\Support\Facades\Log::info('Company email verification url', [
            'url' => request()->url(),
            'verifyUrl' => $verifyUrl
        ]);
        */

        return new Content(
            view: 'emails.company-verification',
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
     * @return array<int, Attachment>
     */
    public function attachments(): array {
        return [];
    }
}
