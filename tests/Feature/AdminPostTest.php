<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class AdminPostTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_draft_post(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $category = $this->createCategory();

        $this->actingAs($admin)
            ->post(route('admin.posts.store'), [
                'category_id' => $category->id,
                'title' => 'Bai nhap moi',
                'summary' => 'Tom tat bai nhap',
                'content' => '<p>Noi dung bai nhap</p>',
                'status' => 'draft',
            ])
            ->assertRedirect(route('admin.posts.index'));

        $this->assertDatabaseHas('posts', [
            'user_id' => $admin->id,
            'category_id' => $category->id,
            'title' => 'Bai nhap moi',
            'slug' => 'bai-nhap-moi',
            'status' => 'draft',
            'published_at' => null,
        ]);
    }

    public function test_admin_can_publish_post(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $category = $this->createCategory();

        $this->actingAs($admin)
            ->post(route('admin.posts.store'), [
                'category_id' => $category->id,
                'title' => 'Bai xuat ban moi',
                'summary' => 'Tom tat bai xuat ban',
                'content' => '<p>Noi dung bai xuat ban</p>',
                'status' => 'published',
            ])
            ->assertRedirect(route('admin.posts.index'));

        $post = Post::query()
            ->where('title', 'Bai xuat ban moi')
            ->firstOrFail();

        $this->assertSame('published', $post->status);
        $this->assertNotNull($post->published_at);
    }

    public function test_admin_can_update_draft_to_published_post(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $post = $this->createPost([
            'user_id' => $admin->id,
            'status' => 'draft',
            'published_at' => null,
        ]);

        $this->actingAs($admin)
            ->put(route('admin.posts.update', $post), [
                'category_id' => $post->category_id,
                'title' => 'Bai da xuat ban',
                'summary' => 'Tom tat moi',
                'content' => '<p>Noi dung moi</p>',
                'status' => 'published',
            ])
            ->assertRedirect(route('admin.posts.index'));

        $post->refresh();

        $this->assertSame('published', $post->status);
        $this->assertSame('bai-da-xuat-ban', $post->slug);
        $this->assertNotNull($post->published_at);
    }

    public function test_regular_user_cannot_create_post(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        $category = $this->createCategory();

        $this->actingAs($user)
            ->post(route('admin.posts.store'), [
                'category_id' => $category->id,
                'title' => 'User khong duoc tao',
                'summary' => 'Tom tat',
                'content' => '<p>Noi dung</p>',
                'status' => 'draft',
            ])
            ->assertForbidden();

        $this->assertDatabaseMissing('posts', [
            'title' => 'User khong duoc tao',
        ]);
    }

    public function test_draft_post_does_not_appear_on_frontend(): void
    {
        $draftPost = $this->createPost([
            'title' => 'Bai nhap an frontend',
            'slug' => 'bai-nhap-an-frontend',
            'status' => 'draft',
            'published_at' => null,
        ]);

        $this->get(route('blog.index'))
            ->assertOk()
            ->assertDontSee($draftPost->title);

        $this->get(route('blog.show', $draftPost->slug))
            ->assertNotFound();
    }

    public function test_published_post_appears_on_frontend(): void
    {
        $publishedPost = $this->createPost([
            'title' => 'Bai publish hien frontend',
            'slug' => 'bai-publish-hien-frontend',
            'status' => 'published',
            'published_at' => now()->subDay(),
        ]);

        $this->get(route('blog.index'))
            ->assertOk()
            ->assertSee($publishedPost->title);

        $this->get(route('blog.show', $publishedPost->slug))
            ->assertOk()
            ->assertSee($publishedPost->title);
    }

    public function test_admin_can_soft_delete_and_restore_post(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $post = $this->createPost(['user_id' => $admin->id]);

        $this->actingAs($admin)
            ->delete(route('admin.posts.destroy', $post))
            ->assertRedirect(route('admin.posts.index'));

        $this->assertSoftDeleted('posts', ['id' => $post->id]);

        $this->actingAs($admin)
            ->get(route('admin.posts.trash'))
            ->assertOk()
            ->assertSee($post->title);

        $this->actingAs($admin)
            ->patch(route('admin.posts.restore', ['trashedPost' => $post->id]))
            ->assertRedirect(route('admin.posts.trash'));

        $this->assertDatabaseHas('posts', [
            'id' => $post->id,
            'deleted_at' => null,
        ]);
    }

    private function createCategory(array $overrides = []): Category
    {
        return Category::query()->create(array_merge([
            'name' => 'Tin tuc '.Str::random(8),
            'slug' => 'tin-tuc-'.Str::random(8),
            'description' => null,
        ], $overrides));
    }

    private function createPost(array $overrides = []): Post
    {
        $categoryId = $overrides['category_id']
            ?? $this->createCategory()->id;

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
