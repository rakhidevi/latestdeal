<?php
$events = [
    'CampaignCreated',
    'CampaignScheduled',
    'CampaignStarted',
    'CampaignPaused',
    'CampaignCompleted',
    'CampaignFailed',
    'TemplatePublished',
    'SettingsUpdated'
];

foreach ($events as $event) {
    $content = <<<PHP
<?php

namespace App\Events\Marketing;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Models\EmailCampaign;

class {$event}
{
    use Dispatchable, SerializesModels;

    public function __construct(public readonly EmailCampaign \$campaign)
    {}
}
PHP;
    if ($event === 'SettingsUpdated') {
        $content = <<<PHP
<?php

namespace App\Events\Marketing;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class {$event}
{
    use Dispatchable, SerializesModels;

    public function __construct(public readonly array \$settings)
    {}
}
PHP;
    }
    if ($event === 'TemplatePublished') {
        $content = <<<PHP
<?php

namespace App\Events\Marketing;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class {$event}
{
    use Dispatchable, SerializesModels;

    public function __construct(public readonly string \$templateName, public readonly string \$version)
    {}
}
PHP;
    }

    file_put_contents(__DIR__ . "/app/Events/Marketing/{$event}.php", $content);
}
echo "Events generated.";
