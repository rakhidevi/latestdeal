<?php

namespace App\Services\Editorial;

use App\DTOs\EditorialArticleDTO;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class EditorialService
{
    protected $parser;
    protected $basePath;

    public function __construct(FrontMatterParser $parser)
    {
        $this->parser = $parser;
        $this->basePath = resource_path('content/editorial');
    }

    /**
     * Get all articles of a specific content type (e.g. blog, guide, event)
     */
    public function getAll(string $contentType = null): array
    {
        $path = $contentType ? $this->basePath . '/' . $contentType : $this->basePath;
        
        if (!File::exists($path)) {
            return [];
        }

        $files = File::allFiles($path);
        $articles = [];

        foreach ($files as $file) {
            if ($file->getExtension() === 'md') {
                // Determine content type from directory structure if not explicitly passed
                $type = $contentType ?? $file->getRelativePath() ?: 'blog';
                $articles[] = $this->parseFile($file->getPathname(), $type);
            }
        }

        // Sort by published_at descending
        usort($articles, function ($a, $b) {
            $dateA = $a->publishedAt ?? '1970-01-01';
            $dateB = $b->publishedAt ?? '1970-01-01';
            return strtotime($dateB) <=> strtotime($dateA);
        });

        return $articles;
    }

    /**
     * Get a specific article by slug and type
     */
    public function getArticle(string $slug, string $contentType): ?EditorialArticleDTO
    {
        $articles = $this->getAll($contentType);
        foreach ($articles as $article) {
            if ($article->slug === $slug) {
                return $article;
            }
        }
        return null;
    }

    /**
     * Parse a single markdown file into a DTO
     */
    protected function parseFile(string $filePath, string $contentType): EditorialArticleDTO
    {
        $content = File::get($filePath);
        $parsed = $this->parser->parse($content);
        
        $matter = $parsed['matter'];
        
        // Use filename as fallback slug
        if (!isset($matter['slug'])) {
            $matter['slug'] = File::name($filePath);
        }
        
        // Ensure content type is set
        $matter['content_type'] = $contentType;
        
        // Convert Markdown to HTML
        $htmlContent = Str::markdown($parsed['body']);
        $matter['content'] = $htmlContent;

        return EditorialArticleDTO::fromArray($matter);
    }
}
