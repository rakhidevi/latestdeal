<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PublisherTokenController extends Controller
{
    public function store(Request $request)
    {
        $tokenName = $request->input('name', 'Publisher API Token');
        $token = $request->user()->createToken($tokenName);

        return redirect()->back()->with('api_token', $token->plainTextToken);
    }

    public function destroy(Request $request, $id)
    {
        $request->user()->tokens()->where('id', $id)->delete();
        return redirect()->back()->with('success', 'API Token revoked successfully.');
    }
}
