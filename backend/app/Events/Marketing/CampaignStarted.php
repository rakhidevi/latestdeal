<?php
namespace App\Events\Marketing;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Models\EmailCampaign;

class CampaignStarted
{
    use Dispatchable, SerializesModels;
    public function __construct(public readonly EmailCampaign $campaign) {}
}
