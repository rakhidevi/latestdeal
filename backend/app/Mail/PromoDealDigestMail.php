<?php

namespace App\Mail;

use App\Enums\MailCategory;
use App\Models\Deal;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class PromoDealDigestMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public Collection $deals;
    public string $headline;
    public string $subheadline;
    public ?string $unsubscribeUrl;
    public string $preheaderText;

    public function __construct(
        ?Collection $deals = null,
        string $headline = "Today's Hot Deals 🔥",
        string $subheadline = 'Handpicked savings up to 80% off — verified & live right now.',
        ?string $unsubscribeUrl = null
    ) {
        // If no deals provided, pull top 6 active deals sorted by discount
        // Prioritize deals with external (HTTP) image URLs since those always work in emails
        $this->deals = $deals ?? Deal::where('status', 'active')
            ->whereNotNull('image_path')
            ->where('image_path', '!=', '')
            ->whereNotNull('discounted_price')
            ->where('discounted_price', '>', 0)
            ->with(['brandRelation', 'merchant'])
            ->orderByRaw("CASE WHEN image_path LIKE 'http%' THEN 0 ELSE 1 END")
            ->orderByDesc('discount_percentage')
            ->limit(6)
            ->get();

        $this->headline = $headline;
        $this->subheadline = $subheadline;
        $this->unsubscribeUrl = $unsubscribeUrl;
        $this->preheaderText = strip_tags($subheadline);
        $this->onQueue('normal');
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "🔥 {$this->headline} — LatestDeal.in",
            tags: ['promo', 'digest', MailCategory::Marketing->value]
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.promo-deal-digest',
            text: 'emails.promo-deal-digest-plain'
        );
    }
}
