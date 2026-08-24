@extends('frontend.layouts.app')

@section('title', 'Phần mềm hỗ trợ - CHỮ KÝ SỐ VIP')

@section('content')
    <section class="relative overflow-hidden bg-zinc-950 pb-20 pt-32 text-white sm:pb-24 sm:pt-36" data-scroll-section="Phần mềm hỗ trợ">
        <div class="absolute inset-0 ui-mesh-bg opacity-70"></div>
        <div class="relative mx-auto max-w-7xl px-4 text-center sm:px-6 lg:px-8" data-reveal="fade-up">
            <h1 class="site-page-title">
                {{ $softwareContent['hero_title'] }}
            </h1>
            <p class="site-page-copy">
                {{ $softwareContent['hero_copy'] }}
            </p>
        </div>
    </section>

    <section class="border-b border-slate-200 bg-white py-6" data-scroll-section="Tìm kiếm phần mềm">
        <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
            <form action="{{ route('blog.index') }}" method="GET">
                <label for="software-search" class="sr-only">Tìm kiếm phần mềm hỗ trợ</label>
                <div class="flex flex-col gap-3 rounded-2xl border border-slate-300 bg-slate-50 p-2 shadow-[0_20px_50px_-38px_rgba(15,23,42,0.6)] sm:flex-row sm:items-center">
                    <div class="flex min-h-12 flex-1 items-center gap-3 px-3 text-slate-500">
                        <svg class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.35-4.35M10.5 18a7.5 7.5 0 1 1 0-15 7.5 7.5 0 0 1 0 15Z" />
                        </svg>
                        <input
                            id="software-search"
                            type="search"
                            name="search"
                            value="{{ request('search') }}"
                            class="w-full border-0 bg-transparent p-0 text-base font-semibold text-slate-900 placeholder:text-slate-400 focus:ring-0"
                            placeholder="Tìm kiếm phần mềm, công cụ hỗ trợ..."
                        >
                    </div>
                    <button
                        type="submit"
                        class="inline-flex min-h-12 items-center justify-center rounded-xl bg-red-600 px-6 text-sm font-bold text-white transition hover:bg-red-700"
                    >
                        Tìm kiếm
                    </button>
                </div>
            </form>
        </div>
    </section>

    <section class="site-section-cool" data-scroll-section="Danh sách phần mềm">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="site-highlight-card mb-8">
                {{ $softwareContent['notice'] }}
            </div>

            <div
                x-data="{ activeCategory: 0 }"
                class="grid gap-6"
            >
                <div class="flex gap-2 overflow-x-auto rounded-xl border border-slate-200 bg-white p-2 shadow-sm" role="tablist" aria-label="Danh mục phần mềm">
                    @forelse ($softwareCategories as $categoryIndex => $category)
                        <button
                            type="button"
                            class="ui-focus inline-flex shrink-0 items-center gap-2 rounded-lg px-4 py-2 text-sm font-extrabold transition"
                            x-bind:class="activeCategory === {{ $categoryIndex }} ? 'bg-red-600 text-white shadow-sm' : 'text-slate-600 hover:bg-red-50 hover:text-red-700'"
                            x-on:click="activeCategory = {{ $categoryIndex }}"
                            role="tab"
                            x-bind:aria-selected="(activeCategory === {{ $categoryIndex }}).toString()"
                        >
                            <span>{{ $category['name'] ?: 'Danh mục ' . ($categoryIndex + 1) }}</span>
                        </button>
                    @empty
                        <div class="px-3 py-2 text-sm font-semibold text-slate-500">Chưa có danh mục phần mềm.</div>
                    @endforelse
                </div>

                @foreach ($softwareCategories as $categoryIndex => $category)
                    <div
                        x-show="activeCategory === {{ $categoryIndex }}"
                        x-cloak
                        role="tabpanel"
                    >
                        @if (! empty($category['desc']))
                            <p class="mb-4 text-base font-medium leading-7 text-slate-600">{{ $category['desc'] }}</p>
                        @endif

                        <div class="grid gap-5 md:grid-cols-2">
                            @forelse ($category['items'] ?? [] as $index => $item)
                                <article class="site-feature-card" data-reveal="fade-up" data-reveal-delay="{{ $index * 70 }}">
                                    <div class="flex flex-wrap items-start justify-between gap-4">
                                        <div>
                                            <p class="text-sm font-extrabold uppercase tracking-normal text-red-600">{{ $item['type'] }}</p>
                                            <h2 class="mt-2 text-2xl font-extrabold text-red-600">{{ $item['name'] }}</h2>
                                        </div>
                                        <a
                                            href="{{ $item['url'] }}"
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            class="inline-flex rounded-lg bg-red-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-red-700"
                                        >
                                            Tải về
                                        </a>
                                    </div>
                                    <p class="mt-4 text-base font-medium leading-7 text-slate-600">{{ $item['desc'] }}</p>
                                </article>
                            @empty
                                <div class="rounded-xl border border-dashed border-slate-300 bg-white p-6 text-center text-sm font-semibold text-slate-500 md:col-span-2">
                                    Danh mục này chưa có phần mềm.
                                </div>
                            @endforelse
                        </div>
                    </div>
                @endforeach

                @if (empty($softwareCategories))
                    <div class="rounded-xl border border-dashed border-slate-300 bg-white p-6 text-center text-sm font-semibold text-slate-500">
                        Chưa có phần mềm hỗ trợ.
                    </div>
                @endif
            </div>
        </div>
    </section>

@endsection
