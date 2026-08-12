<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class FrontendBlogTest extends TestCase
{
    use RefreshDatabase;

    public function test_blog_only_shows_published_posts(): void
    {
        $publishedPost = $this->createPost([
            'title' => 'Bài viết đã xuất bản',
            'slug' => 'bai-viet-da-xuat-ban',
            'status' => 'published',
            'published_at' => now()->subDay(),
        ]);

        $draftPost = $this->createPost([
            'title' => 'Bài viết nháp',
            'slug' => 'bai-viet-nhap',
            'status' => 'draft',
            'published_at' => null,
            'category_id' => $publishedPost->category_id,
            'user_id' => $publishedPost->user_id,
        ]);

        $response = $this->get(route('blog.index'));

        $response
            ->assertOk()
            ->assertSee($publishedPost->title)
            ->assertDontSee($draftPost->title);
    }

    public function test_draft_post_is_not_accessible_publicly(): void
    {
        $draftPost = $this->createPost([
            'status' => 'draft',
            'published_at' => null,
        ]);

        $this->get(route('blog.show', $draftPost->slug))
            ->assertNotFound();
    }

    public function test_blog_show_counts_one_view_per_session_window(): void
    {
        $post = $this->createPost([
            'status' => 'published',
            'published_at' => now()->subDay(),
            'views' => 0,
        ]);

        $this->get(route('blog.show', $post->slug))
            ->assertOk();

        $this->get(route('blog.show', $post->slug))
            ->assertOk();

        $this->assertSame(1, $post->refresh()->views);
    }

    public function test_category_page_only_shows_published_posts(): void
    {
        $category = Category::query()->create([
            'name' => 'Chữ ký số',
            'slug' => 'chu-ky-so',
        ]);

        $publishedPost = $this->createPost([
            'title' => 'Bài trong danh mục',
            'slug' => 'bai-trong-danh-muc',
            'category_id' => $category->id,
            'status' => 'published',
            'published_at' => now()->subDay(),
        ]);

        $draftPost = $this->createPost([
            'title' => 'Bài nháp trong danh mục',
            'slug' => 'bai-nhap-trong-danh-muc',
            'category_id' => $category->id,
            'status' => 'draft',
            'published_at' => null,
            'user_id' => $publishedPost->user_id,
        ]);

        $response = $this->get(route('blog.category', $category->slug));

        $response
            ->assertOk()
            ->assertSee($publishedPost->title)
            ->assertDontSee($draftPost->title);
    }

    private function createPost(array $overrides = []): Post
    {
        $categoryId = $overrides['category_id']
            ?? Category::query()->create([
                'name' => 'Tin tức',
                'slug' => 'tin-tuc-'.Str::random(8),
            ])->id;

        $userId = $overrides['user_id']
            ?? User::factory()->create()->id;

        return Post::query()->create(array_merge([
            'user_id' => $userId,
            'category_id' => $categoryId,
            'title' => 'Bài viết công khai',
            'slug' => 'bai-viet-cong-khai-'.Str::random(8),
            'summary' => 'Tóm tắt bài viết',
            'content' => '<p>Nội dung bài viết</p>',
            'thumbnail' => null,
            'status' => 'published',
            'views' => 0,
            'published_at' => now()->subDay(),
        ], $overrides));
    }
}
