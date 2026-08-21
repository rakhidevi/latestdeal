<?php

namespace App\Services\Admin;

use App\Models\Setting;

class SettingService
{
    public function getSettings()
    {
        return Setting::whereIn('key', [
            'ollama_model', 
            'ollama_base_url', 
            'ai_auto_summarize', 
            'crawler_automated', 
            'crawler_manual'
        ])->pluck('value', 'key');
    }

    public function saveSettings(array $data)
    {
        $keys = ['ollama_model', 'ollama_base_url', 'ai_auto_summarize', 'crawler_automated', 'crawler_manual'];
        
        foreach ($keys as $key) {
            if (isset($data[$key])) {
                Setting::updateOrCreate(
                    ['key' => $key],
                    ['value' => $data[$key]]
                );
            }
        }
    }

    public function toggleSetting(string $key, string $value)
    {
        return Setting::updateOrCreate(
            ['key' => $key],
            ['value' => $value]
        );
    }
}
