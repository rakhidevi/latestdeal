<?php

namespace App\Services\Communications\Blocks;

class HeaderBlock extends ContentBlock
{
    public function defaultSettings(): array
    {
        return [
            'logo_url' => '',
            'alt_text' => 'Brand Logo',
            'align' => 'center', // left, center, right
            'padding_top' => '20px',
            'padding_bottom' => '20px',
            'background_color' => 'transparent',
        ];
    }

    public function render(array $theme = []): string
    {
        $align = $this->settings['align'];
        $bg = $this->settings['background_color'];
        $pt = $this->settings['padding_top'];
        $pb = $this->settings['padding_bottom'];
        $logo = $this->settings['logo_url'];
        $alt = $this->settings['alt_text'];
        
        if (empty($logo)) {
            return "<div style='text-align: {$align}; padding: {$pt} 0 {$pb} 0; background-color: {$bg};'><h1>{$alt}</h1></div>";
        }

        return "<div style='text-align: {$align}; padding: {$pt} 0 {$pb} 0; background-color: {$bg};'><img src='{$logo}' alt='{$alt}' style='max-width: 200px;' /></div>";
    }
}
