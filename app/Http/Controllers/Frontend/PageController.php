<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\View\View;

class PageController extends Controller
{
    public function home(): View
    {
        $latestPosts = Post::query()
            ->published()
            ->with(['category', 'user'])
            ->latest('published_at')
            ->limit(3)
            ->get();

        return view('frontend.home', compact('latestPosts'));
    }

    public function about(): View
    {
        return view('frontend.about');
    }

    public function services(): View
    {
        return view('frontend.services.index');
    }

    public function contact(): View
    {
        return view('frontend.contact');
    }
}
