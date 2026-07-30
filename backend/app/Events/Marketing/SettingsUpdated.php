<?php
namespace App\Events\Marketing;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SettingsUpdated
{
    use Dispatchable, SerializesModels;
    public function __construct(public readonly array $settings) {}
}
