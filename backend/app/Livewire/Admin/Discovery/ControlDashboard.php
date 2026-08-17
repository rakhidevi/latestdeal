<?php

namespace App\Livewire\Admin\Discovery;

use Livewire\Component;

class ControlDashboard extends Component
{
    public $strategies = [];
    public $queueStats = [];
    public $brandCoverage = [];
    public $priceDrops = [];
    public $strategyRoiRanking = [];
    public $opportunitySources = [];
    public $providerCapabilityMatrix = [];
    public $effectivePriceLeaderboard = [];
    public $strategyCertification = [];
    public $crossProviderTracker = [];

    public function mount()
    {
        // Mock data representing the Strategy Registry + Economics Database
        $this->strategies = [
            [
                'id' => 'strat_search',
                'name' => 'Amazon Keyword Search',
                'lifecycle' => 'CERTIFIED',
                'mode' => 'RUNNING',
                'next_run' => 'In 2 mins',
                'budget' => 'AUTO (5,200)',
                'generated' => 5200,
                'published' => 840,
                'revenue' => '₹42,500',
                'roi' => '4.8x',
                'health' => [
                    'overall' => 97,
                    'extraction' => 99,
                    'validation' => 98,
                    'publishing' => 100,
                    'roi' => 95,
                    'exceptions' => 0.1,
                ],
                'heat' => 'VERY HIGH',
                'version' => '1.0.2',
                'notes' => 'Primary fallback'
            ],
            [
                'id' => 'strat_lightning',
                'name' => 'Amazon Lightning Deals',
                'lifecycle' => 'SHADOW',
                'mode' => 'SHADOW_ONLY',
                'next_run' => 'In 5 mins',
                'budget' => 'MANUAL (1,000)',
                'generated' => 950,
                'published' => 0,
                'revenue' => '₹0',
                'roi' => '0.0x',
                'health' => [
                    'overall' => 92,
                    'extraction' => 95,
                    'validation' => 89,
                    'publishing' => 0,
                    'roi' => 0,
                    'exceptions' => 2.4,
                ],
                'heat' => 'MEDIUM',
                'version' => '0.9.0',
                'notes' => 'Testing new DOM parser'
            ]
        ];

        $this->queueStats = [
            'generated' => 6150,
            'queued' => 402,
            'processing' => 15,
            'published' => 840,
            'failed' => 24
        ];
        
        $this->brandCoverage = [
            ['brand' => 'Samsung', 'products' => 2400, 'published' => 92, 'roi' => '4.3x'],
            ['brand' => 'Apple', 'products' => 850, 'published' => 14, 'roi' => '5.1x'],
            ['brand' => 'Sony', 'products' => 1200, 'published' => 45, 'roi' => '3.8x'],
        ];
        
        $this->priceDrops = [
            [
                'product' => 'Samsung 55" QLED 4K TV', 
                'current' => 54990, 
                'lowest_30d' => 58990,
                'highest_30d' => 64990, 
                'average' => 61200, 
                'drop_percent' => 10.1, 
                'trend' => 'DOWN',
                'buy_signal' => true
            ],
            [
                'product' => 'Apple AirPods Pro (2nd Gen)', 
                'current' => 19999, 
                'lowest_30d' => 20999,
                'highest_30d' => 24900, 
                'average' => 23500, 
                'drop_percent' => 14.9, 
                'trend' => 'DOWN',
                'buy_signal' => true
            ],
            [
                'product' => 'Sony WH-1000XM5', 
                'current' => 26990, 
                'lowest_30d' => 26990,
                'highest_30d' => 29990, 
                'average' => 28500, 
                'drop_percent' => 5.3, 
                'trend' => 'STABLE',
                'buy_signal' => false
            ]
        ];

        $this->strategyRoiRanking = [
            ['strategy' => 'Lightning', 'roi' => '4.8x'],
            ['strategy' => 'Coupons', 'roi' => '4.1x'],
            ['strategy' => 'Warehouse', 'roi' => '3.9x'],
            ['strategy' => 'Brand Store', 'roi' => '3.6x'],
        ];

        $this->opportunitySources = [
            ['source' => 'Today\'s Deals', 'count' => 1250],
            ['source' => 'Coupons', 'count' => 840],
            ['source' => 'Warehouse', 'count' => 420],
            ['source' => 'Price Drops', 'count' => 310],
            ['source' => 'Bank Offers', 'count' => 890],
        ];

        $this->providerCapabilityMatrix = [
            'Amazon' => [
                'Lightning' => true,
                'Coupons' => true,
                'Warehouse' => true,
                'Bank Offers' => true,
            ],
            'Flipkart' => [
                'Lightning' => false,
                'Coupons' => true,
                'Warehouse' => false,
                'Bank Offers' => true,
            ]
        ];

        $this->effectivePriceLeaderboard = [
            ['product' => 'iPhone 15 Pro (128GB)', 'mrp' => 134900, 'base' => 127990, 'bank' => 6000, 'exchange' => 12000, 'effective' => 109990],
            ['product' => 'Sony PS5 Console', 'mrp' => 54990, 'base' => 44990, 'bank' => 2000, 'exchange' => 0, 'effective' => 42990],
            ['product' => 'Pampers Premium Care', 'mrp' => 1499, 'base' => 1199, 'coupon' => 100, 'subscribe' => 119, 'effective' => 980],
        ];

        $this->strategyCertification = [
            ['strategy' => 'Keyword Search', 'status' => 'CERTIFIED', 'class' => 'success'],
            ['strategy' => 'Lightning Deals', 'status' => 'CERTIFIED', 'class' => 'success'],
            ['strategy' => 'Coupons', 'status' => 'CERTIFIED', 'class' => 'success'],
            ['strategy' => 'Warehouse', 'status' => 'SHADOW', 'class' => 'warning text-dark'],
            ['strategy' => 'Bank Offers', 'status' => 'SHADOW', 'class' => 'warning text-dark'],
            ['strategy' => 'Cross Provider', 'status' => 'EXPERIMENTAL', 'class' => 'danger'],
        ];

        $this->crossProviderTracker = [
            ['product' => 'Samsung 55" 4K TV', 'amazon' => 54990, 'flipkart' => 53990, 'winner' => 'Flipkart', 'diff' => 1000],
            ['product' => 'Apple Watch Series 9', 'amazon' => 41900, 'flipkart' => 41999, 'winner' => 'Amazon', 'diff' => 99],
            ['product' => 'Dyson V12 Detect', 'amazon' => 55900, 'flipkart' => 55900, 'winner' => 'Tie', 'diff' => 0],
        ];
    }

    public function forceRun($strategyId, $mode)
    {
        // $mode can be: run, shadow, replay, dry_run
        session()->flash('message', "Triggered manual execution for $strategyId in $mode mode.");
    }
    
    public function updateMode($strategyId, $newMode)
    {
        // RUNNING, PAUSED, SHADOW_ONLY, DISABLED
        session()->flash('message', "Strategy $strategyId mode changed to $newMode.");
    }

    public function render()
    {
        return view('livewire.admin.discovery.control-dashboard')
            ->layout('layouts.admin'); // Ensure this layout exists in your app
    }
}
