<article class="overflow-hidden rounded-lg border border-slate-200/80 bg-white/95 shadow-[0_24px_70px_-48px_rgb(15_23_42/0.5)] transition duration-200 ease-out hover:-translate-y-0.5 hover:border-amber-200 hover:shadow-[0_28px_80px_-44px_rgb(15_23_42/0.55)]">
    <a href="{{ route('blog.show', $post->slug) }}">
        @if ($post->thumbnail)
            <img
                src="{{ asset('storage/' . $post->thumbnail) }}"
                alt="{{ $post->title }}"
                class="h-44 w-full object-cover sm:h-48"
                loading="lazy"
                decoding="async"
            >
        @else
            <img
                src="{{ asset('images/digital-signature-cybersecurity-hero.png') }}"
                alt="CHỮ KÝ SỐ VIP"
                class="h-44 w-full object-cover sm:h-48"
                loading="lazy"
                decoding="async"
            >
        @endif
    </a>

    <div class="p-6">
        <div class="flex flex-wrap items-center gap-2 text-sm font-medium text-slate-500">
            <a href="{{ route('blog.category', $post->category->slug) }}"
                    class="font-semibold text-red-600 hover:text-red-700">
                {{ $post->category->name }}
            </a>
            <span aria-hidden="true">/</span>
            <span>{{ $post->published_at?->format('d/m/Y') }}</span>
        </div>

        <h3 class="mt-3 text-xl font-extrabold leading-snug text-slate-950">
            <a href="{{ route('blog.show', $post->slug) }}"
               class="transition-colors hover:text-red-600">
                {{ $post->title }}
            </a>
        </h3>

        <p class="mt-3 line-clamp-3 text-base font-medium leading-7 text-slate-600">
            {{ $post->summary ?: str($post->content)->stripTags()->limit(140) }}
        </p>

        <a href="{{ route('blog.show', $post->slug) }}"
              class="mt-5 inline-flex text-sm font-semibold text-red-600 transition-colors hover:text-red-700">
            Xem thảo luận
        </a>
    </div>
</article>
