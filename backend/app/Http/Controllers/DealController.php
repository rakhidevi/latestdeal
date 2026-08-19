<?php

namespace App\Http\Controllers;

use App\Models\Deal;

class DealController extends Controller
{
    public function show(Deal $deal, \App\Services\User\InteractionService $interactionService)
    {
        if (! $deal->isIndexable()) {
            if ($deal->status === \App\Models\Deal::STATUS_EXPIRED) {
                abort(410);
            }
            abort(404);
        }

        $priceHistory = $deal->priceHistories()->orderBy('recorded_at')->get();
        
        $similarDeals = Deal::where('category_id', $deal->category_id)
            ->where('id', '!=', $deal->id)
            ->where('status', 'active')
            ->limit(4)
            ->get();
            
        // Track Interaction if logged in
        if (auth()->check()) {
            $interactionService->record('deal_view', 'deal_page', $deal->id, [
                'title' => $deal->title,
                'price' => $deal->discounted_price,
                'discount' => $deal->discount_percentage ?? 0
            ]);
        }

        return view('deals.show', compact('deal', 'priceHistory', 'similarDeals'));
    }

    public function saveDeal(Deal $deal, \Illuminate\Http\Request $request, \App\Services\User\InteractionService $interactionService)
    {
        $user = $request->user();
        if ($user) {
            $user->savedDeals()->syncWithoutDetaching([$deal->id]);
            
            $interactionService->record('deal_save', $request->input('source', 'dashboard'), $deal->id, [
                'title' => $deal->title,
                'price' => $deal->discounted_price
            ]);
            
            return back()->with('success', 'Deal saved successfully!');
        }
        return back();
    }
}
