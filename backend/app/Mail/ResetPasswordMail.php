<?php

namespace App\Mail;

use App\Enums\MailCategory;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ResetPasswordMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public string $resetUrl;

    public function __construct(string $resetUrl)
    {
        $this->resetUrl = $resetUrl;
        $this->onQueue('critical'); // Priority: critical
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Reset Password Request - LatestDeal',
            tags: ['password-reset', MailCategory::Security->value]
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.reset-password',
            text: 'emails.reset-password-plain'
        );
    }
}
