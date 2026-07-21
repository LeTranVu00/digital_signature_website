@extends('frontend.layouts.app')

@section('title', 'Tin tức - Digital Signature')

@section('content')
    <section class="bg-gray-950 py-20 text-white">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <p class="text-sm font-semibold uppercase text-blue-200">Tin tức</p>
            <h1 class="mt-4 max-w-3xl text-4xl font-bold leading-tight">
                Kiến thức chữ ký số, hóa đơn điện tử và giao dịch số
            </h1>
        </div>
    </section>

    <section class="bg-white py-16">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mb-8 flex flex-wrap gap-3">
                <a href="{{ route('blog.index') }}"
                   class="rounded-full bg-blue-700 px-4 py-2 text-sm font-semibold text-white">
                    Tất cả
                </a>

                @foreach ($categories as $category)
                    <a href="{{ route('blog.category', $category->slug) }}"
                       class="rounded-full border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:border-blue-700 hover:text-blue-700">
                        {{ $category->name }}
                    </a>
                @endforeach
            </div>

            <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                @forelse ($posts as $post)
                    @include('frontend.components.post-card', ['post' => $post])
                @empty
                    <div class="rounded-xl border border-dashed border-gray-300 p-8 text-gray-500 md:col-span-2 lg:col-span-3">
                        Chưa có bài viết công khai.
                    </div>
                @endforelse
            </div>

            <div class="mt-10">
                {{ $posts->links() }}
            </div>
        </div>
    </section>
@endsection
