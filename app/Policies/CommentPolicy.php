<?php

namespace App\Policies;

use App\Models\Comment;
use App\Models\User;

class CommentPolicy
{
    public function update(User $user, Comment $comment): bool
    {
        return $user->isAdmin() || $user->is($comment->user);
    }

    public function delete(User $user, Comment $comment): bool
    {
        return $user->isAdmin() || $user->is($comment->user);
    }

    public function moderate(User $user): bool
    {
        return $user->isAdmin();
    }
}
