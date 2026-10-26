<?php

namespace App\DTOs\Editorial;

class GuideArticleDTO
{
    public function __construct(
        public readonly int $dealId,
        public readonly string $title,
        public readonly string $slug,
        public readonly string $summary,
        public readonly ?string $excerpt,
        public readonly string $content,
        public readonly ?int $categoryId = null,
        public readonly ?string $featuredImage = null,
        public readonly array $seoMetadata = [],
        public readonly array $tags = [],
        public readonly string $status = 'published'
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            dealId: (int) ($data['deal_id'] ?? 0),
            title: (string) ($data['title'] ?? ''),
            slug: (string) ($data['slug'] ?? ''),
            summary: (string) ($data['summary'] ?? ''),
            excerpt: $data['excerpt'] ?? null,
            content: (string) ($data['content'] ?? ''),
            categoryId: isset($data['category_id']) ? (int) $data['category_id'] : null,
            featuredImage: $data['featured_image'] ?? null,
            seoMetadata: (array) ($data['seo_metadata'] ?? []),
            tags: (array) ($data['tags'] ?? []),
            status: (string) ($data['status'] ?? 'published')
        );
    }

    public function toArray(): array
    {
        return [
            'deal_id' => $this->dealId,
            'title' => $this->title,
            'slug' => $this->slug,
            'summary' => $this->summary,
            'excerpt' => $this->excerpt,
            'content' => $this->content,
            'category_id' => $this->categoryId,
            'featured_image' => $this->featuredImage,
            'seo_metadata' => $this->seoMetadata,
            'status' => $this->status,
        ];
    }
}
