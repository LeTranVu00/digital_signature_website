<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class AdminCategoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_category(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->post(route('admin.categories.store'), [
                'name' => 'Chu ky so',
                'description' => 'Danh muc ve chu ky so.',
            ])
            ->assertRedirect(route('admin.categories.index'));

        $this->assertDatabaseHas('categories', [
            'name' => 'Chu ky so',
            'slug' => 'chu-ky-so',
            'description' => 'Danh muc ve chu ky so.',
        ]);
    }

    public function test_admin_can_update_category(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $category = Category::query()->create([
            'name' => 'Tin cu',
            'slug' => 'tin-cu',
            'description' => 'Mo ta cu',
        ]);

        $this->actingAs($admin)
            ->put(route('admin.categories.update', $category), [
                'name' => 'Tin moi',
                'description' => 'Mo ta moi',
            ])
            ->assertRedirect(route('admin.categories.index'));

        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'name' => 'Tin moi',
            'slug' => 'tin-moi',
            'description' => 'Mo ta moi',
        ]);
    }

    public function test_admin_can_delete_empty_category(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $category = Category::query()->create([
            'name' => 'Danh muc rong',
            'slug' => 'danh-muc-rong',
        ]);

        $this->actingAs($admin)
            ->delete(route('admin.categories.destroy', $category))
            ->assertRedirect(route('admin.categories.index'));

        $this->assertDatabaseMissing('categories', [
            'id' => $category->id,
        ]);
    }

    public function test_admin_cannot_delete_category_that_has_posts(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $category = Category::query()->create([
            'name' => 'Danh muc co bai',
            'slug' => 'danh-muc-co-bai',
        ]);

        $this->createPost(['category_id' => $category->id]);

        $this->actingAs($admin)
            ->delete(route('admin.categories.destroy', $category))
            ->assertRedirect(route('admin.categories.index'))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
        ]);
    }

    public function test_regular_user_cannot_manage_categories(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        $this->actingAs($user)
            ->get(route('admin.categories.index'))
            ->assertForbidden();

        $this->actingAs($user)
            ->post(route('admin.categories.store'), [
                'name' => 'Khong duoc tao',
            ])
            ->assertForbidden();
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
            'title' => 'Bai viet trong danh muc',
            'slug' => 'bai-viet-trong-danh-muc-'.Str::random(8),
            'summary' => 'Tom tat',
            'content' => '<p>Noi dung</p>',
            'status' => 'published',
            'views' => 0,
            'published_at' => now()->subDay(),
        ], $overrides));
    }
}
