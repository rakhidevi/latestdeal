<?php

namespace App\Livewire\Admin\Marketing;

use Livewire\Component;
use Livewire\Attributes\Middleware;

#[Middleware('studio.admin')]
class ThemeManager extends Component
{
    public array $themes = [];

    public function mount()
    {
        $this->themes = [
            ['id' => 'diwali-2026', 'name' => 'Diwali 2026', 'primary' => '#FF9933', 'secondary' => '#FFFFFF'],
            ['id' => 'cyber-monday', 'name' => 'Cyber Monday', 'primary' => '#000000', 'secondary' => '#00FF00'],
        ];
    }

    public function render()
    {
        return view('livewire.admin.marketing.theme-manager')->layout('layouts.admin');
    }
}
