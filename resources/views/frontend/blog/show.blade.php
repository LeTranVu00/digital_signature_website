@extends('frontend.layouts.app')

@section('title', $post->title . ' - Digital Signature')

@section('content')
    <article class="bg-white">
        <section class="bg-gray-950 py-16 text-white">
            <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
                <a href="{{ route('blog.category', $post->category->slug) }}"
                   class="text-sm font-semibold uppercase text-blue-200 hover:text-blue-100">
                    {{ $post->category->name }}
                </a>

                <h1 class="mt-4 text-4xl font-bold leading-tight sm:text-5xl">
                    {{ $post->title }}
                </h1>

                <div class="mt-6 flex flex-wrap gap-3 text-sm text-gray-300">
                    <span>{{ $post->user?->name ?? 'Admin' }}</span>
                    <span>•</span>
                    <span>{{ $post->published_at?->format('d/m/Y H:i') }}</span>
                    <span>•</span>
                    <span>{{ number_format($post->views) }} lượt xem</span>
                </div>
            </div>
        </section>

        <section class="py-12">
            <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
                @if ($post->thumbnail)
                    <img
                        src="{{ asset('storage/' . $post->thumbnail) }}"
                        alt="{{ $post->title }}"
                        class="mb-10 max-h-[460px] w-full rounded-xl object-cover"
                    >
                @endif

                @if ($post->summary)
                    <p class="mb-8 rounded-xl bg-blue-50 p-5 text-lg leading-8 text-blue-950">
                        {{ $post->summary }}
                    </p>
                @endif

                <div class="post-content">
                    {!! $post->content !!}
                </div>
            </div>
        </section>
    </article>

    <section class="bg-gray-50 py-16">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <h2 class="text-3xl font-bold text-gray-950">Bài viết liên quan</h2>

            <div class="mt-8 grid gap-6 md:grid-cols-3">
                @forelse ($relatedPosts as $relatedPost)
                    @include('frontend.components.post-card', ['post' => $relatedPost])
                @empty
                    <div class="rounded-xl border border-dashed border-gray-300 p-8 text-gray-500 md:col-span-3">
                        Chưa có bài viết liên quan.
                    </div>
                @endforelse
            </div>
        </div>
    </section>
@endsection
