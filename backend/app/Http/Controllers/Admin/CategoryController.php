<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Merchant;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('search', '');
        $query = Category::withCount('deals')->orderBy('name', 'asc');

        if (!empty($search)) {
            $query->where('name', 'like', "%{$search}%")
                  ->orWhere('slug', 'like', "%{$search}%");
        }

        $categories = $query->paginate(20)->withQueryString();
        $merchants = Merchant::orderBy('name')->get();

        return view('admin.categories', compact('categories', 'merchants', 'search'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:categories,slug',
            'top_merchant_id' => 'nullable|exists:merchants,id',
        ]);

        $slug = !empty($validated['slug']) ? Str::slug($validated['slug']) : Str::slug($validated['name']);
        
        $counter = 1;
        $originalSlug = $slug;
        while (Category::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $counter++;
        }

        Category::create([
            'name' => $validated['name'],
            'slug' => $slug,
            'top_merchant_id' => $validated['top_merchant_id'] ?? null,
            'deal_count' => 0,
        ]);

        return back()->with('success', 'Category created successfully!');
    }

    public function update(Request $request, Category $category)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:categories,slug,' . $category->id,
            'top_merchant_id' => 'nullable|exists:merchants,id',
        ]);

        $category->update([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['slug']),
            'top_merchant_id' => $validated['top_merchant_id'] ?? null,
        ]);

        return back()->with('success', 'Category updated successfully!');
    }

    public function destroy(Category $category)
    {
        if ($category->deals()->count() > 0) {
            return back()->with('error', "Cannot delete category '{$category->name}' because it contains {$category->deals()->count()} active deals. Reassign deals first.");
        }

        $category->delete();
        return back()->with('success', 'Category deleted successfully.');
    }
}
