<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BlogController extends Controller
{
    public function index(): View
    {
        $posts = Post::query()
            ->published()
            ->with(['category', 'user'])
            ->latest('published_at')
            ->paginate(9);

        $categories = Category::query()
            ->whereHas('posts', function ($query) {
                $query->published();
            })
            ->orderBy('name')
            ->get();

        return view('frontend.blog.index', compact('categories', 'posts'));
    }

    public function show(Request $request, string $post): View
    {
        $post = Post::query()
            ->published()
            ->with(['category', 'user'])
            ->where('slug', $post)
            ->firstOrFail();

        $this->recordView($request, $post);

        $relatedPosts = Post::query()
            ->published()
            ->with(['category', 'user'])
            ->where('category_id', $post->category_id)
            ->whereKeyNot($post->getKey())
            ->latest('published_at')
            ->limit(3)
            ->get();

        return view('frontend.blog.show', compact('post', 'relatedPosts'));
    }

    public function category(Category $category): View
    {
        $posts = $category->posts()
            ->published()
            ->with(['category', 'user'])
            ->latest('published_at')
            ->paginate(9);

        $categories = Category::query()
            ->whereHas('posts', function ($query) {
                $query->published();
            })
            ->orderBy('name')
            ->get();

        return view('frontend.blog.category', compact(
            'categories',
            'category',
            'posts'
        ));
    }

    private function recordView(Request $request, Post $post): void
    {
        $key = "post_viewed_at.{$post->getKey()}";
        $viewedAt = $request->session()->get($key);

        if ($viewedAt && now()->diffInMinutes($viewedAt) < 60) {
            return;
        }

        $post->increment('views');
        $request->session()->put($key, now());
    }
}
