<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DiscoveryProfile;
use Illuminate\Http\Request;

class DiscoveryProfileApiController extends Controller
{
    public function getActiveProfiles()
    {
        $profiles = DiscoveryProfile::with(['brand', 'category'])
            ->where('status', 'active')
            ->where(function($q) {
                $q->whereNull('next_run_at')
                  ->orWhere('next_run_at', '<=', now());
            })
            ->get();

        return response()->json($profiles);
    }

    public function updateProfileStatus(Request $request, DiscoveryProfile $profile)
    {
        $validated = $request->validate([
            'last_run_status' => 'required|string',
            'last_run_count' => 'required|integer',
            'last_error' => 'nullable|string',
        ]);

        $profile->update([
            'last_run_at' => now(),
            'next_run_at' => now()->addMinutes($profile->run_interval),
            'last_run_status' => $validated['last_run_status'],
            'last_run_count' => $validated['last_run_count'],
            'last_error' => $validated['last_error'],
        ]);

        return response()->json(['success' => true]);
    }
}
