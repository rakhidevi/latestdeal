<?php

namespace App\DTOs\Marketing;

class ActivityItemDTO
{
    public function __construct(
        public readonly string $type,
        public readonly string $title,
        public readonly string $description,
        public readonly string $icon,
        public readonly string $color,
        public readonly string $timestamp,
        public readonly ?string $link = null
    ) {}
}
