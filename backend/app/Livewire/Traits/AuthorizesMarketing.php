<?php

namespace App\Livewire\Traits;

use Illuminate\Support\Facades\Gate;

trait AuthorizesMarketing
{
    public function authorizeMarketing(string ): void
    {
        abort_if(Gate::denies(), 403, \'Unauthorized action.\');
    }
}
