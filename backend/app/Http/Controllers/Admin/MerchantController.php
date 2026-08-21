<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Merchant;
use App\Services\Admin\MerchantService;
use Illuminate\Http\Request;

class MerchantController extends Controller
{
    protected $merchantService;

    public function __construct(MerchantService $merchantService)
    {
        $this->merchantService = $merchantService;
    }

    public function index()
    {
        $merchants = $this->merchantService->getAllMerchants();
        return view('admin.merchants', compact('merchants'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'domain' => 'required|string',
            'store_id' => 'required|string',
            'affiliate_param_key' => 'required|string',
            'status' => 'boolean'
        ]);

        $validated['status'] = $request->has('status');

        $this->merchantService->createMerchant($validated);
        return back()->with('success', 'Merchant created successfully!');
    }

    public function update(Request $request, Merchant $merchant)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'domain' => 'required|string',
            'store_id' => 'required|string',
            'affiliate_param_key' => 'required|string',
            'status' => 'boolean'
        ]);

        $validated['status'] = $request->has('status');

        $this->merchantService->updateMerchant($merchant, $validated);
        return back()->with('success', 'Merchant updated successfully!');
    }
}
