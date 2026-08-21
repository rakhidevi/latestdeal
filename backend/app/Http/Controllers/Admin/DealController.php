<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Deal;
use App\Services\Admin\DealAdminService;
use Illuminate\Http\Request;

class DealController extends Controller
{
    protected $dealAdminService;

    public function __construct(DealAdminService $dealAdminService)
    {
        $this->dealAdminService = $dealAdminService;
    }

    public function index(Request $request)
    {
        $status = $request->get('status', 'pending');
        $search = $request->get('search', '');
        
        $data = $this->dealAdminService->getDealsCatalogData($status, $search);

        return view('admin.deals', $data);
    }

    public function updateStatus(Request $request, Deal $deal)
    {
        $request->validate(['status' => 'required|in:active,rejected,pending']);
        
        $this->dealAdminService->updateDealStatus($deal, $request->status);
        
        return back()->with('success', 'Deal status updated to ' . $request->status);
    }

    public function destroy(Deal $deal)
    {
        $this->dealAdminService->destroyDeal($deal);
        
        return back()->with('success', 'Deal permanently deleted.');
    }

    public function purgeIllegal()
    {
        $count = $this->dealAdminService->purgeIllegalDeals();
        
        return back()->with('success', "Purged {$count} illegal/pirated deals.");
    }
}
