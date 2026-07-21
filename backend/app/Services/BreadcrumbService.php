<?php

namespace App\Services;

class BreadcrumbService
{
    /**
     * Generate Breadcrumbs array for the UI.
     */
    public function generate(array $crumbs)
    {
        // Example: [['title' => 'Home', 'url' => '/'], ['title' => 'Electronics', 'url' => null]]
        return $crumbs;
    }

    /**
     * Generate Schema.org JSON-LD for Breadcrumbs.
     */
    public function generateSchema(array $crumbs)
    {
        $itemListElement = [];
        $position = 1;

        foreach ($crumbs as $crumb) {
            $item = [
                "@type" => "ListItem",
                "position" => $position,
                "name" => $crumb['title'],
            ];

            if (!empty($crumb['url'])) {
                // Ensure URL is absolute for schema
                $item['item'] = url($crumb['url']);
            }

            $itemListElement[] = $item;
            $position++;
        }

        return [
            "@context" => "https://schema.org",
            "@type" => "BreadcrumbList",
            "itemListElement" => $itemListElement,
        ];
    }
}
