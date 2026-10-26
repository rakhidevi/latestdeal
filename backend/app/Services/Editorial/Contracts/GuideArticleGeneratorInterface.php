<?php

namespace App\Services\Editorial\Contracts;

use App\DTOs\Editorial\GuideArticleDTO;
use App\Models\Article;
use App\Models\Deal;

interface GuideArticleGeneratorInterface
{
    /**
     * Synthesize a Google AdWords and SEO compliant guide article for the given Deal.
     *
     * @param Deal $deal
     * @param array $options
     * @return GuideArticleDTO
     */
    public function generateForDeal(Deal $deal, array $options = []): GuideArticleDTO;

    /**
     * Persist the generated GuideArticleDTO into the articles table.
     *
     * @param GuideArticleDTO $dto
     * @param int|null $authorId
     * @return Article
     */
    public function saveArticle(GuideArticleDTO $dto, ?int $authorId = null): Article;
}
