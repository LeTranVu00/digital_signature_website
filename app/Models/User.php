<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable([
    'name',
    'email',
    'phone',
    'email_verified_at',
    'google_id',
    'provider',
    'avatar',
    'password',
    'role',
    'status',
])]

#[Hidden([
    'password',
    'remember_token',
])]
class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Kiểm tra có phải Admin không.
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Kiểm tra có phải User không.
     */
    public function isUser(): bool
    {
        return $this->role === 'user';
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isBlocked(): bool
    {
        return $this->status === 'blocked';
    }

    /**
     * Danh sách bài viết của người dùng.
     */
    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }

    /**
     * Danh sách bình luận của người dùng.
     */
    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }
}
