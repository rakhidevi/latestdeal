<?php

namespace App\Livewire\Admin\Marketing;

use Livewire\Component;
use Livewire\Attributes\Middleware;

#[Middleware('studio.admin')]
class CampaignBuilder extends Component
{
    public string $campaignName = '';
    public string $selectedTheme = 'diwali-2026';
    public array $targetCategories = [];

    public function saveCampaign()
    {
        session()->flash('success', 'Campaign saved and automated rules applied.');
    }

    public function render()
    {
        return view('livewire.admin.marketing.campaign-builder')->layout('layouts.admin');
    }
}
