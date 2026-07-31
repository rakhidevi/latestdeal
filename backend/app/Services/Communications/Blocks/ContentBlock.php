<?php

namespace App\Services\Communications\Blocks;

abstract class ContentBlock
{
    public string $id;
    public string $type;
    public array $settings = [];

    public function __construct(string $id, array $settings = [])
    {
        $this->id = $id;
        $this->type = class_basename(static::class);
        $this->settings = array_merge($this->defaultSettings(), $settings);
    }

    /**
     * Define the default settings structure for this block.
     *
     * @return array
     */
    abstract public function defaultSettings(): array;
    
    /**
     * Render the block into HTML.
     *
     * @param array $theme
     * @return string
     */
    abstract public function render(array $theme = []): string;
    
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'settings' => $this->settings,
        ];
    }
}
