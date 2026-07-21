<article class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm transition hover:-translate-y-1 hover:shadow-md">
    <a href="{{ route('blog.show', $post->slug) }}">
        @if ($post->thumbnail)
            <img
                src="{{ asset('storage/' . $post->thumbnail) }}"
                alt="{{ $post->title }}"
                class="h-48 w-full object-cover"
            >
        @else
            <div class="flex h-48 items-center justify-center bg-blue-50 text-sm font-medium text-blue-700">
                Digital Signature
            </div>
        @endif
    </a>

    <div class="p-5">
        <div class="flex flex-wrap items-center gap-2 text-xs text-gray-500">
            <a href="{{ route('blog.category', $post->category->slug) }}"
               class="font-semibold text-blue-700 hover:text-blue-800">
                {{ $post->category->name }}
            </a>
            <span>•</span>
            <span>{{ $post->published_at?->format('d/m/Y') }}</span>
        </div>

        <h3 class="mt-3 text-lg font-bold leading-snug text-gray-950">
            <a href="{{ route('blog.show', $post->slug) }}"
               class="hover:text-blue-700">
                {{ $post->title }}
            </a>
        </h3>

        <p class="mt-3 line-clamp-3 text-sm leading-6 text-gray-600">
            {{ $post->summary ?: str($post->content)->stripTags()->limit(140) }}
        </p>

        <a href="{{ route('blog.show', $post->slug) }}"
           class="mt-5 inline-flex text-sm font-semibold text-blue-700 hover:text-blue-800">
            Đọc tiếp
        </a>
    </div>
</article>
