<?php

namespace App\Services;

use App\Models\Post;
use Illuminate\Support\Str;

class SlugService
{
    public function forPostTitle(string $title, ?Post $ignorePost = null): string
    {
        $baseSlug = Str::slug($title) ?: 'bai-viet';
        $slug = $baseSlug;
        $counter = 2;

        while ($this->postSlugExists($slug, $ignorePost)) {
            $slug = "{$baseSlug}-{$counter}";
            $counter++;
        }

        return $slug;
    }

    private function postSlugExists(string $slug, ?Post $ignorePost): bool
    {
        return Post::withTrashed()
            ->where('slug', $slug)
            ->when($ignorePost, function ($query) use ($ignorePost) {
                $query->whereKeyNot($ignorePost->getKey());
            })
            ->exists();
    }
}
