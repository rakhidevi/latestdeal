<?php
$services = [
    'CampaignService',
    'AudienceService',
    'TemplateService',
    'ThemeService',
    'QueueMonitorService',
    'ActivityFeedService',
    'NotificationService',
    'HealthService',
    'AIContentService'
];
mkdir(__DIR__ . '/app/Services/Marketing', 0777, true);

foreach ($services as $service) {
    $content = <<<PHP
<?php

namespace App\Services\Marketing;

class {$service}
{
    // Business logic isolated from Livewire
}
PHP;

    file_put_contents(__DIR__ . "/app/Services/Marketing/{$service}.php", $content);
}
echo "Services generated.";
