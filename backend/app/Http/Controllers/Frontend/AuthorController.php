<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\AuthorProfile;
use App\Models\Deal;
use Illuminate\Http\Request;

class AuthorController extends Controller
{
    /**
     * Display the specified author profile.
     *
     * @param  string  $slug
     * @return \Illuminate\View\View
     */
    public function show($slug)
    {
        $author = AuthorProfile::with('user')->where('slug', $slug)->firstOrFail();
        
        // Load the most recent published deals reviewed by this author
        $deals = Deal::with(['merchant', 'brandRelation'])
            ->indexable() // Using indexable so it strictly adheres to publication rules
            ->where('editor_id', $author->user_id)
            ->orderBy('reviewed_at', 'desc')
            ->paginate(15);

        return view('author.show', compact('author', 'deals'));
    }
}
