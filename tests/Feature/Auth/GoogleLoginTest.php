<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Socialite\Facades\Socialite;
use Mockery;
use Tests\TestCase;

class GoogleLoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_google_login_creates_new_user_with_user_role(): void
    {
        $this->mockGoogleUser(
            id: 'google-123',
            name: 'Google User',
            email: 'google@example.com',
            avatar: 'https://example.com/avatar.jpg'
        );

        $response = $this->get(route('google.callback'));

        $user = User::query()->where('email', 'google@example.com')->first();

        $this->assertNotNull($user);
        $this->assertSame('user', $user->role);
        $this->assertSame('google-123', $user->google_id);
        $this->assertSame('google', $user->provider);
        $this->assertSame('https://example.com/avatar.jpg', $user->avatar);
        $this->assertNotNull($user->email_verified_at);
        $this->assertAuthenticatedAs($user);
        $response->assertRedirect(route('home'));
    }

    public function test_google_login_links_existing_email_without_creating_duplicate(): void
    {
        $user = User::factory()->create([
            'email' => 'existing@example.com',
            'role' => 'user',
            'google_id' => null,
        ]);

        $this->mockGoogleUser(
            id: 'google-existing',
            name: 'Existing User',
            email: 'existing@example.com',
            avatar: 'https://example.com/existing.jpg'
        );

        $this->get(route('google.callback'))
            ->assertRedirect(route('home'));

        $this->assertSame(1, User::query()->where('email', 'existing@example.com')->count());
        $this->assertSame('google-existing', $user->refresh()->google_id);
        $this->assertSame('user', $user->role);
        $this->assertAuthenticatedAs($user);
    }

    public function test_google_login_does_not_change_existing_admin_role(): void
    {
        $admin = User::factory()->create([
            'email' => 'admin@example.com',
            'role' => 'admin',
            'google_id' => null,
        ]);

        $this->mockGoogleUser(
            id: 'google-admin',
            name: 'Admin User',
            email: 'admin@example.com',
            avatar: 'https://example.com/admin.jpg'
        );

        $this->get(route('google.callback'))
            ->assertRedirect(route('home'));

        $this->assertSame('admin', $admin->refresh()->role);
        $this->assertSame('google-admin', $admin->google_id);
        $this->assertAuthenticatedAs($admin);
    }

    private function mockGoogleUser(
        string $id,
        string $name,
        string $email,
        string $avatar
    ): void {
        $googleUser = Mockery::mock();
        $googleUser->shouldReceive('getId')->andReturn($id);
        $googleUser->shouldReceive('getName')->andReturn($name);
        $googleUser->shouldReceive('getEmail')->andReturn($email);
        $googleUser->shouldReceive('getAvatar')->andReturn($avatar);

        $provider = Mockery::mock();
        $provider->shouldReceive('user')->andReturn($googleUser);

        Socialite::shouldReceive('driver')
            ->with('google')
            ->andReturn($provider);
    }
}
