<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class AdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_regular_user_cannot_access_admin_dashboard(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        $this->actingAs($user)
            ->get(route('admin.dashboard'))
            ->assertForbidden();
    }

    public function test_admin_dashboard_shows_stats_lists_and_monthly_chart(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $author = User::factory()->create(['role' => 'user']);
        $commenter = User::factory()->create(['role' => 'user']);

        $popularPost = $this->createPost([
            'user_id' => $author->id,
            'title' => 'Popular Post',
            'slug' => 'popular-post',
            'status' => 'published',
            'views' => 20,
            'created_at' => now()->subMonth(),
        ]);
        $this->createPost([
            'user_id' => $author->id,
            'title' => 'Published Post',
            'slug' => 'published-post',
            'status' => 'published',
            'views' => 10,
            'created_at' => now(),
        ]);
        $this->createPost([
            'user_id' => $author->id,
            'title' => 'Draft Post',
            'slug' => 'draft-post',
            'status' => 'draft',
            'views' => 1,
            'published_at' => null,
            'created_at' => now(),
        ]);
        $trashedPost = $this->createPost([
            'user_id' => $author->id,
            'title' => 'Trashed Post',
            'slug' => 'trashed-post',
            'status' => 'draft',
            'views' => 99,
            'published_at' => null,
        ]);
        $trashedPost->delete();

        Comment::query()->create([
            'post_id' => $popularPost->id,
            'user_id' => $commenter->id,
            'content' => 'Newest dashboard comment',
        ]);
        Comment::query()->create([
            'post_id' => $popularPost->id,
            'user_id' => $commenter->id,
            'content' => 'Second dashboard comment',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Dashboard quản trị')
            ->assertSee('Tổng bài viết')
            ->assertSee('Đã xuất bản')
            ->assertSee('Bản nháp')
            ->assertSee('Trong thùng rác')
            ->assertSee('Tổng user')
            ->assertSee('Tổng bình luận')
            ->assertSee('Lượt xem bài viết')
            ->assertSee('31')
            ->assertSee('Popular Post')
            ->assertSee('Published Post')
            ->assertSee('Draft Post')
            ->assertSee('Newest dashboard comment')
            ->assertSee('Top bài theo views')
            ->assertSee(now()->format('m/Y'));
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
