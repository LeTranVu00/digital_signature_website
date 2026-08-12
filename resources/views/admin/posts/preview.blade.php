@extends('layouts.admin')

@section('title', 'Xem trước bài viết')

@section('content')
    <div class="mx-auto max-w-4xl">
        <div class="mb-6 flex items-center justify-between">
            <a
                href="{{ route('admin.posts.index') }}"
                class="text-sm font-medium text-blue-600 hover:text-blue-800"
            >
                ← Quay lại danh sách
            </a>

            <a
                href="{{ route('admin.posts.edit', $post) }}"
                class="rounded-lg bg-amber-500 px-4 py-2.5 text-sm font-medium text-white hover:bg-amber-600"
            >
                Sửa bài viết
            </a>
        </div>

        <article class="rounded-xl bg-white p-8 shadow-sm">
            <div class="mb-6 flex flex-wrap items-center gap-3 text-sm text-gray-500">
                <span>{{ $post->category?->name ?? 'Không còn danh mục' }}</span>
                <span>•</span>
                <span>{{ $post->user?->name ?? 'Không còn tác giả' }}</span>
                <span>•</span>
                <span>
                    {{ $post->status === 'published' ? 'Đã xuất bản' : 'Bản nháp' }}
                </span>
            </div>

            <h1 class="text-3xl font-bold leading-tight text-gray-950">
                {{ $post->title }}
            </h1>

            @if ($post->summary)
                <p class="mt-4 text-lg leading-8 text-gray-600">
                    {{ $post->summary }}
                </p>
            @endif

            @if ($post->thumbnail)
                <img
                    src="{{ asset('storage/' . $post->thumbnail) }}"
                    alt="{{ $post->title }}"
                    class="mt-8 max-h-[420px] w-full rounded-xl object-cover"
                    loading="lazy"
                    decoding="async"
                >
            @endif

            <div class="post-content mt-8">
                {!! $post->content !!}
            </div>
        </article>
    </div>
@endsection
