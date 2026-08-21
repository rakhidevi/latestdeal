<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\DiscoveryProfile;
use App\Models\Merchant;
use App\Services\Admin\DiscoveryProfileService;
use Illuminate\Http\Request;

class DiscoveryProfileController extends Controller
{
    protected $profileService;

    public function __construct(DiscoveryProfileService $profileService)
    {
        $this->profileService = $profileService;
    }

    public function index()
    {
        $profiles = $this->profileService->getAllProfiles();
        $brands = Brand::orderBy('name')->get();
        $categories = Category::orderBy('name')->get();
        $merchants = Merchant::orderBy('name')->get();

        return view('admin.discovery-profiles', compact('profiles', 'brands', 'categories', 'merchants'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'brand_id' => 'nullable|exists:brands,id',
            'category_id' => 'nullable|exists:categories,id',
            'product_type' => 'nullable|string|max:255',
            'merchant_id' => 'nullable|exists:merchants,id',
            'min_discount_percent' => 'nullable|numeric|min:0|max:100',
            'max_discount_percent' => 'nullable|numeric|min:0|max:100',
            'min_price' => 'nullable|numeric|min:0',
            'max_price' => 'nullable|numeric|min:0',
            'keywords' => 'nullable|string',
            'run_interval' => 'required|integer|min:5',
            'status' => 'required|in:active,paused',
        ]);

        $this->profileService->createProfile($validated);

        return back()->with('success', 'Discovery Profile created successfully.');
    }

    public function update(Request $request, DiscoveryProfile $profile)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'brand_id' => 'nullable|exists:brands,id',
            'category_id' => 'nullable|exists:categories,id',
            'product_type' => 'nullable|string|max:255',
            'merchant_id' => 'nullable|exists:merchants,id',
            'min_discount_percent' => 'nullable|numeric|min:0|max:100',
            'max_discount_percent' => 'nullable|numeric|min:0|max:100',
            'min_price' => 'nullable|numeric|min:0',
            'max_price' => 'nullable|numeric|min:0',
            'keywords' => 'nullable|string',
            'run_interval' => 'required|integer|min:5',
        ]);

        $this->profileService->updateProfile($profile, $validated);

        return back()->with('success', 'Discovery Profile updated successfully.');
    }

    public function destroy(DiscoveryProfile $profile)
    {
        $this->profileService->deleteProfile($profile);
        return back()->with('success', 'Discovery Profile deleted successfully.');
    }

    public function toggle(DiscoveryProfile $profile)
    {
        $this->profileService->toggleStatus($profile);
        return back()->with('success', 'Discovery Profile status toggled.');
    }
}
