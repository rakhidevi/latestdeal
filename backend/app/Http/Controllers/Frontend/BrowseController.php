<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\SearchPipeline\DealSearchPipeline;
use App\Models\Deal;
use App\Models\Category;
use App\Models\Brand;
use App\Models\Merchant;
use App\Services\SeoService;
use App\Services\BreadcrumbService;

class BrowseController extends Controller
{
    protected $pipeline;
    protected $seoService;
    protected $breadcrumbService;
    protected $recommendationService;

    public function __construct(
        DealSearchPipeline $pipeline, 
        SeoService $seoService, 
        BreadcrumbService $breadcrumbService,
        \App\Services\RecommendationService $recommendationService
    ) {
        $this->pipeline = $pipeline;
        $this->seoService = $seoService;
        $this->breadcrumbService = $breadcrumbService;
        $this->recommendationService = $recommendationService;
    }

    public function index(Request $request)
    {
        $filters = $request->all();
        $deals = $this->pipeline->search($filters);

        if ($request->ajax()) {
            return response()->json([
                'html' => view('partials.deals_grid', compact('deals'))->render(),
                'next_page' => $deals->nextPageUrl(),
                'has_more' => $deals->hasMorePages()
            ]);
        }

        $pageTitle = 'All Deals';
        
        $seoMeta = $this->seoService->generateMeta(
            $pageTitle . ' | LatestDeal',
            'Find the best deals and discounts across all categories.',
            url('/')
        );

        $trendingDeals = null;
        if (empty($filters)) {
            $trendingDeals = $this->recommendationService->getTrending(5);
        }

        return view('welcome', compact('deals', 'pageTitle', 'filters', 'seoMeta', 'trendingDeals'));
    }

    public function byCategory(Request $request, $slug)
    {
        $category = Category::where('slug', $slug)->firstOrFail();
        
        $filters = array_merge($request->all(), ['category_slug' => $slug]);
        $deals = $this->pipeline->search($filters);
        
        $pageTitle = $category->name . ' Deals';
        $breadcrumbs = $this->breadcrumbService->generate([
            ['title' => 'Home', 'url' => '/'],
            ['title' => 'Categories', 'url' => '#'],
            ['title' => $category->name, 'url' => ''],
        ]);
        
        $seoMeta = $this->seoService->generateMeta(
            $pageTitle . ' | LatestDeal',
            'Find the best ' . $category->name . ' deals and discounts.',
            url('/categories/' . $category->slug)
        );
        $schema = $this->breadcrumbService->generateSchema($breadcrumbs);

        return view('welcome', compact('deals', 'pageTitle', 'breadcrumbs', 'filters', 'seoMeta', 'schema', 'category'));
    }

    public function byBrand(Request $request, $slug)
    {
        $brand = Brand::where('slug', $slug)->firstOrFail();
        
        $filters = array_merge($request->all(), ['brand_slug' => $slug]);
        
        $query = Deal::where('status', 'active');

        // Brand ID filter
        $query->where('brand_id', $brand->id);

        // Defensive guard: Exclude generic acoustic noise cancelling/reduction products from Noise brand page
        if (strtolower($slug) === 'noise') {
            $query->whereRaw("LOWER(title) NOT LIKE '%noise cancelling%' AND LOWER(title) NOT LIKE '%noise cancellation%' AND LOWER(title) NOT LIKE '%noise reduction%'");
        }

        $deals = $query->paginate(15);

        // Deduplicate near-identical product entries
        $uniqueCollection = $deals->getCollection()->unique(function ($deal) {
            $cleanTitle = mb_strtolower($deal->title ?? '', 'UTF-8');
            $cleanTitle = preg_replace('/(wireless|bluetooth|headphones|headphone|earphones|earphone|noise|cancelling|cancellation|reduction|new|on-ear|over-ear|in-ear|mic|3-level|adjustable|for|youtube|with|and|brown|midnight|blue|black|white|silver|grey|gold|red|color|1006834|🚨)/i', '', $cleanTitle);
            $cleanTitle = preg_replace('/[^a-z0-9]/', '', $cleanTitle);
            return $cleanTitle . '_' . (int)($deal->discounted_price ?? 0);
        });
        $deals->setCollection($uniqueCollection->values());
        
        $pageTitle = $brand->name . ' Deals';
        $breadcrumbs = $this->breadcrumbService->generate([
            ['title' => 'Home', 'url' => '/'],
            ['title' => 'Brands', 'url' => '#'],
            ['title' => $brand->name, 'url' => ''],
        ]);
        
        $seoMeta = $this->seoService->generateMeta(
            $pageTitle . ' | LatestDeal',
            'Find the best ' . $brand->name . ' deals and discounts.',
            url('/brands/' . $brand->slug)
        );
        $schema = $this->breadcrumbService->generateSchema($breadcrumbs);

        return view('welcome', compact('deals', 'pageTitle', 'breadcrumbs', 'filters', 'seoMeta', 'schema', 'brand'));
    }

