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
            'name' => 'required|string|max:255',
            'domain' => 'required|string|max:255|unique:merchants,domain',
            'store_id' => 'required|string|max:255',
            'affiliate_param_key' => 'required|string|max:255',
            'status' => 'boolean'
        ]);

        $validated['status'] = $request->has('status');

        $this->merchantService->createMerchant($validated);
        return back()->with('success', 'Merchant created successfully!');
    }

    public function update(Request $request, Merchant $merchant)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'domain' => 'required|string|max:255|unique:merchants,domain,' . $merchant->id,
            'store_id' => 'required|string|max:255',
            'affiliate_param_key' => 'required|string|max:255',
            'status' => 'boolean'
        ]);

        $validated['status'] = $request->has('status');

        $this->merchantService->updateMerchant($merchant, $validated);
        return back()->with('success', 'Merchant updated successfully!');
    }

    public function destroy(Merchant $merchant)
    {
        if ($merchant->deals()->exists()) {
            return back()->with('error', 'Cannot delete merchant with active catalog deals. Reassign or delete the deals first.');
        }

        $merchant->delete();
        return back()->with('success', 'Merchant removed successfully.');
    }
}
