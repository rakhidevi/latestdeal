<?php

namespace App\Services;

class SeoService
{
    /**
     * Generate standard SEO metadata arrays for any entity or list.
     */
    public function generateMeta(string $title, string $description, string $url, array $extra = [])
    {
        return array_merge([
            'title' => $title,
            'description' => $description,
            'canonical' => $url,
            'og_title' => $title,
            'og_description' => $description,
            'og_url' => $url,
            'og_type' => 'website',
            'twitter_card' => 'summary_large_image',
        ], $extra);
    }
}
