<?php

namespace App\Contracts;

use App\Models\EmailCampaign;
use App\Models\User;
use Illuminate\Mail\Mailable;

interface MailProviderInterface
{
    /**
     * Send an email via the provider.
     *
     * @param User $user
     * @param Mailable $mailable
     * @param EmailCampaign|null $campaign
     * @return array Returns provider response details e.g., ['message_id' => '...', 'status' => 'Sent']
     * @throws \Exception
     */
    public function send(User $user, Mailable $mailable, ?EmailCampaign $campaign = null): array;

    /**
     * Get the name of the provider.
     */
    public function getName(): string;
}
