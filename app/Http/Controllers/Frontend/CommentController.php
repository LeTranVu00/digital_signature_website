<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCommentRequest;
use App\Http\Requests\UpdateCommentRequest;
use App\Models\Comment;
use App\Models\Post;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Collection;

class CommentController extends Controller
{
    public function store(StoreCommentRequest $request, Post $post): JsonResponse|RedirectResponse
    {
        abort_unless($post->status === 'published'
            && $post->published_at !== null
            && $post->published_at->lte(now()), 404);

        $validated = $request->validated();

        $comment = $post->comments()->create([
            'user_id' => $request->user()->getKey(),
            'parent_id' => $validated['parent_id'] ?? null,
            'content' => $validated['content'],
        ]);

        if ($request->expectsJson()) {
            $renderLevel = min(max((int) $request->input('_render_level', 0), 0), 2);
            $renderDepth = min(max((int) $request->input('_render_depth', 0), 0), 2);
            $appendTargetId = $request->integer('_append_target_id') ?: null;
            $countTargetIds = json_decode((string) $request->input('_count_target_ids', '[]'), true);

            $comment->load('user');
            $comment->loadCount(['likes', 'dislikes']);
            $comment->setRelation('votes', new Collection());
            $comment->setRelation('replies', new Collection());

            return response()->json([
                'comment_id' => $comment->getKey(),
                'comments_count' => Comment::query()
                    ->where('post_id', $post->getKey())
                    ->count(),
                'html' => view('frontend.components.comment-thread', [
                    'comment' => $comment,
                    'post' => $post,
                    'level' => $renderLevel,
                    'depth' => $renderDepth,
                    'appendTargetId' => $appendTargetId,
                    'countTargetIds' => is_array($countTargetIds) ? $countTargetIds : [],
                    'openCommentIds' => [],
                ])->render(),
                'message' => 'Bình luận đã được đăng.',
            ]);
        }

        return redirect()
            ->to(route('blog.show', $post->slug) . '#comment-' . $comment->getKey())
            ->with('focus_comment_id', $comment->getKey())
            ->with('status', 'Bình luận đã được đăng.');
    }

    public function update(UpdateCommentRequest $request, Comment $comment): JsonResponse|RedirectResponse
    {
        $this->authorize('update', $comment);

        $comment->update([
            'content' => $request->validated('content'),
            'edited_at' => now(),
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'comment_id' => $comment->getKey(),
                'content' => $comment->content,
                'message' => 'Bình luận đã được cập nhật.',
            ]);
        }

        return redirect()
            ->to(route('blog.show', $comment->post->slug) . '#comment-' . $comment->getKey())
            ->with('focus_comment_id', $comment->getKey())
            ->with('status', 'Bình luận đã được cập nhật.');
    }

    public function destroy(Comment $comment): JsonResponse|RedirectResponse
    {
        $this->authorize('delete', $comment);

        $postSlug = $comment->post->slug;
        $parentId = $comment->parent_id;

        $comment->delete();

        if (request()->expectsJson()) {
            return response()->json([
                'comment_id' => $comment->getKey(),
                'parent_id' => $parentId,
                'comments_count' => Comment::query()
                    ->where('post_id', $comment->post_id)
                    ->count(),
                'message' => 'Bình luận đã được xóa.',
            ]);
        }

        $fragment = $parentId ? '#comment-' . $parentId : '#comments';

        $redirect = redirect()
            ->to(route('blog.show', $postSlug) . $fragment)
            ->with('status', 'Bình luận đã được xóa.');

        if ($parentId) {
            $redirect->with('focus_comment_id', $parentId);
        }

        return $redirect;
    }
}
