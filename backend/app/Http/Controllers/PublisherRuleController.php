<?php

namespace App\Http\Controllers;

use App\Models\PublisherRule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PublisherRuleController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'keyword' => 'nullable|string|max:255',
            'min_discount' => 'nullable|numeric|min:0|max:100',
            'category_id' => 'nullable|exists:categories,id',
        ]);

        $validated['user_id'] = Auth::id();

        PublisherRule::create($validated);

        return back()->with('success', 'Automation rule added successfully.');
    }

    public function destroy(PublisherRule $rule)
    {
        if ($rule->user_id !== Auth::id()) {
            abort(403);
        }

        $rule->delete();

        return back()->with('success', 'Automation rule removed.');
    }
}
