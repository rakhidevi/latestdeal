<?php

namespace App\Services\Communications\Blocks;

class RawHtmlBlock extends ContentBlock
{
    public function defaultSettings(): array
    {
        return [
            'html' => '',
        ];
    }

    public function render(array $theme = []): string
    {
        return $this->settings['html'];
    }
}
