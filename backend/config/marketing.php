<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Marketing Engine Feature Flags
    |--------------------------------------------------------------------------
    |
    | Wrap incomplete widgets and features behind these flags. 
    | Allows progressive rollout of the enterprise marketing center.
    |
    */
    'features' => [
        'health_widget' => env('MARKETING_FEATURE_HEALTH', true),
        'queue_widget' => env('MARKETING_FEATURE_QUEUE', true),
        'activity_widget' => env('MARKETING_FEATURE_ACTIVITY', false), // Set false until fully implemented
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Dashboard Polling Intervals
    |--------------------------------------------------------------------------
    |
    | Define polling intervals for Livewire widgets to keep dashboard lightweight.
    |
    */
    'polling' => [
        'campaign_stats' => '10s',
        'queue_widget' => '5s',
        'health_widget' => '30s',
        'activity_widget' => '5s',
    ],
];
