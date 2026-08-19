<?php

namespace App\Http\Controllers;

use App\Models\Deal;
use App\Models\Category;
use App\Models\Merchant;
use Illuminate\Http\Request;

class SitemapController
{
    public function index()
    {
        $deals = Deal::indexable()
            ->orderBy('created_at', 'desc')
            ->limit(1000)
            ->get();
            
        $articles = \App\Models\Article::where('status', \App\Models\Article::STATUS_PUBLISHED)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->orderBy('published_at', 'desc')
            ->limit(500)
            ->get();

        $categories = Category::all();
        $merchants = Merchant::all();

        return response()->view('sitemap', [
            'deals' => $deals,
            'articles' => $articles,
            'categories' => $categories,
            'merchants' => $merchants,
        ])->header('Content-Type', 'text/xml');
    }
}
