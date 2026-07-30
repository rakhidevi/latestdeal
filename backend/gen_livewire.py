import os
import re

components = [
    'DashboardCards',
    'CampaignTable',
    'QueueMonitor',
    'SettingsManager',
    'CampaignWizard',
    'ActivityTimeline',
    'HealthCenterWidget'
]

os.makedirs('app/Livewire/Admin/Marketing', exist_ok=True)

for comp in components:
    view_name = re.sub(r"(?<!^)(?=[A-Z])", "-", comp).lower()
    content = f"""<?php

namespace App\Livewire\Admin\Marketing;

use Livewire\Component;

class {comp} extends Component
{{
    public function render()
    {{
        return view('livewire.admin.marketing.{view_name}');
    }}
}}
"""
    with open(f"app/Livewire/Admin/Marketing/{comp}.php", "w") as f:
        f.write(content)

print("Livewire components generated successfully!")
