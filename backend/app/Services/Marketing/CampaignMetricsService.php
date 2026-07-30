<?php

namespace App\Services\Marketing;

use App\Models\EmailCampaign;
use App\Models\EmailLog;
use App\DTOs\Marketing\CampaignMetricsDTO;
use Illuminate\Support\Facades\Cache;

class CampaignMetricsService
{
    public function getMetrics(): CampaignMetricsDTO
    {
        return Cache::remember('marketing.campaign_metrics', 10, function () {
            // Using DB facade or Eloquent aggregates
            $activeCampaigns = EmailCampaign::where('status', 'running')->count();
            $draftCampaigns = EmailCampaign::where('status', 'draft')->count();
            $scheduledCampaigns = EmailCampaign::where('status', 'scheduled')->count();
            $sendingCampaigns = EmailCampaign::where('status', 'sending')->count(); // Or use 'running' if running = sending

            // Note: Adjusting for actual statuses in DB: 'draft', 'scheduled', 'running', 'paused', 'completed', 'failed'
            
            $sentToday = EmailLog::whereDate('sent_at', today())->count();
            $failedToday = EmailLog::whereDate('created_at', today())->where('status', 'failed')->count();
            
            $totalCampaigns = EmailCampaign::count();
            
            // Average Success Rate (e.g. out of total sent vs failed)
            $totalSent = EmailLog::where('status', 'sent')->count();
            $totalFailed = EmailLog::where('status', 'failed')->count();
            $totalAttempted = $totalSent + $totalFailed;
            $averageSuccessRate = $totalAttempted > 0 ? round(($totalSent / $totalAttempted) * 100, 2) : 0.0;
            
            $totalRecipients = \App\Models\CampaignRecipient::count();
            
            $emailsSentThisWeek = EmailLog::where('status', 'sent')
                ->where('sent_at', '>=', now()->startOfWeek())
                ->count();
                
            $emailsSentThisMonth = EmailLog::where('status', 'sent')
                ->where('sent_at', '>=', now()->startOfMonth())
                ->count();

            return new CampaignMetricsDTO(
                activeCampaigns: $activeCampaigns,
                draftCampaigns: $draftCampaigns,
                scheduledCampaigns: $scheduledCampaigns,
                sendingCampaigns: $sendingCampaigns,
                sentToday: $sentToday,
                failedToday: $failedToday,
                totalCampaigns: $totalCampaigns,
                averageSuccessRate: $averageSuccessRate,
                totalRecipients: $totalRecipients,
                emailsSentThisWeek: $emailsSentThisWeek,
                emailsSentThisMonth: $emailsSentThisMonth
            );
        });
    }
}
