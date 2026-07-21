<?php

namespace App\Services\SearchPipeline;

use Illuminate\Database\Eloquent\Builder;

class SearchPayload
{
    public Builder $query;
    public array $filters;
    public $result; // Paginator or Collection

    public function __construct(Builder $query, array $filters)
    {
        $this->query = $query;
        $this->filters = $filters;
    }

    public function getResult()
    {
        return $this->result ?? $this->query->get();
    }
}
