<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CommentVoteController extends Controller
{
    public function store(Request $request, Comment $comment): JsonResponse
    {
        $validated = $request->validate([
            'vote' => ['required', 'integer', 'in:1,-1'],
        ]);

        $voteValue = (int) $validated['vote'];
        $vote = $comment->votes()
            ->where('user_id', $request->user()->getKey())
            ->first();

        if (! $vote) {
            $comment->votes()->create([
                'user_id' => $request->user()->getKey(),
                'vote' => $voteValue,
            ]);
        } elseif ($vote->vote === $voteValue) {
            $vote->delete();
            $voteValue = 0;
        } else {
            $vote->update(['vote' => $voteValue]);
        }

        return response()->json([
            'likes' => $comment->likes()->count(),
            'dislikes' => $comment->dislikes()->count(),
            'user_vote' => $voteValue,
        ]);
    }
}
