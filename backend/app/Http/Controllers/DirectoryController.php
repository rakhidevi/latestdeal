<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Brand;
use App\Models\Merchant;
use App\Models\Deal;
use Illuminate\Http\Request;

class DirectoryController extends Controller
{
    public function categories(Request $request)
    {
        $categories = Category::where('deal_count', '>', 0)
            ->where('slug', '!=', 'general')
            ->orderBy('deal_count', 'desc')
            ->get();

        return view('directory.categories', compact('categories'));
    }

    public function brands(Request $request)
    {
        $brands = Brand::where('is_active', true)
            ->where('deal_count', '>', 0)
            ->orderBy('deal_count', 'desc')
            ->get();

        return view('directory.brands', compact('brands'));
    }

    public function merchants(Request $request)
    {
        $merchants = Merchant::active()
            ->where('deal_count', '>', 0)
            ->orderBy('deal_count', 'desc')
            ->get();

        return view('directory.merchants', compact('merchants'));
    }
}
