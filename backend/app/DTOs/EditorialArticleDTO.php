<?php

namespace App\DTOs;

class EditorialArticleDTO
{
    public function __construct(
        public readonly string $title,
        public readonly string $slug,
        public readonly string $description,
        public readonly string $content, // The parsed HTML content
        public readonly ?string $author = 'LatestDeal Editorial Team',
        public readonly ?string $publishedAt = null,
        public readonly ?string $updatedAt = null,
        public readonly ?string $category = null,
        public readonly array $tags = [],
        public readonly ?int $readingTime = null,
        public readonly ?string $coverImage = null,
        public readonly bool $featured = false,
        public readonly array $relatedLinks = [],
        public readonly string $contentType = 'blog' // blog, guide, event, glossary, brand-guide
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            title: $data['title'] ?? 'Untitled',
            slug: $data['slug'] ?? '',
            description: $data['description'] ?? '',
            content: $data['content'] ?? '',
            author: $data['author'] ?? 'LatestDeal Editorial Team',
            publishedAt: $data['published_at'] ?? null,
            updatedAt: $data['updated_at'] ?? null,
            category: $data['category'] ?? null,
            tags: $data['tags'] ?? [],
            readingTime: $data['reading_time'] ?? null,
            coverImage: $data['cover_image'] ?? null,
            featured: $data['featured'] ?? false,
            relatedLinks: $data['related_links'] ?? [],
            contentType: $data['content_type'] ?? 'blog'
        );
    }
}
