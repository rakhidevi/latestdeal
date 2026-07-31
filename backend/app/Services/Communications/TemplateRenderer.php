<?php

namespace App\Services\Communications;

use App\Models\Communications\EmailTheme;
use App\Services\Communications\Blocks\ContentBlock;

class TemplateRenderer
{
    /**
     * Render an array of content blocks into an HTML email template using a specific theme.
     *
     * @param EmailTheme $theme
     * @param array $blocks
     * @param array $mergeVariables
     * @return string
     */
    public function render(EmailTheme $theme, array $blocks, array $mergeVariables = []): string
    {
        $manifest = $theme->manifest ?? [];
        $bg = $manifest['colors']['background'] ?? '#f5f7fa';
        $font = $manifest['typography']['font_family'] ?? 'sans-serif';
        $containerWidth = $manifest['spacing']['container_width'] ?? '600px';

        $renderedBlocks = '';
        
        foreach ($blocks as $block) {
            if ($block instanceof ContentBlock) {
                $renderedBlocks .= $block->render($manifest);
            } elseif (is_array($block) && isset($block['type'])) {
                // If passed as an array, reconstruct the block class
                $class = "App\\Services\\Communications\\Blocks\\" . $block['type'];
                if (class_exists($class)) {
                    $obj = new $class($block['id'] ?? uniqid(), $block['settings'] ?? []);
                    $renderedBlocks .= $obj->render($manifest);
                }
            }
        }

        $surface = $manifest['colors']['surface'] ?? '#ffffff';
        $padding = $manifest['spacing']['padding'] ?? '20px';
        $radius = $manifest['components']['card']['border_radius'] ?? '0';

        // Very basic layout wrapper for email templates
        $html = <<<HTML
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="utf-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>{$theme->name}</title>
            <style>
                body { margin: 0; padding: 0; background-color: {$bg}; font-family: {$font}; }
                .email-container { max-width: {$containerWidth}; margin: 0 auto; background-color: transparent; padding: 20px 0; }
                .email-content { background-color: {$surface}; padding: {$padding}; border-radius: {$radius}; overflow: hidden; margin: 0 auto; }
            </style>
        </head>
        <body>
            <div class="email-container">
                <div class="email-content">
                    {$renderedBlocks}
                </div>
            </div>
        </body>
        </html>
        HTML;

        // Replace merge variables (e.g. {{ user.name }})
        foreach ($mergeVariables as $key => $value) {
            $html = str_replace('{{ ' . $key . ' }}', $value, $html);
            $html = str_replace('{{' . $key . '}}', $value, $html);
        }

        return $html;
    }
}
