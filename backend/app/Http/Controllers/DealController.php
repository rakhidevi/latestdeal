<?php

namespace App\Http\Controllers;

use App\Models\Deal;

class DealController extends Controller
{
    public function show(Deal $deal)
    {
        $priceHistory = $deal->priceHistories()->orderBy('recorded_at')->get();
        return view('deals.show', compact('deal', 'priceHistory'));
    }

    public function saveDeal(Deal $deal, \Illuminate\Http\Request $request)
    {
        $user = $request->user();
        if ($user) {
            $user->savedDeals()->syncWithoutDetaching([$deal->id]);
            return back()->with('success', 'Deal saved successfully!');
        }
        return back();
    }
}
