<?php

namespace App\Services\Search;

class SearchQuery
{
    public array $brandIds = [];
    public array $categoryIds = [];
    public array $productTypeIds = [];
    public array $merchantIds = [];
    
    public ?float $minDiscount = null;
    public ?float $maxPrice = null;
    public ?float $minPrice = null;
    
    public array $keywords = [];
    
    public string $originalQuery = '';

    public function __construct(string $originalQuery)
    {
        $this->originalQuery = $originalQuery;
    }
}
