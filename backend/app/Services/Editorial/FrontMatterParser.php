<?php

namespace App\Services\Editorial;

class FrontMatterParser
{
    /**
     * Parses a markdown file with YAML-like front matter.
     * Expects format:
     * ---
     * title: My Title
     * slug: my-title
     * tags: [tag1, tag2]
     * ---
     * Content here
     *
     * @param string $fileContent
     * @return array [ 'matter' => array, 'body' => string ]
     */
    public function parse(string $fileContent): array
    {
        $pattern = '/^---\s*(.*?)\s*---\s*(.*)$/s';
        
        if (preg_match($pattern, ltrim($fileContent), $matches)) {
            $frontMatterStr = $matches[1];
            $body = $matches[2];
            
            $matter = $this->parseYamlLike($frontMatterStr);
            
            return [
                'matter' => $matter,
                'body' => $body
            ];
        }

        // If no front matter is found, return empty matter and full body
        return [
            'matter' => [],
            'body' => $fileContent
        ];
    }

    /**
     * A very basic YAML-like parser for simple key-value pairs and arrays.
     */
    protected function parseYamlLike(string $matterStr): array
    {
        $matter = [];
        $lines = explode("\n", $matterStr);
        
        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line) || str_starts_with($line, '#')) {
                continue; // Skip empty lines and comments
            }
            
            $parts = explode(':', $line, 2);
            if (count($parts) === 2) {
                $key = trim($parts[0]);
                $value = trim($parts[1]);
                
                // Remove quotes if present
                $value = preg_replace('/^[\'"](.*)[\'"]$/', '$1', $value);
                
                // Handle simple arrays [a, b, c]
                if (preg_match('/^\[(.*)\]$/', $value, $matches)) {
                    $arrayStr = $matches[1];
                    $value = array_map('trim', explode(',', $arrayStr));
                    // Remove empty elements
                    $value = array_filter($value, function($val) {
                        return $val !== '';
                    });
                    $value = array_values($value); // re-index
                } else if ($value === 'true') {
                    $value = true;
                } else if ($value === 'false') {
                    $value = false;
                }
                
                $matter[$key] = $value;
            }
        }
        
        return $matter;
    }
}
