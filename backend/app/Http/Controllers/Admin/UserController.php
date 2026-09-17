<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $role = $request->query('role');
        $query = User::orderBy('created_at', 'desc');

        if ($role && in_array($role, ['admin', 'publisher', 'shopper'])) {
            $query->where('role', $role);
        }

        $users = $query->paginate(20)->withQueryString();
        return view('admin.users', compact('users', 'role'));
    }

    public function destroy(User $user)
    {
        if ($user->role === 'admin') {
            return back()->with('error', 'Cannot ban or delete an administrator.');
        }

        if (auth()->id() === $user->id) {
            return back()->with('error', 'Cannot delete your own account.');
        }

        \Illuminate\Support\Facades\DB::transaction(function () use ($user) {
            if (method_exists($user, 'tokens')) {
                $user->tokens()->delete();
            }
            $user->delete();
        });
        
        return back()->with('success', 'User account has been removed.');
    }
}
