<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Deal;
use App\Models\DealAiGeneration;
use Illuminate\Http\Request;

class ReviewQueueController extends Controller
{
    /**
     * Display the review queue.
     */
    public function index()
    {
        // Get deals IN_REVIEW, ordered by oldest first
        $deals = Deal::where('editorial_status', Deal::STATUS_IN_REVIEW)
                     ->orderBy('updated_at', 'asc')
                     ->paginate(20);
                     
        return view('admin.deals.review-queue', compact('deals'));
    }

    /**
     * Approve and Publish a deal.
     */
    public function approve(Request $request, $id)
    {
        $deal = Deal::findOrFail($id);
        
        if ($deal->editorial_status !== Deal::STATUS_IN_REVIEW) {
            return back()->with('error', 'Deal is not in review state.');
        }

        // Apply any manual edits if provided
        $deal->fill($request->only([
            'editorial_summary', 
            'editorial_verdict', 
            'pros', 
            'cons', 
            'best_for', 
            'not_for'
        ]));

        if (!$deal->canPublish()) {
            return back()->with('error', 'Deal does not meet all publication requirements. Ensure all editorial fields are filled and a QA pass exists.');
        }

        $deal->editorial_status = Deal::STATUS_PUBLISHED;
        $deal->reviewed_at = now();
        $deal->editor_id = auth()->id() ?? 1; // Fallback if no auth in testing
        
        $deal->save();
        
        return back()->with('success', 'Deal approved and published!');
    }

    /**
     * Reject a deal (sends back to DRAFT).
     */
    public function reject(Request $request, $id)
    {
        $deal = Deal::findOrFail($id);
        
        if ($deal->editorial_status !== Deal::STATUS_IN_REVIEW) {
            return back()->with('error', 'Deal is not in review state.');
        }

        $deal->editorial_status = Deal::STATUS_DRAFT;
        $deal->quality_feedback = $request->input('reason', 'Manually rejected from Review Queue.');
        $deal->save();
        
        return back()->with('success', 'Deal rejected and returned to draft.');
    }

    /**
     * Trigger a granular regeneration request.
     */
    public function regenerate(Request $request, $id)
    {
        $deal = Deal::findOrFail($id);
        
        $target = $request->input('target', 'all');
        $validTargets = ['all', 'summary', 'verdict', 'pros_cons', 'best_for', 'not_for'];
        
        if (!in_array($target, $validTargets)) {
            return back()->with('error', 'Invalid regeneration target.');
        }
        
        // Transition to AI_GENERATING
        $deal->editorial_status = Deal::STATUS_AI_GENERATING;
        $deal->save();
        
        // Create a pending generation record
        DealAiGeneration::create([
            'deal_id' => $deal->id,
            'generation_number' => DealAiGeneration::where('deal_id', $deal->id)->count() + 1,
            'generation_target' => $target,
            'status' => 'pending'
        ]);
        
        return back()->with('success', "Regeneration queued for: {$target}");
    }
}