    public function byMerchant(Request $request, $slug)
    {
        // Merchant uses name instead of slug right now, let's try to match by name or add slug support
        $merchant = Merchant::where('name', 'like', $slug)->orWhere('domain', 'like', "%$slug%")->firstOrFail();
        
        $filters = array_merge($request->all(), ['merchant_slug' => $merchant->name]); 
        
        $filters['merchant_id'] = $merchant->id;
        $deals = $this->pipeline->search($filters);
        
        $pageTitle = $merchant->name . ' Deals';
        $breadcrumbs = $this->breadcrumbService->generate([
            ['title' => 'Home', 'url' => '/'],
            ['title' => 'Merchants', 'url' => '#'],
            ['title' => $merchant->name, 'url' => ''],
        ]);
        
        $seoMeta = $this->seoService->generateMeta(
            $pageTitle . ' | LatestDeal',
            'Find the best ' . $merchant->name . ' deals and discounts.',
            url('/merchants/' . \Illuminate\Support\Str::slug($merchant->name))
        );
        $schema = $this->breadcrumbService->generateSchema($breadcrumbs);

        return view('welcome', compact('deals', 'pageTitle', 'breadcrumbs', 'filters', 'seoMeta', 'schema', 'merchant'));
    }

    public function byDiscount(Request $request, $range)
    {
        $filters = array_merge($request->all(), []);
        
        // Handle routes like 90-off, 50-69-off
        if (preg_match('/^(\d+)-off$/', $range, $matches)) {
            $filters['discount_range'] = $matches[1] . '+';
            $pageTitle = $matches[1] . '%+ Off Deals';
        } elseif (preg_match('/^(\d+)-(\d+)-off$/', $range, $matches)) {
            $filters['discount_range'] = $matches[1] . '-' . $matches[2];
            $pageTitle = $matches[1] . '% - ' . $matches[2] . '% Off Deals';
        } else {
            abort(404);
        }

        $deals = $this->pipeline->search($filters);

        // Fallback: If no deals match exact discount tier, return top discounted deals sorted by discount percentage
        if ($deals->isEmpty()) {
            unset($filters['discount_range'], $filters['discount_min'], $filters['discount_max']);
            $filters['sort'] = 'discount';
            $deals = $this->pipeline->search($filters);
        }

        $breadcrumbs = $this->breadcrumbService->generate([
            ['title' => 'Home', 'url' => '/'],
            ['title' => 'Discounts', 'url' => '#'],
            ['title' => $pageTitle, 'url' => ''],
        ]);
        
        $seoMeta = $this->seoService->generateMeta(
            $pageTitle . ' | LatestDeal',
            'Browse our collection of ' . $pageTitle . ' from top merchants.',
            url('/deals/' . $range)
        );
        $schema = $this->breadcrumbService->generateSchema($breadcrumbs);

        return view('welcome', compact('deals', 'pageTitle', 'breadcrumbs', 'filters', 'seoMeta', 'schema'));
    }

    public function show(Request $request, $slug)
    {
        $deal = Deal::with(['merchant', 'category', 'brandRelation', 'priceHistories', 'tags'])
            ->where('slug', $slug)
            ->firstOrFail();

        // Price history for the chart section
        $priceHistory = $deal->priceHistories()->orderBy('recorded_at', 'asc')->get();

        // Similar deals from the same category, excluding current
        $similarDeals = Deal::where('status', 'active')
            ->where('id', '!=', $deal->id)
            ->where(function ($q) use ($deal) {
                $q->where('category_id', $deal->category_id)
                  ->orWhere('brand_id', $deal->brand_id);
            })
            ->orderByDesc('ai_score')
            ->limit(4)
            ->get();

        $pageTitle = $deal->title;
        $breadcrumbs = $this->breadcrumbService->generate([
            ['title' => 'Home', 'url' => '/'],
            ['title' => 'Deals', 'url' => '/'],
            ['title' => $deal->title, 'url' => ''],
        ]);
        $seoMeta = $this->seoService->generateMeta(
            $pageTitle . ' | LatestDeal',
            $deal->title . ' - Best deal price ₹' . number_format($deal->discounted_price),
            url('/deal/' . $deal->slug)
        );
        $schema = $this->breadcrumbService->generateSchema($breadcrumbs);

        return view('deals.show', compact('deal', 'pageTitle', 'breadcrumbs', 'seoMeta', 'schema', 'priceHistory', 'similarDeals'));
    }
}
