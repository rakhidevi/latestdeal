<?php

namespace App\Services\Communications\Blocks;

class DealCardBlock extends ContentBlock
{
    public function defaultSettings(): array
    {
        return [
            'image_url' => '',
            'title' => 'Amazing Deal',
            'original_price' => '100.00',
            'discounted_price' => '49.99',
            'button_text' => 'Shop Now',
            'button_url' => '#',
            'background_color' => '#ffffff',
            'text_color' => '#333333',
            'button_color' => '#ff5722',
        ];
    }

    public function render(array $theme = []): string
    {
        $s = $this->settings;
        $btnColor = $theme['colors']['primary'] ?? $s['button_color'];
        $font = $theme['fonts']['body'] ?? 'sans-serif';

        return <<<HTML
        <div style="background-color: {$s['background_color']}; color: {$s['text_color']}; font-family: {$font}; border: 1px solid #eaeaea; border-radius: 8px; padding: 16px; margin-bottom: 16px; text-align: center;">
            <img src="{$s['image_url']}" alt="{$s['title']}" style="max-width: 100%; height: auto; border-radius: 4px; margin-bottom: 12px;" />
            <h3 style="margin: 0 0 8px 0;">{$s['title']}</h3>
            <div style="margin-bottom: 16px;">
                <span style="text-decoration: line-through; color: #888;">\${$s['original_price']}</span>
                <span style="font-size: 1.25em; font-weight: bold; color: #e53935; margin-left: 8px;">\${$s['discounted_price']}</span>
            </div>
            <a href="{$s['button_url']}" style="display: inline-block; padding: 10px 20px; background-color: {$btnColor}; color: #fff; text-decoration: none; border-radius: 4px; font-weight: bold;">{$s['button_text']}</a>
        </div>
        HTML;
    }
}
