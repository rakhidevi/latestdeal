<?php

namespace App\Livewire\Admin\Marketing;

use Livewire\Component;
use Livewire\Attributes\Middleware;

#[Middleware('studio.admin')]
class AssetManager extends Component
{
    public array $assets = [];

    public function mount()
    {
        $this->assets = [
            ['id' => 1, 'name' => 'Diwali Banner Base', 'type' => 'image/png', 'size' => '2.4MB'],
            ['id' => 2, 'name' => 'Tech Sale Overlay', 'type' => 'image/png', 'size' => '1.1MB'],
        ];
    }

    public function render()
    {
        return view('livewire.admin.marketing.asset-manager')->layout('layouts.admin');
    }
}
