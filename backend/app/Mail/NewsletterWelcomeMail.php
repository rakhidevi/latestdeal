<?php

namespace App\Mail;

use App\Enums\MailCategory;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NewsletterWelcomeMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public ?string $unsubscribeUrl;

    public function __construct(?string $unsubscribeUrl = null)
    {
        $this->unsubscribeUrl = $unsubscribeUrl;
        $this->onQueue('normal'); // Queue priority: normal
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Subscription Confirmed - LatestDeal Alerts',
            tags: ['newsletter', MailCategory::Notification->value]
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.newsletter-welcome',
            text: 'emails.newsletter-welcome-plain'
        );
    }
}
