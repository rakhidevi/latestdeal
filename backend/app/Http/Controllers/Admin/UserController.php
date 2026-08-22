<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index()
    {
        $users = User::orderBy('created_at', 'desc')->paginate(20);
        return view('admin.users', compact('users'));
    }

    public function destroy(User $user)
    {
        if ($user->role === 'admin') {
            return back()->with('error', 'Cannot ban an administrator.');
        }

        $user->delete();
        
        return back()->with('success', 'User has been banned and removed.');
    }
}
