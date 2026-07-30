<?php

namespace App\Livewire\Traits;

trait AuthorizesMarketing
{
    public function authorizeMarketing(string $ability): void
    {
        // Only admins can access marketing features (role check)
        if (auth()->user()?->role !== 'admin') {
            abort(403, 'Unauthorized action.');
        }
    }
}
