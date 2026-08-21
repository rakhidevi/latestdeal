<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Merchant;
use App\Services\Admin\LinkService;
use Illuminate\Http\Request;

class LinkController extends Controller
{
    protected $linkService;

    public function __construct(LinkService $linkService)
    {
        $this->linkService = $linkService;
    }

    public function index()
    {
        $merchants = Merchant::all();
        return view('admin.links', compact('merchants'));
    }

    public function generate(Request $request)
    {
        $request->validate([
            'url' => 'required|url',
            'merchant_id' => 'nullable|exists:merchants,id',
            'sub_id' => 'nullable|string'
        ]);

        $trackedUrl = $this->linkService->generateTrackedUrl(
            $request->url, 
            $request->merchant_id, 
            $request->sub_id
        );

        if (!$trackedUrl) {
            return response()->json(['error' => 'Please select a merchant explicitly.'], 400);
        }

        return response()->json(['url' => $trackedUrl]);
    }
}
