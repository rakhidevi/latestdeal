<?php

namespace App\Http\Controllers;

use App\Models\Article;
use Illuminate\Http\Request;

class ArticleController extends Controller
{
    public function index()
    {
        $articles = Article::with(['author', 'category'])
            ->where('status', Article::STATUS_PUBLISHED)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->orderBy('published_at', 'desc')
            ->paginate(12);

        $seoMeta = [
            'title' => 'Buying Guides & Deal Analysis | LatestDeal',
            'description' => 'Expert buying guides, price history analysis, and data-driven shopping tips from the LatestDeal editorial team.',
            'canonical' => route('articles.index'),
        ];

        return view('articles.index', compact('articles', 'seoMeta'));
    }

    public function show($slug)
    {
        // 301 Permanent Redirect for legacy 2024 slugs to current 2026 edition
        if (str_contains($slug, '-2024')) {
            $updatedSlug = str_replace('-2024', '-2026', $slug);
            if (Article::where('slug', $updatedSlug)->exists()) {
                return redirect()->route('articles.show', $updatedSlug, 301);
            }
        }

        $article = Article::with(['author', 'category', 'tags'])
            ->where('slug', $slug)
            ->where('status', Article::STATUS_PUBLISHED)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->firstOrFail();

        // SEO Meta
        $seoMeta = $article->seo_metadata ?? [];
        $seoMeta['title'] = $seoMeta['title'] ?? ($article->title . ' | LatestDeal Guides');
        $seoMeta['description'] = $seoMeta['description'] ?? $article->summary;
        $seoMeta['canonical'] = route('articles.show', $article->slug);

        return view('articles.show', compact('article', 'seoMeta'));
    }
}
