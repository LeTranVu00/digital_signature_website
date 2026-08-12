@extends('frontend.layouts.app')

@section('title', $category->name . ' - Diễn đàn')

@section('content')
    <section class="relative overflow-hidden bg-zinc-950 py-20 text-white sm:py-24" data-scroll-section="Danh mục diễn đàn">
        <div class="absolute inset-0 ui-mesh-bg opacity-70"></div>
        <div class="relative mx-auto max-w-7xl px-4 text-center sm:px-6 lg:px-8" data-reveal="fade-up">
            <h1 class="site-page-title">
                {{ $category->name }}
            </h1>
            @if ($category->description)
                <p class="site-page-copy">
                    {{ $category->description }}
                </p>
            @endif
        </div>
    </section>

    <section class="site-section-cool" data-scroll-section="Chủ đề trong danh mục">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mb-10 flex flex-wrap justify-center gap-3" data-reveal="fade-up">
                <a href="{{ route('blog.index') }}"
                   class="rounded-full border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 transition hover:border-amber-700 hover:bg-amber-50 hover:text-amber-700">
                    Tất cả chủ đề
                </a>

                @foreach ($categories as $item)
                    <a href="{{ route('blog.category', $item->slug) }}"
                       class="rounded-full px-4 py-2 text-sm font-semibold transition {{ $item->is($category) ? 'bg-zinc-950 text-white shadow-sm' : 'border border-slate-300 text-slate-700 hover:border-amber-700 hover:bg-amber-50 hover:text-amber-700' }}">
                        {{ $item->name }}
                    </a>
                @endforeach
            </div>

            <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                @forelse ($posts as $post)
                    <div data-reveal="fade-up" data-reveal-delay="{{ $loop->index * 70 }}">
                        @include('frontend.components.post-card', ['post' => $post])
                    </div>
                @empty
                    <x-ui.empty-state
                        class="md:col-span-2 lg:col-span-3"
                        title="Chưa có chủ đề công khai"
                        description="Chưa có chủ đề công khai trong danh mục này."
                    />
                @endforelse
            </div>

            <x-ui.pagination :paginator="$posts" class="mt-10" />
        </div>
    </section>
@endsection
