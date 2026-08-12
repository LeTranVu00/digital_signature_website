<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Comment;
use App\Models\CommentVote;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class CommentVoteTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_required_to_login_before_voting(): void
    {
        $comment = $this->createComment();

        $this->postJson(route('comments.vote', $comment), [
            'vote' => 1,
        ])->assertUnauthorized();

        $this->assertDatabaseCount('comment_votes', 0);
    }

    public function test_user_can_like_comment_once_without_duplicate_votes(): void
    {
        $user = User::factory()->create();
        $comment = $this->createComment();

        $this->actingAs($user)
            ->postJson(route('comments.vote', $comment), [
                'vote' => 1,
            ])
            ->assertOk()
            ->assertJson([
                'likes' => 1,
                'dislikes' => 0,
                'user_vote' => 1,
            ]);

        $this->assertDatabaseHas('comment_votes', [
            'comment_id' => $comment->id,
            'user_id' => $user->id,
            'vote' => 1,
        ]);
        $this->assertSame(1, CommentVote::query()->count());
    }

    public function test_second_like_removes_existing_like(): void
    {
        $user = User::factory()->create();
        $comment = $this->createComment();

        $this->actingAs($user)
            ->postJson(route('comments.vote', $comment), ['vote' => 1])
            ->assertOk();

        $this->actingAs($user)
            ->postJson(route('comments.vote', $comment), ['vote' => 1])
            ->assertOk()
            ->assertJson([
                'likes' => 0,
                'dislikes' => 0,
                'user_vote' => 0,
            ]);

        $this->assertDatabaseCount('comment_votes', 0);
    }

    public function test_like_then_dislike_changes_existing_vote(): void
    {
        $user = User::factory()->create();
        $comment = $this->createComment();

        $this->actingAs($user)
            ->postJson(route('comments.vote', $comment), ['vote' => 1])
            ->assertOk();

        $this->actingAs($user)
            ->postJson(route('comments.vote', $comment), ['vote' => -1])
            ->assertOk()
            ->assertJson([
                'likes' => 0,
                'dislikes' => 1,
                'user_vote' => -1,
            ]);

        $this->assertDatabaseHas('comment_votes', [
            'comment_id' => $comment->id,
            'user_id' => $user->id,
            'vote' => -1,
        ]);
        $this->assertSame(1, CommentVote::query()->count());
    }

    public function test_vote_response_counts_other_users_votes(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $comment = $this->createComment();

        CommentVote::query()->create([
            'comment_id' => $comment->id,
            'user_id' => $otherUser->id,
            'vote' => -1,
        ]);

        $this->actingAs($user)
            ->postJson(route('comments.vote', $comment), ['vote' => 1])
            ->assertOk()
            ->assertJson([
                'likes' => 1,
                'dislikes' => 1,
                'user_vote' => 1,
            ]);
    }

    public function test_invalid_vote_value_is_rejected(): void
    {
        $user = User::factory()->create();
        $comment = $this->createComment();

        $this->actingAs($user)
            ->postJson(route('comments.vote', $comment), ['vote' => 2])
            ->assertUnprocessable();

        $this->assertDatabaseCount('comment_votes', 0);
    }

    private function createComment(array $overrides = []): Comment
    {
        $post = isset($overrides['post_id'])
            ? Post::query()->findOrFail($overrides['post_id'])
            : $this->createPost();

        $userId = $overrides['user_id']
            ?? User::factory()->create()->id;

        return Comment::query()->create(array_merge([
            'post_id' => $post->id,
            'user_id' => $userId,
            'parent_id' => null,
            'content' => 'Binh luan hien co',
        ], $overrides));
    }

    private function createPost(array $overrides = []): Post
    {
        $categoryId = $overrides['category_id']
            ?? Category::query()->create([
                'name' => 'Tin tuc',
                'slug' => 'tin-tuc-'.Str::random(8),
            ])->id;

        $userId = $overrides['user_id']
            ?? User::factory()->create()->id;

        return Post::query()->create(array_merge([
            'user_id' => $userId,
            'category_id' => $categoryId,
            'title' => 'Bai viet cong khai',
            'slug' => 'bai-viet-cong-khai-'.Str::random(8),
            'summary' => 'Tom tat bai viet',
            'content' => '<p>Noi dung bai viet</p>',
            'thumbnail' => null,
            'status' => 'published',
            'views' => 0,
            'published_at' => now()->subDay(),
        ], $overrides));
    }
}
