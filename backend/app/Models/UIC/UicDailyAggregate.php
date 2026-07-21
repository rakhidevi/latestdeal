<?php

namespace App\Models\UIC;

use Illuminate\Database\Eloquent\Model;

class UicDailyAggregate extends Model
{
    protected $table = 'uic_daily_aggregates';

    protected $fillable = [
        'date',
        'visitors',
        'sessions',
        'pageviews',
        'clicks',
        'affiliate_clicks',
        'ai_questions',
        'searches',
        'bounce_rate',
    ];
}
