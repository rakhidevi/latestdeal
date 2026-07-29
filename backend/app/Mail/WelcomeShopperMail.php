<?php

namespace App\Mail;

use App\Models\User;
use App\Enums\MailCategory;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class WelcomeShopperMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public User $user;
    public ?string $verificationUrl;

    public function __construct(User $user, ?string $verificationUrl = null)
    {
        $this->user = $user;
        $this->verificationUrl = $verificationUrl;
        $this->onQueue('normal'); // Queue priority: normal
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Welcome to LatestDeal, ' . $this->user->name . '! 🎉',
            tags: ['welcome', MailCategory::Transactional->value]
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.shopper-welcome',
            text: 'emails.shopper-welcome-plain'
        );
    }
}
