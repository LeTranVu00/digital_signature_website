@extends('frontend.layouts.app')

@section('title', 'Báo giá - CHỮ KÝ SỐ VIP')

@section('content')
    <section class="relative overflow-hidden bg-zinc-950 pb-20 pt-32 text-white sm:pb-24 sm:pt-36" data-scroll-section="Báo giá">
        <div class="absolute inset-0 ui-mesh-bg opacity-70"></div>
        <div class="relative mx-auto max-w-7xl px-4 text-center sm:px-6 lg:px-8" data-reveal="fade-up">
            <h1 class="site-page-title">
                {{ $pricingContent['hero_title'] }}
            </h1>

            <p class="site-page-copy">
                {{ $pricingContent['hero_copy'] }}
            </p>
        </div>
    </section>

    <section class="site-section-warm" data-scroll-section="Bảng giá theo danh mục">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            @if ($pricingCategories->isNotEmpty())
                <div
                    x-data="{
                        categories: @js($pricingCategories),
                        active: @js($pricingCategories->first()['slug']),
                        get selected() {
                            return this.categories.find((category) => category.slug === this.active) || this.categories[0];
                        }
                    }"
                    class="grid gap-8 lg:grid-cols-[0.38fr_0.62fr]"
                >
                    <div class="self-start rounded-2xl border border-amber-100 bg-white/90 p-3 shadow-[0_24px_70px_-48px_rgb(15_23_42/0.55)]" data-reveal="fade-right">
                        <div class="px-3 py-3">
                            <h2 class="text-2xl font-extrabold text-slate-950">Chọn danh mục báo giá</h2>
                            <p class="mt-2 text-sm font-medium leading-6 text-slate-600">
                                Bấm vào từng danh mục để xem ảnh bảng giá do admin cập nhật.
                            </p>
                        </div>

                        <div class="mt-2 grid gap-2">
                            <template x-for="category in categories" :key="category.slug">
                                <button
                                    type="button"
                                    class="ui-focus rounded-xl px-4 py-3 text-left transition"
                                    x-bind:class="active === category.slug ? 'bg-gradient-to-r from-amber-50 via-white to-red-50 text-red-700 shadow-sm ring-1 ring-amber-100' : 'text-slate-700 hover:bg-white hover:text-red-600 hover:shadow-sm'"
                                    x-on:click="active = category.slug"
                                >
                                    <span class="block text-base font-extrabold" x-text="category.name"></span>
                                    <span class="mt-1 block text-sm font-medium leading-6 text-slate-500" x-show="category.description" x-text="category.description"></span>
                                </button>
                            </template>
                        </div>
                    </div>

                    <div class="site-feature-card p-4 sm:p-5" data-reveal="fade-left">
                        <div class="mb-4 flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
                            <div>
                                <p class="text-sm font-extrabold uppercase text-red-600">Bảng giá đang xem</p>
                                <h2 class="mt-1 text-3xl font-extrabold text-slate-950" x-text="selected.name"></h2>
                            </div>
                            <x-ui.button :href="route('contact')" size="sm">
                                Gửi yêu cầu tư vấn
                            </x-ui.button>
                        </div>

                        <div class="overflow-hidden rounded-xl border border-slate-200 bg-slate-50">
                            <img
                                x-bind:src="selected.image_url"
                                x-bind:alt="'Bảng giá ' + selected.name"
                                class="max-h-[72rem] w-full object-contain"
                                loading="lazy"
                                decoding="async"
                            >
                        </div>
                    </div>
                </div>
            @else
                <div class="grid gap-6 lg:grid-cols-3">
                    @foreach ($pricingPlans as $index => $plan)
                        <article class="site-feature-card flex h-full flex-col" data-reveal="fade-up" data-reveal-delay="{{ $index * 80 }}">
                            <h2 class="text-2xl font-extrabold text-slate-950">{{ $plan['name'] }}</h2>
                            <p class="mt-4 text-base font-medium leading-7 text-slate-600">{{ $plan['desc'] }}</p>

                            <ul class="mt-6 grid gap-3 text-base font-medium text-slate-700">
                                @foreach ($plan['features'] as $feature)
                                    <li class="flex gap-3">
                                        <span class="mt-2 h-2 w-2 shrink-0 rounded-full bg-amber-500"></span>
                                        <span>{{ $feature }}</span>
                                    </li>
                                @endforeach
                            </ul>

                            <x-ui.button :href="route('pricing.plan', $index + 1)" class="mt-7">
                                Xem chi tiết
                            </x-ui.button>
                        </article>
                    @endforeach
                </div>
            @endif
        </div>
    </section>

@endsection
