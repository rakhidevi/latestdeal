<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class BrandController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('search', '');
        $query = Brand::withCount('deals')->orderBy('name', 'asc');

        if (!empty($search)) {
            $query->where('name', 'like', "%{$search}%")
                  ->orWhere('slug', 'like', "%{$search}%");
        }

        $brands = $query->paginate(24)->withQueryString();

        return view('admin.brands', compact('brands', 'search'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:brands,slug',
            'logo' => 'nullable|string|max:1000',
            'is_active' => 'nullable|boolean',
        ]);

        $slug = !empty($validated['slug']) ? Str::slug($validated['slug']) : Str::slug($validated['name']);
        
        $counter = 1;
        $originalSlug = $slug;
        while (Brand::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $counter++;
        }

        Brand::create([
            'name' => $validated['name'],
            'slug' => $slug,
            'logo' => $validated['logo'] ?? null,
            'is_active' => $request->boolean('is_active', true),
            'deal_count' => 0,
        ]);

        return back()->with('success', 'Brand created successfully!');
    }

    public function update(Request $request, Brand $brand)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:brands,slug,' . $brand->id,
            'logo' => 'nullable|string|max:1000',
            'is_active' => 'nullable|boolean',
        ]);

        $brand->update([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['slug']),
            'logo' => $validated['logo'] ?? null,
            'is_active' => $request->boolean('is_active'),
        ]);

        return back()->with('success', 'Brand updated successfully!');
    }

    public function destroy(Brand $brand)
    {
        if ($brand->deals()->count() > 0) {
            return back()->with('error', "Cannot delete brand '{$brand->name}' because {$brand->deals()->count()} deals are assigned to it. Reassign deals first.");
        }

        $brand->delete();
        return back()->with('success', 'Brand deleted successfully.');
    }
}
