@extends('frontend.layouts.app')

@section('title', $category->name . ' - Digital Signature')

@section('content')
    <section class="bg-gray-950 py-20 text-white">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <p class="text-sm font-semibold uppercase text-blue-200">Danh mục</p>
            <h1 class="mt-4 max-w-3xl text-4xl font-bold leading-tight">
                {{ $category->name }}
            </h1>
            @if ($category->description)
                <p class="mt-5 max-w-2xl leading-8 text-gray-200">
                    {{ $category->description }}
                </p>
            @endif
        </div>
    </section>

    <section class="bg-white py-16">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mb-8 flex flex-wrap gap-3">
                <a href="{{ route('blog.index') }}"
                   class="rounded-full border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:border-blue-700 hover:text-blue-700">
                    Tất cả
                </a>

                @foreach ($categories as $item)
                    <a href="{{ route('blog.category', $item->slug) }}"
                       class="rounded-full px-4 py-2 text-sm font-semibold {{ $item->is($category) ? 'bg-blue-700 text-white' : 'border border-gray-300 text-gray-700 hover:border-blue-700 hover:text-blue-700' }}">
                        {{ $item->name }}
                    </a>
                @endforeach
            </div>

            <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                @forelse ($posts as $post)
                    @include('frontend.components.post-card', ['post' => $post])
                @empty
                    <div class="rounded-xl border border-dashed border-gray-300 p-8 text-gray-500 md:col-span-2 lg:col-span-3">
                        Chưa có bài viết công khai trong danh mục này.
                    </div>
                @endforelse
            </div>

            <div class="mt-10">
                {{ $posts->links() }}
            </div>
        </div>
    </section>
@endsection
