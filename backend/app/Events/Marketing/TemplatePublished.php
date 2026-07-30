<?php
namespace App\Events\Marketing;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TemplatePublished
{
    use Dispatchable, SerializesModels;
    public function __construct(public readonly string $templateName, public readonly string $version) {}
}
