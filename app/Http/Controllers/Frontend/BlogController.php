<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Comment;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class BlogController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
        ]);

        $posts = Post::query()
            ->published()
            ->with(['category', 'user'])
            ->search($filters['search'] ?? null)
            ->latest('published_at')
            ->paginate(9)
            ->withQueryString();

        $categories = Category::query()
            ->orderBy('name')
            ->get();

        return view('frontend.blog.index', compact('categories', 'posts'));
    }

    public function show(Request $request, string $post): View
    {
        $userId = $request->user()?->getKey();

        $post = Post::query()
            ->published()
            ->with([
                'category',
                'user',
            ])
            ->where('slug', $post)
            ->firstOrFail();

        $comments = Comment::query()
            ->where('post_id', $post->getKey())
            ->with('user')
            ->withCount(['likes', 'dislikes'])
            ->with([
                'votes' => function ($query) use ($userId) {
                    $query->where('user_id', $userId ?? 0);
                },
            ])
            ->oldest()
            ->get();

        $commentsCount = $comments->count();
        $openCommentIds = $this->openCommentIds($comments, session('focus_comment_id'));
        $post->setRelation('comments', $this->buildCommentTree($comments));

        $this->recordView($request, $post);

        $relatedPosts = Post::query()
            ->published()
            ->with(['category', 'user'])
            ->where('category_id', $post->category_id)
            ->whereKeyNot($post->getKey())
            ->latest('published_at')
            ->limit(3)
            ->get();

        return view('frontend.blog.show', compact(
            'commentsCount',
            'openCommentIds',
            'post',
            'relatedPosts'
        ));
    }

    public function category(Category $category): View
    {
        $posts = $category->posts()
            ->published()
            ->with(['category', 'user'])
            ->latest('published_at')
            ->paginate(9);

        $categories = Category::query()
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

    /**
     * @param Collection<int, Comment> $comments
     * @return Collection<int, Comment>
     */
    private function buildCommentTree(Collection $comments, ?int $parentId = null): Collection
    {
        return $comments
            ->where('parent_id', $parentId)
            ->values()
            ->map(function (Comment $comment) use ($comments) {
                $comment->setRelation(
                    'replies',
                    $this->buildCommentTree($comments, $comment->getKey())
                );

                return $comment;
            });
    }

    /**
     * @param Collection<int, Comment> $comments
     * @return array<int, int>
     */
    private function openCommentIds(Collection $comments, mixed $focusCommentId): array
    {
        if (! $focusCommentId) {
            return [];
        }

        $byId = $comments->keyBy('id');
        $comment = $byId->get((int) $focusCommentId);
        $ids = [];

        while ($comment && $comment->parent_id) {
            $ids[] = (int) $comment->parent_id;
            $comment = $byId->get((int) $comment->parent_id);
        }

        return $ids;
    }
}
