<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class AdminUserTest extends TestCase
{
    use RefreshDatabase;

    public function test_regular_user_cannot_access_admin_users(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        $this->actingAs($user)
            ->get(route('admin.users.index'))
            ->assertForbidden();
    }

    public function test_admin_can_see_users_with_registration_date_and_comment_count(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create([
            'name' => 'Nguyen Van Test',
            'email' => 'commenter@example.com',
            'role' => 'user',
        ]);

        $this->createComment(['user_id' => $user->id]);
        $deletedComment = $this->createComment(['user_id' => $user->id]);
        $deletedComment->delete();

        $this->actingAs($admin)
            ->get(route('admin.users.index'))
            ->assertOk()
            ->assertSee('Nguyen Van Test')
            ->assertSee('commenter@example.com')
            ->assertSee('User')
            ->assertSee('Active')
            ->assertSee($user->created_at->format('d/m/Y H:i'))
            ->assertSee('2');
    }

    public function test_admin_can_search_and_filter_users_by_role_and_status(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $matchedUser = User::factory()->create([
            'name' => 'Filtered User',
            'email' => 'filtered@example.com',
            'role' => 'user',
            'status' => 'blocked',
        ]);
        User::factory()->create([
            'name' => 'Other Admin',
            'email' => 'other-admin@example.com',
            'role' => 'admin',
            'status' => 'active',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.users.index', [
                'search' => 'filtered',
                'role' => 'user',
                'status' => 'blocked',
            ]))
            ->assertOk()
            ->assertSee($matchedUser->email)
            ->assertDontSee('other-admin@example.com');
    }

    public function test_admin_can_promote_and_demote_other_users(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create(['role' => 'user']);

        $this->actingAs($admin)
            ->patch(route('admin.users.role', $user), [
                'role' => 'admin',
            ])
            ->assertRedirect(route('admin.users.index'));

        $this->assertSame('admin', $user->refresh()->role);

        $this->actingAs($admin)
            ->patch(route('admin.users.role', $user), [
                'role' => 'user',
            ])
            ->assertRedirect(route('admin.users.index'));

        $this->assertSame('user', $user->refresh()->role);
    }

    public function test_admin_cannot_demote_themselves(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->patch(route('admin.users.role', $admin), [
                'role' => 'user',
            ])
            ->assertForbidden();

        $this->assertSame('admin', $admin->refresh()->role);
    }

    public function test_admin_can_block_and_unblock_user(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create(['status' => 'active']);

        $this->actingAs($admin)
            ->patch(route('admin.users.status', $user), [
                'status' => 'blocked',
            ])
            ->assertRedirect(route('admin.users.index'));

        $this->assertSame('blocked', $user->refresh()->status);

        $this->actingAs($admin)
            ->patch(route('admin.users.status', $user), [
                'status' => 'active',
            ])
            ->assertRedirect(route('admin.users.index'));

        $this->assertSame('active', $user->refresh()->status);
    }

    public function test_admin_cannot_block_themselves(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'status' => 'active',
        ]);

        $this->actingAs($admin)
            ->patch(route('admin.users.status', $admin), [
                'status' => 'blocked',
            ])
            ->assertForbidden();

        $this->assertSame('active', $admin->refresh()->status);
    }

    public function test_blocked_user_cannot_login(): void
    {
        $blockedUser = User::factory()->create([
            'email' => 'blocked@example.com',
            'status' => 'blocked',
        ]);

        $this->post(route('login'), [
            'email' => $blockedUser->email,
            'password' => 'password',
        ])
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_blocked_authenticated_user_is_logged_out_on_next_request(): void
    {
        $blockedUser = User::factory()->create(['status' => 'blocked']);

        $this->actingAs($blockedUser)
            ->get(route('dashboard'))
            ->assertRedirect(route('login'));

        $this->assertGuest();
    }

    public function test_user_cannot_register_as_admin_by_submitting_role(): void
    {
        $this->post(route('register'), [
            'name' => 'Injected Admin',
            'email' => 'injected-admin@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'role' => 'admin',
            'status' => 'blocked',
        ])->assertRedirect(route('dashboard', absolute: false));

        $user = User::query()
            ->where('email', 'injected-admin@example.com')
            ->firstOrFail();

        $this->assertSame('user', $user->role);
        $this->assertSame('active', $user->status);
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
