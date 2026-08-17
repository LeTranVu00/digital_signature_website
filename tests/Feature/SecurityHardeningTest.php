<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class SecurityHardeningTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_categories_admin_routes_are_not_exposed_to_regular_users(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        $this->actingAs($user)
            ->get('/categories')
            ->assertNotFound();

        $this->actingAs($user)
            ->get(route('admin.categories.index'))
            ->assertForbidden();
    }

    public function test_tinymce_content_is_sanitized_before_post_is_stored(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $category = Category::query()->create([
            'name' => 'Bao mat',
            'slug' => 'bao-mat',
        ]);

        $this->actingAs($admin)
            ->post(route('admin.posts.store'), [
                'category_id' => $category->id,
                'title' => 'Bai viet sanitize',
                'summary' => 'Noi dung can sanitize',
                'content' => '<p onclick="alert(1)">Safe</p><script>alert(1)</script><a href="javascript:alert(1)">bad</a>',
                'status' => 'published',
            ])
            ->assertRedirect(route('admin.posts.index'));

        $post = Post::query()
            ->where('title', 'Bai viet sanitize')
            ->firstOrFail();

        $this->assertStringContainsString('<p>Safe</p>', $post->content);
        $this->assertStringNotContainsString('<script', $post->content);
        $this->assertStringNotContainsString('onclick', $post->content);
        $this->assertStringNotContainsString('javascript:', $post->content);
    }

    public function test_executable_or_svg_uploads_are_rejected_for_post_thumbnail(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $category = Category::query()->create([
            'name' => 'Upload',
            'slug' => 'upload',
        ]);

        $this->actingAs($admin)
            ->post(route('admin.posts.store'), [
                'category_id' => $category->id,
                'title' => 'Bai viet upload',
                'summary' => 'Noi dung upload',
                'content' => '<p>Noi dung hop le</p>',
                'status' => 'draft',
                'thumbnail' => UploadedFile::fake()->create(
                    'shell.php',
                    10,
                    'application/x-php'
                ),
            ])
            ->assertSessionHasErrors('thumbnail');

        $this->assertDatabaseMissing('posts', [
            'title' => 'Bai viet upload',
        ]);
    }

    public function test_register_route_is_rate_limited(): void
    {
        for ($attempt = 1; $attempt <= 5; $attempt++) {
            $this->withServerVariables(['REMOTE_ADDR' => '10.20.30.40'])
                ->post(route('register'), [
                    'name' => 'Rate User',
                    'email' => 'not-an-email',
                    'password' => 'password',
                    'password_confirmation' => 'password',
                ])
                ->assertSessionHasErrors('email');
        }

        $this->withServerVariables(['REMOTE_ADDR' => '10.20.30.40'])
            ->post(route('register'), [
                'name' => 'Rate User',
                'email' => 'not-an-email',
                'password' => 'password',
                'password_confirmation' => 'password',
            ])
            ->assertTooManyRequests();
    }

    public function test_login_route_allows_only_five_failed_attempts_per_window(): void
    {
        $user = User::factory()->create(['email' => 'limited@example.com']);

        for ($attempt = 1; $attempt <= 5; $attempt++) {
            $this->withServerVariables(['REMOTE_ADDR' => '10.20.30.43'])
                ->post(route('login'), [
                    'email' => $user->email,
                    'password' => 'wrong-password',
                ])
                ->assertSessionHasErrors('email');
        }

        $this->withServerVariables(['REMOTE_ADDR' => '10.20.30.43'])
            ->post(route('login'), [
                'email' => $user->email,
                'password' => 'wrong-password',
            ])
            ->assertTooManyRequests();
    }

    public function test_oversized_request_payloads_are_rejected(): void
    {
        config(['app.debug' => false]);

        $this->withServerVariables([
            'CONTENT_LENGTH' => '10485761',
            'REMOTE_ADDR' => '10.20.30.41',
        ])
            ->get('/')
            ->assertStatus(413);
    }

    public function test_malformed_json_payloads_are_rejected(): void
    {
        config(['app.debug' => false]);

        $this->withServerVariables([
            'CONTENT_TYPE' => 'application/json',
            'HTTP_ACCEPT' => 'application/json',
            'REMOTE_ADDR' => '10.20.30.42',
        ])
            ->call('POST', route('contact.store'), [], [], [], [], '{"name":')
            ->assertStatus(400);
    }
}
