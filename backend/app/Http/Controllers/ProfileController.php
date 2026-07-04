<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Click;

class ProfileController extends Controller
{
    public function destroy(Request $request)
    {
        $user = Auth::user();

        // GDPR: Anonymize IP addresses for this publisher's clicks instead of deleting the analytics
        Click::where('user_id', $user->id)->update([
            'ip_address' => 'anonymized',
            'user_id' => null // Unlink from user
        ]);

        // Cascading delete will handle social_accounts, saved_deals, etc.
        $user->delete();

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/')->with('status', 'Account deleted successfully.');
    }
}
