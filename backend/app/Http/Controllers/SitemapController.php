<?php

namespace App\Http\Controllers;

use App\Models\Deal;
use App\Models\Category;
use App\Models\Merchant;
use App\Models\Article;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SitemapController
{
    public function index()
    {
        $deals = Deal::indexable()
            ->orderBy('created_at', 'desc')
            ->limit(1000)
            ->get();
            
        $articles = Article::where('status', Article::STATUS_PUBLISHED)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->orderBy('published_at', 'desc')
            ->limit(500)
            ->get();

        // Only include genuine clean categories with active deals
        $bogusCategorySlugs = [
            'c.url', 'item.url', 'b.url', 'd.url', 'm.url', '"', "'",
            'general', 'uncategorized', 'all-other-categories'
        ];

        $categories = Category::whereNotIn('slug', $bogusCategorySlugs)
            ->where('slug', 'NOT LIKE', '%.url%')
            ->where('slug', 'NOT LIKE', '%"%')
            ->withCount(['deals' => fn($q) => $q->where('status', 'active')])
            ->get()
            ->filter(function ($cat) {
                // Must have a valid alphanumeric slug and at least 1 active deal
                return preg_match('/^[a-z0-9]+(-[a-z0-9]+)*$/', $cat->slug) && (($cat->deals_count ?? 0) > 0);
            });

        // Genuine merchants with valid names and active deals
        $merchants = Merchant::whereNotNull('name')
            ->where('name', '!=', '')
            ->where('name', 'NOT LIKE', '%.url%')
            ->withCount(['deals' => fn($q) => $q->where('status', 'active')])
            ->get()
            ->filter(function ($m) {
                $slug = Str::slug($m->name);
                return !empty($slug) && (($m->deals_count ?? 0) > 0);
            });

        return response()->view('sitemap', [
            'deals' => $deals,
            'articles' => $articles,
            'categories' => $categories,
            'merchants' => $merchants,
        ])->header('Content-Type', 'text/xml');
    }
}
