<?php

namespace App\Services\Studio;

class WidgetRegistry
{
    protected array $widgets = [];

    public function register(string $name, string $componentClass, array $options = []): void
    {
        $this->widgets[$name] = [
            'class' => $componentClass,
            'options' => $options,
        ];
    }

    public function getWidget(string $name): ?array
    {
        return $this->widgets[$name] ?? null;
    }

    public function all(): array
    {
        return $this->widgets;
    }
}
