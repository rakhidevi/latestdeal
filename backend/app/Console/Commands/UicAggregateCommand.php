<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\UIC\UicVisitorSession;
use App\Models\UIC\UicPageVisit;
use App\Models\UIC\UicEvent;
use App\Models\UIC\UicAffiliateClick;
use App\Models\UIC\UicAiConversation;
use App\Models\UIC\UicSearchHistory;
use App\Models\UIC\UicDailyAggregate;

class UicAggregateCommand extends Command
{
    protected $signature = 'uic:aggregate {--date= : Specific date to aggregate (YYYY-MM-DD)}';
    protected $description = 'Compute and store daily UIC analytics aggregates';

    public function handle(): int
    {
        $dateStr = $this->option('date') ?: today()->toDateString();
        $this->info("Calculating UIC daily aggregates for date: {$dateStr}");

        $visitorsCount = UicVisitorSession::whereDate('created_at', $dateStr)->distinct('visitor_uuid')->count('visitor_uuid');
        $sessionsCount = UicVisitorSession::whereDate('created_at', $dateStr)->count();
        $pageviewsCount = UicPageVisit::whereDate('created_at', $dateStr)->count();
        $clicksCount = UicEvent::whereDate('created_at', $dateStr)->where('event_type', 'CLICK')->count();
        $affiliateClicksCount = UicAffiliateClick::whereDate('created_at', $dateStr)->count();
        $aiQuestionsCount = UicAiConversation::whereDate('created_at', $dateStr)->count();
        $searchesCount = UicSearchHistory::whereDate('created_at', $dateStr)->count();
        
        $bouncedSessions = UicVisitorSession::whereDate('created_at', $dateStr)->where('bounce', true)->count();
        $bounceRate = $sessionsCount > 0 ? round(($bouncedSessions / $sessionsCount) * 100, 2) : 0;

        UicDailyAggregate::updateOrCreate(
            ['date' => $dateStr],
            [
                'visitors' => $visitorsCount,
                'sessions' => $sessionsCount,
                'pageviews' => $pageviewsCount,
                'clicks' => $clicksCount,
                'affiliate_clicks' => $affiliateClicksCount,
                'ai_questions' => $aiQuestionsCount,
                'searches' => $searchesCount,
                'bounce_rate' => $bounceRate,
            ]
        );

        $this->info("UIC Daily Aggregates successfully saved for {$dateStr}!");
        return Command::SUCCESS;
    }
}
