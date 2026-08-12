@extends('frontend.layouts.app')

@section('title', 'Phần mềm hỗ trợ - Digital Signature')

@section('content')
    <section class="relative overflow-hidden bg-zinc-950 py-20 text-white sm:py-24" data-scroll-section="Phần mềm hỗ trợ">
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
                            x-bind:class="activeCategory === {{ $categoryIndex }} ? 'bg-slate-950 text-white shadow-sm' : 'text-slate-600 hover:bg-amber-50 hover:text-amber-800'"
                            x-on:click="activeCategory = {{ $categoryIndex }}"
                            role="tab"
                            x-bind:aria-selected="(activeCategory === {{ $categoryIndex }}).toString()"
                        >
                            <span>{{ $category['name'] ?: 'Danh mục ' . ($categoryIndex + 1) }}</span>
                            <span class="rounded-full px-2 py-0.5 text-xs" x-bind:class="activeCategory === {{ $categoryIndex }} ? 'bg-white/15 text-white' : 'bg-slate-100 text-slate-500'">
                                {{ count($category['items'] ?? []) }}
                            </span>
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
                                            <h2 class="mt-2 text-2xl font-extrabold text-slate-950">{{ $item['name'] }}</h2>
                                        </div>
                                        <a
                                            href="{{ $item['url'] }}"
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            class="inline-flex rounded-lg bg-slate-950 px-4 py-2 text-sm font-semibold text-white transition hover:bg-slate-800"
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

    <section class="site-section-warm" data-scroll-section="Hỗ trợ cài đặt">
        <div class="mx-auto grid max-w-7xl gap-8 px-4 sm:px-6 lg:grid-cols-[1fr_0.8fr] lg:px-8">
            <div data-reveal="fade-right">
                <h2 class="site-section-title">
                    {{ $softwareContent['support_title'] }}
                </h2>
                <p class="mt-5 site-section-copy">
                    {{ $softwareContent['support_copy'] }}
                </p>
            </div>
            <div class="site-feature-card" data-reveal="fade-left">
                <h3 class="text-2xl font-extrabold text-slate-950">Thông tin nên chuẩn bị</h3>
                <ul class="mt-5 grid gap-3 text-base font-medium leading-7 text-slate-700">
                    @foreach ($softwareChecklist as $item)
                        <li>{{ $item }}</li>
                    @endforeach
                </ul>
                <x-ui.button :href="route('contact')" class="mt-6">
                    Liên hệ hỗ trợ
                </x-ui.button>
            </div>
        </div>
    </section>
@endsection
