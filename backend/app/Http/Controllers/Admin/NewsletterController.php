<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\NewsletterWelcomeMail;
use App\Mail\PromoDealDigestMail;
use App\Mail\WelcomeShopperMail;
use App\Models\Deal;
use App\Models\EmailCampaign;
use App\Models\User;
use App\Models\Communications\Subscriber as CommSubscriber;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class NewsletterController extends Controller
{
    /**
     * List all operational email templates with status and previews.
     */
    public function templates()
    {
        $templates = [
            [
                'id' => 'promo-deal-digest',
                'name' => 'Daily Hot Deals Digest',
                'category' => 'Marketing / Deals',
                'type' => 'HTML Email (Responsive)',
                'description' => 'Multi-product deal blast featuring top discounted active deals, store badges, and direct affiliate tracking buttons.',
                'subject_sample' => "🔥 Today's Hot Deals — Up to 80% Off Verified Savings",
                'is_active' => true,
                'blade_view' => 'emails.promo-deal-digest',
            ],
            [
                'id' => 'shopper-welcome',
                'name' => 'Shopper Welcome Email',
                'category' => 'Transactional / Onboarding',
                'type' => 'HTML Email (Responsive)',
                'description' => 'Automated welcome message sent to newly registered members explaining deal tracking, alerts, and coupons.',
                'subject_sample' => 'Welcome to LatestDeal.in — Your Smart Shopping Hub',
                'is_active' => true,
                'blade_view' => 'emails.shopper-welcome',
            ],
            [
                'id' => 'newsletter-welcome',
                'name' => 'Newsletter Welcome & Confirmation',
                'category' => 'Transactional / Subscription',
                'type' => 'HTML Email (Responsive)',
                'description' => 'Instant confirmation email sent to guests who subscribe to newsletter deal alerts from the homepage footer.',
                'subject_sample' => "You're In! Welcome to LatestDeal Newsletter",
                'is_active' => true,
                'blade_view' => 'emails.newsletter-welcome',
            ],
        ];

        return view('admin.marketing.templates_manager', compact('templates'));
    }

    /**
     * Render the template preview page with device toggles and test dispatcher.
     */
    public function previewPage(Request $request, $templateKey)
    {
        $templateNames = [
            'promo-deal-digest' => 'Daily Hot Deals Digest',
            'shopper-welcome' => 'Shopper Welcome Email',
            'newsletter-welcome' => 'Newsletter Welcome & Confirmation',
        ];

        if (!isset($templateNames[$templateKey])) {
            return redirect()->route('admin.marketing.templates')->with('error', 'Template not found.');
        }

        $templateTitle = $templateNames[$templateKey];

        return view('admin.marketing.preview_page', compact('templateKey', 'templateTitle'));
    }

    /**
     * Render raw HTML of the email template inside an iframe.
     */
    public function renderPreviewHtml(Request $request, $templateKey)
    {
        try {
            if ($templateKey === 'promo-deal-digest') {
                $deals = Deal::where('status', 'active')
                    ->whereNotNull('image_path')
                    ->where('discounted_price', '>', 0)
                    ->orderByDesc('discount_percentage')
                    ->limit(6)
                    ->get();

                // If no deals in db, create minimal mock collection
                if ($deals->isEmpty()) {
                    $deals = collect([
                        (object)[
                            'title' => 'Apple AirPods Pro (2nd Gen) with MagSafe Case (USB-C)',
                            'discounted_price' => 19990,
                            'original_price' => 24900,
                            'discount_percentage' => 20,
                            'amount_saved' => 4910,
                            'coupon_code' => 'APPLE10',
                            'image_path' => 'https://images.unsplash.com/photo-1600294037681-c80b4cb5b434?w=500&auto=format&fit=crop',
                            'image_url' => 'https://images.unsplash.com/photo-1600294037681-c80b4cb5b434?w=500&auto=format&fit=crop',
                            'affiliate_url' => 'https://amazon.in',
                            'hash_id' => 'demo123',
                            'slug' => 'apple-airpods-pro-2',
                            'brand' => 'Apple',
                            'brandRelation' => (object)['name' => 'Apple'],
                            'merchant' => (object)['name' => 'Amazon India'],
                        ],
                        (object)[
                            'title' => 'Sony WH-1000XM5 Wireless Industry Leading Noise Canceling Headphones',
                            'discounted_price' => 26990,
                            'original_price' => 34990,
                            'discount_percentage' => 23,
                            'amount_saved' => 8000,
                            'coupon_code' => null,
                            'image_path' => 'https://images.unsplash.com/photo-1546435770-a3e426bf472b?w=500&auto=format&fit=crop',
                            'image_url' => 'https://images.unsplash.com/photo-1546435770-a3e426bf472b?w=500&auto=format&fit=crop',
                            'affiliate_url' => 'https://flipkart.com',
                            'hash_id' => 'demo456',
                            'slug' => 'sony-wh-1000xm5',
                            'brand' => 'Sony',
                            'brandRelation' => (object)['name' => 'Sony'],
                            'merchant' => (object)['name' => 'Flipkart'],
                        ],
                    ]);
                }

                $mailable = new PromoDealDigestMail(
                    $deals,
                    "Today's Hot Deals 🔥",
                    "Handpicked savings up to 80% off — verified & live right now.",
                    url('/unsubscribe?token=sample_token')
                );

                return $mailable->render();
            }

            if ($templateKey === 'shopper-welcome') {
                $mockUser = new User([
                    'name' => 'Pankaj Tiwari',
                    'email' => 'pankaj@example.com',
                ]);
                $mockUser->id = 1;

                $mailable = new WelcomeShopperMail($mockUser);
                return $mailable->render();
            }

            if ($templateKey === 'newsletter-welcome') {
                $mailable = new NewsletterWelcomeMail('pankaj@example.com');
                return $mailable->render();
            }

            return "<h3>Template key '{$templateKey}' unknown.</h3>";
        } catch (\Throwable $e) {
            return "<div style='font-family:sans-serif;padding:24px;color:#b91c1c;background:#fef2f2;border:1px solid #fecaca;border-radius:12px;'>
                <h3>Error Rendering Template Preview:</h3>
                <p>" . htmlspecialchars($e->getMessage()) . "</p>
                <pre style='font-size:11px;overflow-x:auto;'>" . htmlspecialchars($e->getTraceAsString()) . "</pre>
            </div>";
        }
    }

    /**
     * Send an instant test email to the administrator's designated inbox.
     */
    public function sendTestEmail(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'template' => 'required|string',
        ]);

        $recipient = $validated['email'];
        $templateKey = $validated['template'];

        try {
            if ($templateKey === 'promo-deal-digest') {
                $deals = Deal::where('status', 'active')
                    ->whereNotNull('image_path')
                    ->where('discounted_price', '>', 0)
                    ->orderByDesc('discount_percentage')
                    ->limit(6)
                    ->get();

                $mailable = new PromoDealDigestMail(
                    $deals,
                    "[TEST] Today's Hot Deals 🔥",
                    "Handpicked savings up to 80% off — verified & live right now.",
                    url('/unsubscribe?token=test')
                );

                Mail::to($recipient)->send($mailable);
            } elseif ($templateKey === 'shopper-welcome') {
                $mockUser = auth()->user() ?? new User(['name' => 'Admin Tester', 'email' => $recipient]);
                Mail::to($recipient)->send(new WelcomeShopperMail($mockUser));
            } elseif ($templateKey === 'newsletter-welcome') {
                Mail::to($recipient)->send(new NewsletterWelcomeMail($recipient));
            } else {
                return back()->with('error', 'Unknown template selected.');
            }

            return back()->with('success', "Test email for '{$templateKey}' dispatched successfully to {$recipient}!");
        } catch (\Throwable $e) {
            return back()->with('error', "Failed to send email via SMTP: " . $e->getMessage() . ". Check Mail Settings under System Settings.");
        }
    }

    /**
     * Real subscribers management with search, stats, and CSV export.
     */
    public function subscribers(Request $request)
    {
        $search = $request->get('search', '');
        $status = $request->get('status', 'all');

        $query = DB::table('subscribers')->orderByDesc('created_at');

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('email', 'like', "%{$search}%");
                if (\Illuminate\Support\Facades\Schema::hasColumn('subscribers', 'first_name')) {
                    $q->orWhere('first_name', 'like', "%{$search}%")
                      ->orWhere('last_name', 'like', "%{$search}%");
                }
            });
        }

        if ($status === 'active') {
            $query->where('is_active', 1);
        } elseif ($status === 'inactive') {
            $query->where('is_active', 0);
        }

        $totalSubscribers = DB::table('subscribers')->count();
        $activeSubscribers = DB::table('subscribers')->where('is_active', 1)->count();
        $inactiveSubscribers = $totalSubscribers - $activeSubscribers;

        $subscribers = $query->paginate(25)->withQueryString();

        return view('admin.marketing.subscribers_manager', compact(
            'subscribers', 'search', 'status', 'totalSubscribers', 'activeSubscribers', 'inactiveSubscribers'
        ));
    }

    /**
     * Export all subscribers as CSV.
     */
    public function exportSubscribers()
    {
        $subscribers = DB::table('subscribers')->orderBy('id', 'asc')->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="latestdeal_subscribers_' . date('Y-m-d') . '.csv"',
        ];

        $callback = function () use ($subscribers) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['ID', 'Email', 'First Name', 'Last Name', 'Is Active', 'Created At']);

            foreach ($subscribers as $row) {
                fputcsv($file, [
                    $row->id,
                    $row->email,
                    $row->first_name ?? '',
                    $row->last_name ?? '',
                    $row->is_active ? 'Yes' : 'No',
                    $row->created_at,
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Delete or toggle subscriber.
     */
    public function toggleSubscriber($id)
    {
        $subscriber = DB::table('subscribers')->where('id', $id)->first();
        if ($subscriber) {
            DB::table('subscribers')->where('id', $id)->update([
                'is_active' => $subscriber->is_active ? 0 : 1,
                'updated_at' => now(),
            ]);
            return back()->with('success', 'Subscriber status updated.');
        }

        return back()->with('error', 'Subscriber not found.');
    }

    public function destroySubscriber($id)
    {
        DB::table('subscribers')->where('id', $id)->delete();
        return back()->with('success', 'Subscriber removed successfully.');
    }

    /**
     * Newsletter dispatch / campaign broadcaster view.
     */
    public function dispatchView()
    {
        $activeDeals = Deal::where('status', 'active')
            ->whereNotNull('image_path')
            ->where('discounted_price', '>', 0)
            ->orderByDesc('discount_percentage')
            ->limit(12)
            ->get();

        $subscriberCount = DB::table('subscribers')->where('is_active', 1)->count();
        $recentCampaigns = EmailCampaign::orderByDesc('created_at')->limit(10)->get();

        return view('admin.marketing.dispatch_campaign', compact('activeDeals', 'subscriberCount', 'recentCampaigns'));
    }

    /**
     * Trigger campaign broadcast to subscribers.
     */
    public function triggerCampaign(Request $request)
    {
        $validated = $request->validate([
            'subject' => 'required|string|max:255',
            'subheadline' => 'nullable|string|max:500',
            'deal_ids' => 'nullable|array',
            'deal_ids.*' => 'exists:deals,id',
            'target_audience' => 'required|in:subscribers,admin_test',
        ]);

        $dealIds = $validated['deal_ids'] ?? [];
        $deals = !empty($dealIds) 
            ? Deal::whereIn('id', $dealIds)->get()
            : Deal::where('status', 'active')->whereNotNull('image_path')->orderByDesc('discount_percentage')->limit(6)->get();

        $subject = $validated['subject'];
        $subheadline = $validated['subheadline'] ?? 'Handpicked savings up to 80% off — verified & live right now.';

        if ($validated['target_audience'] === 'admin_test') {
            $adminEmail = auth()->user()->email ?? 'admin@latestdeal.in';
            try {
                Mail::to($adminEmail)->send(new PromoDealDigestMail($deals, $subject, $subheadline));
                return back()->with('success', "Test broadcast sent directly to your email ({$adminEmail})!");
            } catch (\Throwable $e) {
                return back()->with('error', "SMTP error: " . $e->getMessage());
            }
        }

        // Audience is active subscribers
        $subscribers = DB::table('subscribers')->where('is_active', 1)->pluck('email');
        if ($subscribers->isEmpty()) {
            return back()->with('error', 'No active subscribers found in database.');
        }

        $campaign = EmailCampaign::create([
            'name' => $subject,
            'subject' => $subject,
            'status' => 'Sending',
            'recipient_count' => $subscribers->count(),
            'sent_count' => 0,
            'failed_count' => 0,
            'scheduled_at' => now(),
        ]);

        // Dispatch in chunks or queue
        foreach ($subscribers->chunk(50) as $chunk) {
            foreach ($chunk as $email) {
                try {
                    $mailable = new PromoDealDigestMail(
                        $deals, 
                        $subject, 
                        $subheadline,
                        url('/unsubscribe?email=' . urlencode($email))
                    );
                    Mail::to($email)->queue($mailable);
                } catch (\Throwable $e) {
                    // Log failure
                }
            }
        }

        $campaign->update(['status' => 'Completed', 'sent_count' => $subscribers->count()]);

        return back()->with('success', "Newsletter '{$subject}' queued for {$subscribers->count()} active subscribers!");
    }
}
