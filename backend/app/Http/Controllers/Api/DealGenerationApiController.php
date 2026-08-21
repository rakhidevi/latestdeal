<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Deal;
use App\Models\DealAiGeneration;
use Illuminate\Http\Request;

class DealGenerationApiController extends Controller
{
    /**
     * Get a pending deal for AI generation.
     */
    public function claim(Request $request)
    {
        // Deals needing AI generation
        $deal = Deal::where('editorial_status', Deal::STATUS_AI_GENERATING)
                    ->orderBy('updated_at', 'asc')
                    ->first();
                    
        if (!$deal) {
            return response()->json(['message' => 'No deals pending generation.'], 404);
        }

        // Determine what target to generate based on latest generation history if any
        // We look for a pending generation record first. If none exists, we assume 'all'.
        $pendingGen = DealAiGeneration::where('deal_id', $deal->id)
                                      ->where('status', 'pending')
                                      ->first();
                                      
        $generationTarget = $pendingGen ? $pendingGen->generation_target : 'all';
        $generationId = $pendingGen ? $pendingGen->id : null;
        
        return response()->json([
            'deal' => $deal,
            'generation_target' => $generationTarget,
            'pending_generation_id' => $generationId
        ]);
    }

    /**
     * Post the generation content and QA results.
     */
    public function submit(Request $request, $id)
    {
        $request->validate([
            'content' => 'required|array',
            'source_facts' => 'required|array',
            'qa_result' => 'required|in:PASS,FAIL',
            'qa_feedback' => 'nullable|string',
            'model' => 'nullable|string',
            'provider' => 'nullable|string',
            'generation_target' => 'required|string',
            'pending_generation_id' => 'nullable|integer'
        ]);
        
        $deal = Deal::findOrFail($id);
        
        if ($deal->editorial_status !== Deal::STATUS_AI_GENERATING && $deal->editorial_status !== Deal::STATUS_QUALITY_CHECK) {
            return response()->json(['message' => 'Deal is not in a generating state.'], 400);
        }
        
        $genNumber = DealAiGeneration::where('deal_id', $deal->id)->count() + 1;
        
        $genId = $request->pending_generation_id;
        
        if ($genId) {
            $generation = DealAiGeneration::findOrFail($genId);
            $generation->update([
                'content' => $request->input('content'),
                'source_facts' => $request->input('source_facts'),
                'qa_result' => $request->input('qa_result'),
                'qa_feedback' => $request->input('qa_feedback'),
                'model' => $request->input('model'),
                'provider' => $request->input('provider'),
                'status' => 'pending' // Still pending manual review
            ]);
        } else {
            $generation = DealAiGeneration::create([
                'deal_id' => $deal->id,
                'generation_number' => $genNumber,
                'generation_target' => $request->input('generation_target'),
                'model' => $request->input('model'),
                'provider' => $request->input('provider'),
                'content' => $request->input('content'),
                'source_facts' => $request->input('source_facts'),
                'qa_result' => $request->input('qa_result'),
                'qa_feedback' => $request->input('qa_feedback'),
                'status' => 'pending'
            ]);
        }
        
        // Temporarily step through QUALITY_CHECK if needed, or directly handle it
        // The spec implies the worker does the QA and submits it all at once
        if ($request->input('qa_result') === 'PASS') {
            // Apply the content to the deal
            $content = $request->input('content');
            
            if (isset($content['editorial_summary'])) $deal->editorial_summary = $content['editorial_summary'];
            if (isset($content['editorial_verdict'])) $deal->editorial_verdict = $content['editorial_verdict'];
            if (isset($content['pros'])) $deal->pros = $content['pros'];
            if (isset($content['cons'])) $deal->cons = $content['cons'];
            if (isset($content['best_for'])) $deal->best_for = $content['best_for'];
            if (isset($content['not_for'])) $deal->not_for = $content['not_for'];
            
            $deal->editorial_status = Deal::STATUS_IN_REVIEW;
        } else {
            // Failed QA, revert to DRAFT
            $deal->editorial_status = Deal::STATUS_DRAFT;
            $deal->quality_feedback = $request->input('qa_feedback');
        }
        
        $deal->save();
        
        return response()->json([
            'message' => 'Generation and QA recorded successfully.', 
            'status' => $deal->editorial_status
        ]);
    }
}
