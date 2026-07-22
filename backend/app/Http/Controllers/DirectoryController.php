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
        $categories = Category::where('slug', '!=', 'general')
            ->where('name', '!=', 'General')
            ->where('name', '!=', 'All Other Categories')
            ->withCount(['deals as active_deals_count' => function ($q) {
                $q->where('status', 'active');
            }])
            ->get()
            ->filter(function ($cat) {
                return ($cat->active_deals_count ?? 0) > 0;
            })
            ->map(function ($cat) {
                $cat->deal_count = $cat->active_deals_count ?? $cat->deal_count ?? 0;
                if (empty($cat->icon) || $cat->icon === '📦') {
                    $icons = [
                        'electronics' => '💻',
                        'home-kitchen' => '🏡',
                        'sports-fitness' => '🏋️',
                        'fashion-accessories' => '👗',
                        'beauty-personal-care' => '💄',
                        'mobile-phones' => '📱',
                        'data-storage-devices' => '💾',
                        'televisions' => '📺',
                        'smart-watches' => '⌚',
                        'personal-computers' => '🖥️',
                        'bill-payment-recharges' => '💳',
                        'gaming' => '🎮',
                        'courses-education' => '🎓'
                    ];
                    $cat->icon = $icons[$cat->slug] ?? '📦';
                }
                return $cat;
            })
            ->sortByDesc('deal_count')
            ->values();

        return view('directory.categories', compact('categories'));
    }

    public function brands(Request $request)
    {
        $brands = Brand::where('is_active', true)
            ->withCount(['deals as active_deals_count' => function ($q) {
                $q->where('status', 'active');
            }])
            ->get()
            ->filter(function ($b) {
                return ($b->active_deals_count ?? 0) > 0;
            })
            ->map(function ($b) {
                $b->deal_count = $b->active_deals_count ?? $b->deal_count ?? 0;
                return $b;
            })
            ->sortByDesc('deal_count')
            ->values();

        return view('directory.brands', compact('brands'));
    }

    public function merchants(Request $request)
    {
        $merchants = Merchant::active()
            ->withCount(['deals as active_deals_count' => function ($q) {
                $q->where('status', 'active');
            }])
            ->get()
            ->filter(function ($m) {
                return ($m->active_deals_count ?? 0) > 0;
            })
            ->map(function ($m) {
                $m->deal_count = $m->active_deals_count ?? $m->deal_count ?? 0;
                return $m;
            })
            ->sortByDesc('deal_count')
            ->values();

        return view('directory.merchants', compact('merchants'));
    }
}
