<?php
$components = [
    'DashboardCards',
    'CampaignTable',
    'QueueMonitor',
    'SettingsManager',
    'CampaignWizard',
    'ActivityTimeline',
    'HealthCenterWidget'
];
mkdir(__DIR__ . '/app/Livewire/Admin/Marketing', 0777, true);

foreach ($components as $comp) {
    $content = <<<PHP
<?php

namespace App\Livewire\Admin\Marketing;

use Livewire\Component;

class {$comp} extends Component
{
    public function render()
    {
        return view('livewire.admin.marketing.' . strtolower(preg_replace('/(?<!^)[A-Z]/', '-\$0', '{$comp}')));
    }
}
PHP;

    file_put_contents(__DIR__ . "/app/Livewire/Admin/Marketing/{$comp}.php", $content);
}
echo "Livewire Components generated.";
