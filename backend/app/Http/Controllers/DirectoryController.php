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
        $query = Category::where('slug', '!=', 'general');
        if (\Illuminate\Support\Facades\Schema::hasColumn('categories', 'deal_count')) {
            $query->where('deal_count', '>', 0)->orderBy('deal_count', 'desc');
        }
        $categories = $query->get();

        return view('directory.categories', compact('categories'));
    }

    public function brands(Request $request)
    {
        $query = Brand::where('is_active', true);
        if (\Illuminate\Support\Facades\Schema::hasColumn('brands', 'deal_count')) {
            $query->where('deal_count', '>', 0)->orderBy('deal_count', 'desc');
        }
        $brands = $query->get();

        return view('directory.brands', compact('brands'));
    }

    public function merchants(Request $request)
    {
        $query = Merchant::active();
        if (\Illuminate\Support\Facades\Schema::hasColumn('merchants', 'deal_count')) {
            $query->where('deal_count', '>', 0)->orderBy('deal_count', 'desc');
        }
        $merchants = $query->get();

        return view('directory.merchants', compact('merchants'));
    }
}
