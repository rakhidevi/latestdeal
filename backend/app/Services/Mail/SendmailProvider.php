<?php

namespace App\Services\Mail;

use App\Contracts\MailProviderInterface;
use App\Models\EmailCampaign;
use App\Models\User;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class SendmailProvider implements MailProviderInterface
{
    public function send(User $user, Mailable $mailable, ?EmailCampaign $campaign = null): array
    {
        // Actually dispatch the mailable instantly via the configured driver
        // Because this runs inside a queued job, we send synchronously here.
        Mail::to($user)->send($mailable);

        return [
            'status' => 'Sent',
            'message_id' => Str::uuid()->toString(), // Sendmail might not easily return a msg id without parsing headers, so we generate one.
        ];
    }

    public function getName(): string
    {
        return 'sendmail';
    }
}
