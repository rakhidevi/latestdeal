<?php

namespace App\Console\Commands;

use App\Models\EmailCampaign;
use Illuminate\Console\Command;

class EmailCampaignStatusCommand extends Command
{
    protected $signature = 'email:campaign:status {campaign_id?}';
    protected $description = 'Show the status of all campaigns or a specific campaign.';

    public function handle()
    {
        $campaignId = $this->argument('campaign_id');

        if ($campaignId) {
            $this->showSingleCampaign($campaignId);
        } else {
            $this->showAllCampaigns();
        }

        return 0;
    }

    protected function showAllCampaigns()
    {
        $campaigns = EmailCampaign::orderBy('id', 'desc')->limit(10)->get([
            'id', 'name', 'type', 'status', 'total_recipients', 'sent_count', 'failed_count', 'started_at'
        ]);

        $this->info("Recent Email Campaigns:");
        
        $headers = ['ID', 'Name', 'Type', 'Status', 'Total', 'Sent', 'Failed', 'Started'];
        $rows = $campaigns->map(function ($c) {
            return [
                $c->id,
                $c->name,
                $c->type,
                $c->status,
                $c->total_recipients,
                $c->sent_count,
                $c->failed_count,
                $c->started_at ? $c->started_at->diffForHumans() : 'N/A'
            ];
        });

        $this->table($headers, $rows);
    }

    protected function showSingleCampaign($id)
    {
        $campaign = EmailCampaign::findOrFail($id);

        $this->info("=== Campaign #{$campaign->id}: {$campaign->name} ===");
        $this->line("Status:    {$campaign->status}");
        $this->line("Subject:   {$campaign->subject}");
        $this->line("Type:      {$campaign->type}");
        $this->line("Priority:  {$campaign->priority}");
        $this->newLine();

        $remaining = max(0, $campaign->total_recipients - $campaign->sent_count - $campaign->failed_count - $campaign->skipped_count);
        $rateLimit = config('mail.rate_per_hour', 300);
        $etaMins = $rateLimit > 0 ? round(($remaining / $rateLimit) * 60) : 0;

        $this->info("--- Metrics ---");
        $this->line("Total Recipients: {$campaign->total_recipients}");
        $this->line("Sent:             {$campaign->sent_count}");
        $this->line("Failed:           {$campaign->failed_count}");
        $this->line("Skipped/Bounced:  {$campaign->skipped_count}");
        $this->line("Remaining:        {$remaining}");
        
        if ($campaign->status === 'Sending' || $campaign->status === 'Queued') {
            $this->line("ETA to Complete:  ~{$etaMins} mins");
        }
        
        $this->newLine();
        $this->info("--- Timing ---");
        $this->line("Started At:       " . ($campaign->started_at ?? 'N/A'));
        $this->line("Completed At:     " . ($campaign->completed_at ?? 'N/A'));
    }
}
