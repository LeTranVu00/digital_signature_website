<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\Post;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CommentController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'post_id' => ['nullable', 'integer', 'exists:posts,id'],
            'deleted' => ['nullable', 'in:all,active,trashed'],
        ]);

        $comments = Comment::query()
            ->withTrashed()
            ->with([
                'user',
                'post' => function ($query) {
                    $query->withTrashed();
                },
                'parent' => function ($query) {
                    $query->withTrashed()->with('user');
                },
            ])
            ->withCount([
                'replies' => function ($query) {
                    $query->withTrashed();
                },
            ])
            ->when($filters['search'] ?? null, function ($query, string $search) {
                $query->where('content', 'like', '%'.$search.'%');
            })
            ->when($filters['post_id'] ?? null, function ($query, string $postId) {
                $query->where('post_id', $postId);
            })
            ->when(($filters['deleted'] ?? 'all') === 'active', function ($query) {
                $query->whereNull('deleted_at');
            })
            ->when(($filters['deleted'] ?? 'all') === 'trashed', function ($query) {
                $query->onlyTrashed();
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $posts = Post::query()
            ->withTrashed()
            ->whereHas('comments', function ($query) {
                $query->withTrashed();
            })
            ->orderBy('title')
            ->get();

        return view('admin.comments.index', compact(
            'comments',
            'filters',
            'posts'
        ));
    }

    public function destroy(Comment $comment): RedirectResponse
    {
        $comment->delete();

        return redirect()
            ->route('admin.comments.index')
            ->with('success', 'Da chuyen binh luan vao thung rac.');
    }

    public function restore(int $trashedComment): RedirectResponse
    {
        $comment = Comment::onlyTrashed()->findOrFail($trashedComment);

        $comment->restore();

        return redirect()
            ->route('admin.comments.index', ['deleted' => 'trashed'])
            ->with('success', 'Khoi phuc binh luan thanh cong.');
    }

    public function forceDelete(int $trashedComment): RedirectResponse
    {
        $comment = Comment::onlyTrashed()->findOrFail($trashedComment);

        $comment->forceDelete();

        return redirect()
            ->route('admin.comments.index', ['deleted' => 'trashed'])
            ->with('success', 'Da xoa vinh vien binh luan.');
    }
}
