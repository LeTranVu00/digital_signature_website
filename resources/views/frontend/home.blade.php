@extends('frontend.layouts.app')

@section('title', 'Trang chủ - Digital Signature')

@section('content')
    @php
        $youtubeEmbedUrl = $homeContent['youtube_embed_url'] ?? '';
    @endphp

    <section
        x-data="{
            slides: @js($heroSlides->all()),
            current: 0,
            timer: null,
            next() {
                this.current = (this.current + 1) % this.slides.length;
            },
            prev() {
                this.current = (this.current + this.slides.length - 1) % this.slides.length;
            },
            start() {
                if (this.slides.length < 2 || window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
                    return;
                }

                this.timer = setInterval(() => this.next(), 5500);
            },
            stop() {
                if (this.timer) {
                    clearInterval(this.timer);
                    this.timer = null;
                }
            },
            restart() {
                this.stop();
                this.start();
            }
        }"
        x-init="start()"
        class="relative isolate overflow-hidden bg-slate-950 pt-32 text-white sm:pt-36"
        data-scroll-section="Trang chủ"
    >
        <img
            src="{{ $heroSlides->first() }}"
            x-bind:src="slides[current]"
            alt="Giải pháp chữ ký số và hỗ trợ doanh nghiệp"
            class="absolute inset-0 -z-20 h-full w-full object-cover transition duration-700 [object-size:92%_auto]"
            fetchpriority="high"
        >
        <div class="absolute inset-0 -z-10 bg-slate-950/78"></div>

        <button
            type="button"
            x-show="slides.length > 1"
            x-cloak
            x-on:click="prev(); restart()"
            class="ui-focus absolute left-3 top-1/2 z-10 hidden h-11 w-11 -translate-y-1/2 items-center justify-center rounded-full border border-white/20 bg-white/10 text-white backdrop-blur transition hover:bg-white/20 sm:inline-flex lg:left-8"
            aria-label="Chuyển ảnh trước"
        >
            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 18l-6-6 6-6" />
            </svg>
        </button>

        <button
            type="button"
            x-show="slides.length > 1"
            x-cloak
            x-on:click="next(); restart()"
            class="ui-focus absolute right-3 top-1/2 z-10 hidden h-11 w-11 -translate-y-1/2 items-center justify-center rounded-full border border-white/20 bg-white/10 text-white backdrop-blur transition hover:bg-white/20 sm:inline-flex lg:right-8"
            aria-label="Chuyển ảnh tiếp theo"
        >
            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 18l6-6-6-6" />
            </svg>
        </button>

        <div class="mx-auto flex min-h-[38rem] max-w-5xl flex-col items-center justify-center px-4 pb-16 text-center sm:px-6 lg:px-8">
            <div data-reveal="fade-up">
                <h1 class="site-page-title">
                    {{ $homeContent['hero_title'] }}
                </h1>

                <p class="site-page-copy">
                    {{ $homeContent['hero_copy'] }}
                </p>

                <form action="{{ route('blog.index') }}" method="GET" class="mx-auto mt-8 max-w-3xl">
                    <label for="home-search" class="sr-only">Tìm kiếm trong diễn đàn</label>
                    <div class="flex flex-col gap-3 rounded-lg border border-white/20 bg-white p-2 shadow-2xl shadow-slate-950/30 sm:flex-row sm:items-center">
                        <div class="flex min-h-12 flex-1 items-center gap-3 px-3 text-slate-500">
                            <svg class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.35-4.35M10.5 18a7.5 7.5 0 1 1 0-15 7.5 7.5 0 0 1 0 15Z" />
                            </svg>
                            <input
                                id="home-search"
                                type="search"
                                name="search"
                                class="w-full border-0 bg-transparent p-0 text-base font-semibold text-slate-900 placeholder:text-slate-400 focus:ring-0"
                                placeholder="Tìm: chữ ký số, hóa đơn, phần mềm, kế toán..."
                            >
                        </div>
                        <button
                            type="submit"
                            class="inline-flex min-h-12 items-center justify-center rounded-lg bg-red-600 px-6 text-sm font-bold text-white shadow-md shadow-red-950/20 transition hover:bg-red-700"
                        >
                            Tìm kiếm
                        </button>
                    </div>
                </form>

                <div class="mt-7 flex flex-wrap justify-center gap-3">
                    <x-ui.button :href="route('pricing')" class="!bg-amber-400 !text-slate-950 hover:!bg-amber-300">
                        Xem báo giá
                    </x-ui.button>
                    <x-ui.button :href="route('contact')" variant="secondary" class="!border-white/30 !bg-white/10 !text-white hover:!bg-white/15">
                        Gửi yêu cầu tư vấn
                    </x-ui.button>
                </div>

                <div class="mx-auto mt-9 grid max-w-3xl gap-3 sm:grid-cols-3">
                    @foreach ($stats as $item)
                        <div class="rounded-lg border border-white/15 bg-white/10 p-4 text-left backdrop-blur">
                            <p class="text-2xl font-bold text-amber-300">{{ $item['value'] }}</p>
                            <p class="mt-1 text-xs font-semibold leading-5 text-zinc-200">{{ $item['label'] }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div
            x-show="slides.length > 1"
            x-cloak
            class="absolute bottom-6 left-1/2 z-10 flex -translate-x-1/2 gap-2"
            aria-label="Danh sách ảnh trang chủ"
        >
            <template x-for="(slide, index) in slides" x-bind:key="slide">
                <button
                    type="button"
                    x-on:click="current = index; restart()"
                    class="h-2.5 rounded-full transition"
                    x-bind:class="current === index ? 'w-8 bg-amber-300' : 'w-2.5 bg-white/50 hover:bg-white/80'"
                    x-bind:aria-label="'Chuyển tới ảnh ' + (index + 1)"
                ></button>
            </template>
        </div>
    </section>

    <section class="site-section-cool" data-scroll-section="Giới thiệu công ty">
        <div class="mx-auto max-w-7xl px-4 text-center sm:px-6 lg:px-8">
            <div data-reveal="fade-up">
                <p class="ui-section-kicker"><span>Giới thiệu công ty</span></p>
            </div>

            <div class="mx-auto mt-8 grid max-w-5xl gap-8 site-section-copy" data-reveal="fade-up" data-reveal-delay="120">
                <p class="mx-auto max-w-4xl">
                    {{ $homeContent['intro_text'] }}
                </p>

                <div class="grid gap-4 sm:grid-cols-2">
                    @foreach ($services as $service)
                        <article class="site-feature-card text-center">
                            <h3 class="text-xl font-extrabold text-slate-950">{{ $service['title'] }}</h3>
                            <p class="mx-auto mt-3 max-w-sm text-base font-medium leading-7 text-slate-600">{{ $service['desc'] }}</p>
                        </article>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    <section class="site-section-warm" data-scroll-section="Quy trình hỗ trợ">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-8 max-w-4xl text-center" data-reveal="fade-up">
                <p class="ui-section-kicker"><span>Quy trình hỗ trợ</span></p>
                <h2 class="mx-auto mt-5 max-w-3xl text-lg font-semibold leading-8 text-slate-700 sm:text-xl">
                    {{ $homeContent['process_intro'] }}
                </h2>
            </div>

            <div class="grid items-start gap-8 lg:grid-cols-[1.05fr_0.95fr] lg:gap-10">
                <div class="self-start overflow-hidden rounded-lg border border-slate-200 bg-slate-950 shadow-[0_24px_70px_-48px_rgb(15_23_42/0.5)]" data-reveal="fade-right">
                    <div class="relative aspect-video">
                        @if ($youtubeEmbedUrl)
                            <iframe
                                src="{{ $youtubeEmbedUrl }}"
                                title="Video hướng dẫn dịch vụ chữ ký số"
                                class="absolute inset-0 h-full w-full"
                                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                                allowfullscreen
                            ></iframe>
                        @else
                            <img
                                src="{{ asset('images/home-video-thumbnail.png') }}"
                                alt="Video hướng dẫn dịch vụ chữ ký số"
                                class="h-full w-full object-cover"
                                loading="lazy"
                            >
                            <span class="absolute left-1/2 top-1/2 inline-flex h-14 w-14 -translate-x-1/2 -translate-y-1/2 items-center justify-center rounded-full bg-red-600 text-white shadow-lg">
                                <svg class="ml-1 h-7 w-7" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                    <path d="M8 5.14v13.72a1 1 0 0 0 1.52.86l11.43-6.86a1 1 0 0 0 0-1.72L9.52 4.28A1 1 0 0 0 8 5.14Z" />
                                </svg>
                            </span>
                        @endif
                    </div>
                </div>

                <div class="grid gap-4 lg:gap-5" data-reveal="fade-left">
                    @foreach ($processSteps as $index => $step)
                        <article class="flex gap-4 rounded-lg border border-slate-200/80 bg-white/95 p-5 shadow-[0_22px_62px_-46px_rgb(15_23_42/0.48)] transition duration-200 ease-out hover:-translate-y-0.5 hover:border-amber-200 hover:shadow-[0_26px_72px_-44px_rgb(15_23_42/0.55)]" data-reveal="fade-left" data-reveal-delay="{{ 120 + ($index * 90) }}">
                            <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-lg bg-slate-950 text-base font-extrabold text-white">
                                {{ $index + 1 }}
                            </span>
                            <div>
                                <h3 class="text-lg font-extrabold text-slate-950 sm:text-xl">{{ $step['title'] }}</h3>
                                <p class="mt-2 text-sm font-medium leading-6 text-slate-600 sm:text-base">{{ $step['desc'] }}</p>
                            </div>
                        </article>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    <section class="site-section-mist" data-scroll-section="Diễn đàn thảo luận">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-10 max-w-3xl text-center" data-reveal="fade-left">
                <p class="ui-section-kicker"><span>Diễn đàn thảo luận</span></p>
                <h2 class="site-section-title mt-5">
                    Chủ đề mới cập nhật.
                </h2>
            </div>

            <div class="flex flex-wrap justify-center gap-6">
                @forelse ($latestPosts as $post)
                    <div class="w-full max-w-md md:w-[calc(50%-0.75rem)] lg:w-[calc(33.333%-1rem)]" data-reveal="fade-left" data-reveal-delay="{{ $loop->index * 90 }}">
                        @include('frontend.components.post-card', ['post' => $post])
                    </div>
                @empty
                    <x-ui.empty-state
                        class="w-full"
                        title="Chưa có chủ đề công khai"
                        description="Các bài thảo luận mới sẽ được cập nhật tại đây."
                    />
                @endforelse
            </div>

            <div class="mt-8 flex justify-center" data-reveal="fade-left" data-reveal-delay="180">
                <x-ui.button :href="route('blog.index')" variant="secondary">
                    Vào diễn đàn
                </x-ui.button>
            </div>
        </div>
    </section>

    <section class="bg-red-600 py-12 text-white">
        <div class="mx-auto flex max-w-7xl flex-col items-center justify-between gap-6 px-4 text-center sm:px-6 lg:flex-row lg:text-left lg:px-8">
            <div>
                <h2 class="text-2xl font-bold sm:text-3xl">{{ $homeContent['cta_title'] }}</h2>
                <p class="mt-2 text-sm leading-6 text-red-50">
                    {{ $homeContent['cta_copy'] }}
                </p>
            </div>
            <x-ui.button :href="route('contact')" class="!bg-white !text-red-600 hover:!bg-red-50">
                Liên hệ hỗ trợ
            </x-ui.button>
        </div>
    </section>
@endsection
