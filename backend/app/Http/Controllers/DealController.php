<?php

namespace App\Http\Controllers;

use App\Models\Deal;
use App\Services\RecommendationService;

class DealController extends Controller
{
    public function show(Deal $deal, \App\Services\User\InteractionService $interactionService, RecommendationService $recommendationService)
    {
        if (! $deal->isIndexable()) {
            if (strtolower($deal->status) === strtolower(\App\Models\Deal::STATUS_EXPIRED)) {
                abort(410);
            }
            abort(404);
        }

        $priceHistory = $deal->priceHistories()->orderBy('recorded_at')->get();

        // Use keyword + tag scoring for semantically relevant results.
        // Falls back to trending deals with an honest heading when no close matches exist.
        $similarDealsResult = $recommendationService->getSimilarDeals($deal, 4);
        $similarDeals = $similarDealsResult['deals'];
        $similarDealsHeading = $similarDealsResult['heading'];

        // Track Interaction if logged in
        if (auth()->check()) {
            $interactionService->record('deal_view', 'deal_page', $deal->id, [
                'title' => $deal->title,
                'price' => $deal->discounted_price,
                'discount' => $deal->discount_percentage ?? 0
            ]);
        }

        return view('deals.show', compact('deal', 'priceHistory', 'similarDeals', 'similarDealsHeading'));
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
