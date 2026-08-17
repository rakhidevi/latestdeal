<?php

namespace Tests\Feature;

use App\Services\NavigationService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class NavigationServiceTest extends TestCase
{
    public function test_it_handles_missing_navigation_tables_gracefully(): void
    {
        Cache::flush();

        Schema::shouldReceive('hasTable')
            ->with('categories')->andReturn(false)
            ->with('brands')->andReturn(false)
            ->with('merchants')->andReturn(false);

        $tree = app(NavigationService::class)->getNavigationTree();

        $this->assertSame([], $tree['categories']->toArray());
        $this->assertSame([], $tree['brands']->toArray());
        $this->assertSame([], $tree['merchants']->toArray());
    }
}
