<?php

namespace App\Services\Admin;

use App\Models\Setting;

class SettingService
{
    /**
     * Get all settings as key-value array.
     */
    public function getSettings()
    {
        return Setting::pluck('value', 'key')->toArray();
    }

    /**
     * Save arbitrary settings array.
     */
    public function saveSettings(array $data)
    {
        $allowedKeys = [
            // General
            'site_name', 'site_tagline', 'site_email', 'currency_symbol', 'logo_url',
            // Affiliate Networks
            'amazon_associate_tag', 'flipkart_affiliate_id', 'cuelinks_campaign_id', 'earnkaro_affiliate_id',
            // Mail & SMTP
            'mail_mailer', 'mail_host', 'mail_port', 'mail_username', 'mail_password', 'mail_encryption', 'mail_from_address', 'mail_from_name',
            // AI & Scraping
            'ollama_model', 'ollama_base_url', 'ai_auto_summarize', 'crawler_automated', 'crawler_manual',
            // Social
            'telegram_channel_url', 'whatsapp_group_url'
        ];

        foreach ($allowedKeys as $key) {
            if (array_key_exists($key, $data)) {
                $category = 'General';
                if (str_starts_with($key, 'amazon_') || str_starts_with($key, 'flipkart_') || str_starts_with($key, 'cuelinks_') || str_starts_with($key, 'earnkaro_')) {
                    $category = 'Affiliate';
                } elseif (str_starts_with($key, 'mail_')) {
                    $category = 'Mail';
                } elseif (str_starts_with($key, 'ollama_') || str_starts_with($key, 'ai_') || str_starts_with($key, 'crawler_')) {
                    $category = 'AI';
                } elseif (str_starts_with($key, 'telegram_') || str_starts_with($key, 'whatsapp_')) {
                    $category = 'Social';
                }

                Setting::updateOrCreate(
                    ['key' => $key],
                    [
                        'value' => (string)($data[$key] ?? ''),
                        'category' => $category,
                    ]
                );
            }
        }
    }

    /**
     * Toggle a single setting.
     */
    public function toggleSetting(string $key, string $value)
    {
        return Setting::updateOrCreate(
            ['key' => $key],
            ['value' => $value]
        );
    }
}
