<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SocialAccount;
use App\Services\Admin\SocialAccountService;
use Illuminate\Http\Request;

class SocialAccountController extends Controller
{
    protected $socialAccountService;

    public function __construct(SocialAccountService $socialAccountService)
    {
        $this->socialAccountService = $socialAccountService;
    }

    public function index()
    {
        $accounts = $this->socialAccountService->getAllAccounts();
        return view('admin.social-accounts', compact('accounts'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'platform' => 'required|in:telegram,instagram,facebook,twitter',
            'account_name' => 'required|string',
            'access_token' => 'required|string',
            'target_id' => 'required|string',
        ]);

        $this->socialAccountService->createAccount($validated, auth()->id());
        return back()->with('success', 'Social account added successfully.');
    }

    public function destroy(SocialAccount $socialAccount)
    {
        $this->socialAccountService->deleteAccount($socialAccount);
        return back()->with('success', 'Social account deleted successfully.');
    }

    public function toggle(SocialAccount $socialAccount)
    {
        $this->socialAccountService->toggleAccountStatus($socialAccount);
        return back()->with('success', 'Social account status updated.');
    }
}
