<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class CommentTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_create_comment(): void
    {
        $post = $this->createPost();

        $this->post(route('posts.comments.store', $post), [
            'content' => 'Binh luan hop le',
        ])->assertRedirect(route('login'));

        $this->assertDatabaseCount('comments', 0);
    }

    public function test_authenticated_user_can_create_comment_for_published_post(): void
    {
        $user = User::factory()->create();
        $post = $this->createPost();

        $response = $this->actingAs($user)
            ->post(route('posts.comments.store', $post), [
                'content' => 'Binh luan dau tien',
            ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('comments', [
            'post_id' => $post->id,
            'user_id' => $user->id,
            'parent_id' => null,
            'content' => 'Binh luan dau tien',
        ]);

        $comment = Comment::query()->latest('id')->firstOrFail();
        $this->assertSame(
            route('blog.show', $post->slug) . '#comment-' . $comment->id,
            $response->headers->get('Location')
        );
    }

    public function test_authenticated_user_can_create_comment_without_page_reload(): void
    {
        $user = User::factory()->create();
        $post = $this->createPost();

        $response = $this->actingAs($user)
            ->postJson(route('posts.comments.store', $post), [
                'content' => 'Binh luan ajax',
            ]);

        $response
            ->assertOk()
            ->assertJsonPath('comments_count', 1)
            ->assertJsonPath('message', 'Bình luận đã được đăng.');

        $this->assertStringContainsString('Binh luan ajax', $response->json('html'));
        $this->assertDatabaseHas('comments', [
            'post_id' => $post->id,
            'user_id' => $user->id,
            'content' => 'Binh luan ajax',
        ]);
    }

    public function test_authenticated_user_can_create_reply_without_page_reload(): void
    {
        $user = User::factory()->create();
        $post = $this->createPost();
        $rootComment = $this->createComment([
            'post_id' => $post->id,
            'content' => 'Comment goc ajax',
        ]);

        $response = $this->actingAs($user)
            ->postJson(route('posts.comments.store', $post), [
                'parent_id' => $rootComment->id,
                'content' => 'Reply ajax',
                '_render_level' => 1,
                '_render_depth' => 1,
            ]);

        $response
            ->assertOk()
            ->assertJsonPath('comments_count', 2);

        $this->assertStringContainsString('Reply ajax', $response->json('html'));
        $this->assertStringContainsString('data-comment-node', $response->json('html'));
        $this->assertDatabaseHas('comments', [
            'post_id' => $post->id,
            'parent_id' => $rootComment->id,
            'user_id' => $user->id,
            'content' => 'Reply ajax',
        ]);
    }

    public function test_comment_content_cannot_contain_html(): void
    {
        $user = User::factory()->create();
        $post = $this->createPost();

        $this->actingAs($user)
            ->post(route('posts.comments.store', $post), [
                'content' => '<script>alert("xss")</script>',
            ])
            ->assertSessionHasErrors('content');

        $this->assertDatabaseCount('comments', 0);
    }

    public function test_user_cannot_update_or_delete_another_users_comment(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $comment = $this->createComment(['user_id' => $owner->id]);

        $this->actingAs($otherUser)
            ->patch(route('comments.update', $comment), [
                'content' => 'Noi dung bi sua',
            ])
            ->assertForbidden();

        $this->actingAs($otherUser)
            ->delete(route('comments.destroy', $comment))
            ->assertForbidden();

        $this->assertSame('Binh luan hien co', $comment->refresh()->content);
        $this->assertNull($comment->deleted_at);
    }

    public function test_comment_owner_can_update_and_soft_delete_comment(): void
    {
        $owner = User::factory()->create();
        $comment = $this->createComment(['user_id' => $owner->id]);

        $this->actingAs($owner)
            ->patch(route('comments.update', $comment), [
                'content' => 'Noi dung da cap nhat',
            ])
            ->assertRedirect(route('blog.show', $comment->post->slug) . '#comment-' . $comment->id);

        $this->assertDatabaseHas('comments', [
            'id' => $comment->id,
            'content' => 'Noi dung da cap nhat',
        ]);
        $this->assertNotNull($comment->refresh()->edited_at);

        $this->actingAs($owner)
            ->delete(route('comments.destroy', $comment))
            ->assertRedirect(route('blog.show', $comment->post->slug) . '#comments');

        $this->assertSoftDeleted('comments', ['id' => $comment->id]);
    }

    public function test_comment_owner_can_update_comment_without_page_reload(): void
    {
        $owner = User::factory()->create();
        $comment = $this->createComment(['user_id' => $owner->id]);

        $this->actingAs($owner)
            ->patchJson(route('comments.update', $comment), [
                'content' => 'Noi dung ajax da cap nhat',
            ])
            ->assertOk()
            ->assertJsonPath('comment_id', $comment->id)
            ->assertJsonPath('content', 'Noi dung ajax da cap nhat');

        $this->assertDatabaseHas('comments', [
            'id' => $comment->id,
            'content' => 'Noi dung ajax da cap nhat',
        ]);
        $this->assertNotNull($comment->refresh()->edited_at);
    }

    public function test_comment_owner_can_delete_comment_without_page_reload(): void
    {
        $owner = User::factory()->create();
        $comment = $this->createComment(['user_id' => $owner->id]);

        $this->actingAs($owner)
            ->deleteJson(route('comments.destroy', $comment))
            ->assertOk()
            ->assertJsonPath('comment_id', $comment->id)
            ->assertJsonPath('comments_count', 0);

        $this->assertSoftDeleted('comments', ['id' => $comment->id]);
    }

    public function test_admin_can_update_and_delete_any_comment(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $comment = $this->createComment();

        $this->actingAs($admin)
            ->patch(route('comments.update', $comment), [
                'content' => 'Admin da sua',
            ])
            ->assertRedirect(route('blog.show', $comment->post->slug) . '#comment-' . $comment->id);

        $this->assertSame('Admin da sua', $comment->refresh()->content);

        $this->actingAs($admin)
            ->delete(route('comments.destroy', $comment))
            ->assertRedirect(route('blog.show', $comment->post->slug) . '#comments');

        $this->assertSoftDeleted('comments', ['id' => $comment->id]);
    }

    public function test_reply_is_stored_and_displayed_under_root_comment(): void
    {
        $user = User::factory()->create();
        $post = $this->createPost();
        $rootComment = $this->createComment([
            'post_id' => $post->id,
            'content' => 'Comment goc',
        ]);

        $response = $this->actingAs($user)
            ->post(route('posts.comments.store', $post), [
                'parent_id' => $rootComment->id,
                'content' => 'Reply mot',
            ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('comments', [
            'post_id' => $post->id,
            'parent_id' => $rootComment->id,
            'user_id' => $user->id,
            'content' => 'Reply mot',
        ]);

        $reply = Comment::query()->where('content', 'Reply mot')->firstOrFail();
        $this->assertSame(
            route('blog.show', $post->slug) . '#comment-' . $reply->id,
            $response->headers->get('Location')
        );

        $this->get(route('blog.show', $post->slug))
            ->assertOk()
            ->assertSee('Comment goc')
            ->assertSee('Reply mot');
    }

    public function test_reply_to_reply_is_stored_as_nested_reply(): void
    {
        $user = User::factory()->create();
        $post = $this->createPost();
        $rootComment = $this->createComment(['post_id' => $post->id]);
        $reply = $this->createComment([
            'post_id' => $post->id,
            'parent_id' => $rootComment->id,
        ]);

        $response = $this->actingAs($user)
            ->post(route('posts.comments.store', $post), [
                'parent_id' => $reply->id,
                'content' => 'Reply qua sau',
            ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('comments', [
            'post_id' => $post->id,
            'parent_id' => $reply->id,
            'user_id' => $user->id,
            'content' => 'Reply qua sau',
        ]);

        $nestedReply = Comment::query()->where('content', 'Reply qua sau')->firstOrFail();
        $this->assertSame(
            route('blog.show', $post->slug) . '#comment-' . $nestedReply->id,
            $response->headers->get('Location')
        );
    }

    public function test_existing_comment_html_is_escaped_on_blog_show(): void
    {
        $post = $this->createPost();

        $this->createComment([
            'post_id' => $post->id,
            'content' => '<strong>Injected</strong>',
        ]);

        $this->get(route('blog.show', $post->slug))
            ->assertOk()
            ->assertSee(e('<strong>Injected</strong>'), false)
            ->assertDontSee('<strong>Injected</strong>', false);
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
