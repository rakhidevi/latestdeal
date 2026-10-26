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

class PromoDealDigestMail extends Mailable
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
        if ($deals && $deals->isNotEmpty()) {
            $this->deals = $deals;
        } else {
            // Query database for active or published deals
            $dbDeals = Deal::where(function ($q) {
                    $q->where('status', 'active')
                      ->orWhere('editorial_status', Deal::STATUS_PUBLISHED);
                })
                ->where('discounted_price', '>', 0)
                ->with(['brandRelation', 'merchant'])
                ->orderByRaw("CASE WHEN image_path IS NOT NULL AND image_path != '' THEN 0 ELSE 1 END")
                ->orderByDesc('discount_percentage')
                ->limit(6)
                ->get();

            if ($dbDeals->isNotEmpty()) {
                $this->deals = $dbDeals;
            } else {
                // High-quality showcase deals fallback so newsletter is never empty
                $this->deals = collect([
                    (object)[
                        'title' => 'Apple AirPods Pro (2nd Gen) with MagSafe Case (USB-C)',
                        'discounted_price' => 19990,
                        'original_price' => 24900,
                        'discount_percentage' => 20,
                        'amount_saved' => 4910,
                        'coupon_code' => 'APPLE10',
                        'image_path' => 'https://images.unsplash.com/photo-1600294037681-c80b4cb5b434?w=500&auto=format&fit=crop',
                        'image_url' => 'https://images.unsplash.com/photo-1600294037681-c80b4cb5b434?w=500&auto=format&fit=crop',
                        'affiliate_url' => url('/'),
                        'hash_id' => 'airpods',
                        'slug' => 'apple-airpods-pro-2',
                        'brand' => 'Apple',
                        'brandRelation' => (object)['name' => 'Apple'],
                        'merchant' => (object)['name' => 'Amazon India'],
                    ],
                    (object)[
                        'title' => 'Sony WH-1000XM5 Wireless Noise Cancelling Headphones',
                        'discounted_price' => 26990,
                        'original_price' => 34990,
                        'discount_percentage' => 23,
                        'amount_saved' => 8000,
                        'coupon_code' => null,
                        'image_path' => 'https://images.unsplash.com/photo-1546435770-a3e426bf472b?w=500&auto=format&fit=crop',
                        'image_url' => 'https://images.unsplash.com/photo-1546435770-a3e426bf472b?w=500&auto=format&fit=crop',
                        'affiliate_url' => url('/'),
                        'hash_id' => 'sonyxm5',
                        'slug' => 'sony-wh-1000xm5',
                        'brand' => 'Sony',
                        'brandRelation' => (object)['name' => 'Sony'],
                        'merchant' => (object)['name' => 'Flipkart'],
                    ],
                    (object)[
                        'title' => 'Samsung Galaxy Watch 6 Bluetooth (40mm, Graphite)',
                        'discounted_price' => 17999,
                        'original_price' => 29999,
                        'discount_percentage' => 40,
                        'amount_saved' => 12000,
                        'coupon_code' => 'WATCH40',
                        'image_path' => 'https://images.unsplash.com/photo-1579586337278-3befd40fd17a?w=500&auto=format&fit=crop',
                        'image_url' => 'https://images.unsplash.com/photo-1579586337278-3befd40fd17a?w=500&auto=format&fit=crop',
                        'affiliate_url' => url('/'),
                        'hash_id' => 'samwatch6',
                        'slug' => 'samsung-galaxy-watch-6',
                        'brand' => 'Samsung',
                        'brandRelation' => (object)['name' => 'Samsung'],
                        'merchant' => (object)['name' => 'Amazon India'],
                    ],
                    (object)[
                        'title' => 'Kindle Paperwhite (16 GB) – 6.8" Display with Adjustable Warm Light',
                        'discounted_price' => 12499,
                        'original_price' => 14999,
                        'discount_percentage' => 17,
                        'amount_saved' => 2500,
                        'coupon_code' => null,
                        'image_path' => 'https://images.unsplash.com/photo-1592496431122-2349e0fbc666?w=500&auto=format&fit=crop',
                        'image_url' => 'https://images.unsplash.com/photo-1592496431122-2349e0fbc666?w=500&auto=format&fit=crop',
                        'affiliate_url' => url('/'),
                        'hash_id' => 'kindle16',
                        'slug' => 'kindle-paperwhite-16gb',
                        'brand' => 'Amazon',
                        'brandRelation' => (object)['name' => 'Amazon'],
                        'merchant' => (object)['name' => 'Amazon India'],
                    ],
                ]);
            }
        }

        $this->headline = $headline;
        $this->subheadline = $subheadline;
        $this->unsubscribeUrl = $unsubscribeUrl;
        $this->preheaderText = strip_tags($subheadline);
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
