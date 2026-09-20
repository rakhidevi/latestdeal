<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Deal;
use App\Models\Merchant;
use App\Services\Admin\DealAdminService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class DealController extends Controller
{
    protected $dealAdminService;

    public function __construct(DealAdminService $dealAdminService)
    {
        $this->dealAdminService = $dealAdminService;
    }

    public function index(Request $request)
    {
        $status = $request->get('status', 'active');
        $search = $request->get('search', '');
        
        $data = $this->dealAdminService->getDealsCatalogData($status, $search);

        return view('admin.deals', $data);
    }

    public function create()
    {
        $categories = Category::orderBy('name')->get();
        $merchants = Merchant::orderBy('name')->get();
        $brands = Brand::orderBy('name')->get();

        return view('admin.deals.create', compact('categories', 'merchants', 'brands'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:500',
            'merchant_id' => 'required|exists:merchants,id',
            'category_id' => 'nullable|exists:categories,id',
            'brand_id' => 'nullable|exists:brands,id',
            'original_price' => 'nullable|numeric|min:0',
            'discounted_price' => 'required|numeric|min:0',
            'url' => 'required|url|max:2000',
            'image_path' => 'nullable|string|max:1000',
            'image_file' => 'nullable|image|max:3072',
            'coupon_code' => 'nullable|string|max:100',
            'status' => 'required|in:active,pending,rejected',
            'editorial_summary' => 'nullable|string',
            'editorial_verdict' => 'nullable|string',
            'pros' => 'nullable|string',
            'cons' => 'nullable|string',
            'is_editor_pick' => 'nullable|boolean',
        ]);

        $originalPrice = !empty($validated['original_price']) ? (float)$validated['original_price'] : 0.0;
        $discountedPrice = (float)$validated['discounted_price'];
        $discountPercentage = 0;
        $amountSaved = 0.0;

        if ($originalPrice > $discountedPrice && $originalPrice > 0) {
            $amountSaved = $originalPrice - $discountedPrice;
            $discountPercentage = round(($amountSaved / $originalPrice) * 100);
        }

        $imagePath = $validated['image_path'] ?? null;
        if ($request->hasFile('image_file')) {
            $path = $request->file('image_file')->store('deals', 'public');
            $imagePath = '/storage/' . $path;
        }

        $pros = !empty($validated['pros']) ? array_filter(array_map('trim', explode("\n", $validated['pros']))) : null;
        $cons = !empty($validated['cons']) ? array_filter(array_map('trim', explode("\n", $validated['cons']))) : null;

        $slugBase = Str::slug($validated['title']);
        $slug = $slugBase;
        $counter = 1;
        while (Deal::where('slug', $slug)->exists()) {
            $slug = $slugBase . '-' . $counter++;
        }

        $deal = Deal::create([
            'title' => $validated['title'],
            'slug' => $slug,
            'hash_id' => Str::random(10),
            'merchant_id' => $validated['merchant_id'],
            'category_id' => $validated['category_id'] ?? null,
            'brand_id' => $validated['brand_id'] ?? null,
            'original_price' => $originalPrice > 0 ? $originalPrice : null,
            'discounted_price' => $discountedPrice,
            'discount_percentage' => $discountPercentage,
            'amount_saved' => $amountSaved > 0 ? $amountSaved : null,
            'url' => $validated['url'],
            'image_path' => $imagePath,
            'coupon_code' => $validated['coupon_code'] ?? null,
            'status' => $validated['status'],
            'editorial_status' => $validated['status'] === 'active' ? Deal::STATUS_PUBLISHED : Deal::STATUS_DRAFT,
            'editorial_summary' => $validated['editorial_summary'] ?? null,
            'editorial_verdict' => $validated['editorial_verdict'] ?? null,
            'pros' => $pros,
            'cons' => $cons,
            'is_editor_pick' => $request->boolean('is_editor_pick'),
        ]);

        return redirect()->route('admin.deals', ['status' => $deal->status])
            ->with('success', 'Deal created successfully!');
    }

    public function edit(Deal $deal)
    {
        $categories = Category::orderBy('name')->get();
        $merchants = Merchant::orderBy('name')->get();
        $brands = Brand::orderBy('name')->get();

        return view('admin.deals.edit', compact('deal', 'categories', 'merchants', 'brands'));
    }

    public function update(Request $request, Deal $deal)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:500',
            'merchant_id' => 'required|exists:merchants,id',
            'category_id' => 'nullable|exists:categories,id',
            'brand_id' => 'nullable|exists:brands,id',
            'original_price' => 'nullable|numeric|min:0',
            'discounted_price' => 'required|numeric|min:0',
            'url' => 'required|url|max:2000',
            'image_path' => 'nullable|string|max:1000',
            'image_file' => 'nullable|image|max:3072',
            'coupon_code' => 'nullable|string|max:100',
            'status' => 'required|in:active,pending,rejected',
            'editorial_summary' => 'nullable|string',
            'editorial_verdict' => 'nullable|string',
            'pros' => 'nullable|string',
            'cons' => 'nullable|string',
            'is_editor_pick' => 'nullable|boolean',
        ]);

        $originalPrice = !empty($validated['original_price']) ? (float)$validated['original_price'] : 0.0;
        $discountedPrice = (float)$validated['discounted_price'];
        $discountPercentage = 0;
        $amountSaved = 0.0;

        if ($originalPrice > $discountedPrice && $originalPrice > 0) {
            $amountSaved = $originalPrice - $discountedPrice;
            $discountPercentage = round(($amountSaved / $originalPrice) * 100);
        }

        $imagePath = $deal->image_path;
        if (!empty($validated['image_path'])) {
            $imagePath = $validated['image_path'];
        }
        if ($request->hasFile('image_file')) {
            $path = $request->file('image_file')->store('deals', 'public');
            $imagePath = '/storage/' . $path;
        }

        $pros = !empty($validated['pros']) ? array_filter(array_map('trim', explode("\n", $validated['pros']))) : null;
        $cons = !empty($validated['cons']) ? array_filter(array_map('trim', explode("\n", $validated['cons']))) : null;

        $deal->update([
            'title' => $validated['title'],
            'merchant_id' => $validated['merchant_id'],
            'category_id' => $validated['category_id'] ?? null,
            'brand_id' => $validated['brand_id'] ?? null,
            'original_price' => $originalPrice > 0 ? $originalPrice : null,
            'discounted_price' => $discountedPrice,
            'discount_percentage' => $discountPercentage,
            'amount_saved' => $amountSaved > 0 ? $amountSaved : null,
            'url' => $validated['url'],
            'image_path' => $imagePath,
            'coupon_code' => $validated['coupon_code'] ?? null,
            'status' => $validated['status'],
            'editorial_status' => $validated['status'] === 'active' ? Deal::STATUS_PUBLISHED : ($validated['status'] === 'rejected' ? Deal::STATUS_REJECTED : Deal::STATUS_DRAFT),
            'editorial_summary' => $validated['editorial_summary'] ?? null,
            'editorial_verdict' => $validated['editorial_verdict'] ?? null,
            'pros' => $pros,
            'cons' => $cons,
            'is_editor_pick' => $request->boolean('is_editor_pick'),
        ]);

        return redirect()->route('admin.deals', ['status' => $deal->status])
            ->with('success', 'Deal updated successfully!');
    }

    public function duplicate(Deal $deal)
    {
        $newDeal = $deal->replicate([
            'id', 'created_at', 'updated_at', 'slug', 'hash_id', 'total_clicks', 'clicks_count'
        ]);

        $newDeal->title = '[COPY] ' . $deal->title;
        $slugBase = Str::slug($newDeal->title);
        $slug = $slugBase;
        $counter = 1;
        while (Deal::where('slug', $slug)->exists()) {
            $slug = $slugBase . '-' . $counter++;
        }
        $newDeal->slug = $slug;
        $newDeal->hash_id = Str::random(10);
        $newDeal->status = 'pending';
        $newDeal->editorial_status = Deal::STATUS_DRAFT;
        $newDeal->save();

        return redirect()->route('admin.deals.edit', $newDeal->id)
            ->with('success', 'Deal duplicated! Review and update before publishing.');
    }

    public function updateStatus(Request $request, Deal $deal)
    {
        $request->validate(['status' => 'required|in:active,rejected,pending']);
        
        $this->dealAdminService->updateDealStatus($deal, $request->status);
        
        return back()->with('success', 'Deal status updated to ' . $request->status);
    }

    public function destroy(Deal $deal)
    {
        $this->dealAdminService->destroyDeal($deal);
        
        return back()->with('success', 'Deal permanently deleted.');
    }

    public function purgeIllegal()
    {
        $count = $this->dealAdminService->purgeIllegalDeals();
        
        return back()->with('success', "Purged {$count} illegal/pirated deals.");
    }
}
