<?php

namespace App\Services\Mail;

use App\Contracts\MailProviderInterface;
use App\Models\EmailCampaign;
use App\Models\User;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class SendmailProvider implements MailProviderInterface
{
    public function send(User $user, Mailable $mailable, ?EmailCampaign $campaign = null): array
    {
        try {
            Mail::to($user)->send($mailable);
            
            // Track success for Health Monitor
            Cache::put('marketing.mail.last_success', now());
            Cache::put('marketing.mail.consecutive_failures', 0);

            return [
                'status' => 'Sent',
                'message_id' => Str::uuid()->toString(),
            ];
        } catch (\Exception $e) {
            Cache::put('marketing.mail.last_failure', now());
            Cache::increment('marketing.mail.consecutive_failures');
            throw $e;
        }
    }

    public function getName(): string
    {
        return 'sendmail';
    }
}
