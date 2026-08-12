<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class AdminCommentTest extends TestCase
{
    use RefreshDatabase;

    public function test_regular_user_cannot_access_admin_comments(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        $this->actingAs($user)
            ->get(route('admin.comments.index'))
            ->assertForbidden();
    }

    public function test_admin_can_see_comments_with_user_post_parent_and_child_info(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $post = $this->createPost(['title' => 'Bai viet co binh luan']);
        $rootComment = $this->createComment([
            'post_id' => $post->id,
            'content' => 'Comment goc can quan ly',
        ]);
        $reply = $this->createComment([
            'post_id' => $post->id,
            'parent_id' => $rootComment->id,
            'content' => 'Reply can quan ly',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.comments.index'))
            ->assertOk()
            ->assertSee('Comment goc can quan ly')
            ->assertSee('Reply can quan ly')
            ->assertSee('Bai viet co binh luan')
            ->assertSee($rootComment->user->name)
            ->assertSee($reply->user->name)
            ->assertSee(route('admin.posts.preview', $post))
            ->assertSee('1 reply');
    }

    public function test_admin_can_search_and_filter_comments_by_post_and_deleted_status(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $matchedPost = $this->createPost(['title' => 'Bai viet dung']);
        $otherPost = $this->createPost(['title' => 'Bai viet khac']);

        $matchedComment = $this->createComment([
            'post_id' => $matchedPost->id,
            'content' => 'Noi dung dac biet',
        ]);
        $this->createComment([
            'post_id' => $otherPost->id,
            'content' => 'Noi dung khac',
        ]);
        $deletedComment = $this->createComment([
            'post_id' => $matchedPost->id,
            'content' => 'Binh luan da xoa',
        ]);
        $deletedComment->delete();

        $this->actingAs($admin)
            ->get(route('admin.comments.index', [
                'search' => 'dac biet',
                'post_id' => $matchedPost->id,
                'deleted' => 'active',
            ]))
            ->assertOk()
            ->assertSee($matchedComment->content)
            ->assertDontSee('Noi dung khac')
            ->assertDontSee('Binh luan da xoa');

        $this->actingAs($admin)
            ->get(route('admin.comments.index', [
                'deleted' => 'trashed',
            ]))
            ->assertOk()
            ->assertSee('Binh luan da xoa')
            ->assertDontSee($matchedComment->content);
    }

    public function test_admin_can_soft_delete_restore_and_force_delete_comment(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $comment = $this->createComment();

        $this->actingAs($admin)
            ->delete(route('admin.comments.destroy', $comment))
            ->assertRedirect(route('admin.comments.index'));

        $this->assertSoftDeleted('comments', ['id' => $comment->id]);

        $this->actingAs($admin)
            ->patch(route('admin.comments.restore', [
                'trashedComment' => $comment->id,
            ]))
            ->assertRedirect(route('admin.comments.index', ['deleted' => 'trashed']));

        $this->assertDatabaseHas('comments', [
            'id' => $comment->id,
            'deleted_at' => null,
        ]);

        $comment->delete();

        $this->actingAs($admin)
            ->delete(route('admin.comments.force-delete', [
                'trashedComment' => $comment->id,
            ]))
            ->assertRedirect(route('admin.comments.index', ['deleted' => 'trashed']));

        $this->assertDatabaseMissing('comments', ['id' => $comment->id]);
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
