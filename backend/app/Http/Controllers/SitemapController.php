<?php

namespace App\Http\Controllers;

use App\Models\Deal;
use Illuminate\Http\Request;

class SitemapController
{
    public function index()
    {
        $deals = Deal::where('status', 'active')->orderBy('created_at', 'desc')->get();

        return response()->view('sitemap', [
            'deals' => $deals
        ])->header('Content-Type', 'text/xml');
    }
}
