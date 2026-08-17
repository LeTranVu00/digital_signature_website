@extends('frontend.layouts.app')

@section('title', ($pricingPlan['name'] ?? 'Chi tiết báo giá') . ' - Digital Signature')

@section('content')
    <section class="relative overflow-hidden bg-zinc-950 py-16 text-white sm:py-20" data-scroll-section="Chi tiết báo giá">
        <div class="absolute inset-0 ui-mesh-bg opacity-70"></div>
        <div class="relative mx-auto max-w-5xl px-4 text-center sm:px-6 lg:px-8" data-reveal="fade-up">
            <p class="mx-auto inline-flex rounded-full border border-amber-300/40 bg-white/10 px-5 py-2 text-sm font-extrabold uppercase text-amber-100">
                Gói {{ $plan }}
            </p>
            <h1 class="site-page-title mt-5">
                {{ $pricingPlan['name'] ?? 'Chi tiết báo giá' }}
            </h1>
            @if (! empty($pricingPlan['desc']))
                <p class="site-page-copy">
                    {{ $pricingPlan['desc'] }}
                </p>
            @endif
        </div>
    </section>

    <section class="site-section-warm" data-scroll-section="Ảnh bảng giá">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="mb-8 flex flex-col gap-4 rounded-2xl border border-amber-100 bg-white/90 p-5 shadow-[0_24px_70px_-48px_rgb(15_23_42/0.55)] sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <p class="text-sm font-extrabold uppercase text-red-600">Bảng giá chi tiết</p>
                    <h2 class="mt-1 text-2xl font-extrabold text-slate-950">{{ $pricingPlan['name'] ?? 'Chi tiết báo giá' }}</h2>
                </div>

                <div class="flex flex-wrap gap-3">
                    <x-ui.button :href="route('pricing')" variant="secondary" size="sm">
                        Quay lại báo giá
                    </x-ui.button>
                    <x-ui.button :href="route('contact')" size="sm">
                        Gửi yêu cầu tư vấn
                    </x-ui.button>
                </div>
            </div>

            @if (! empty($pricingPlan['images']))
                <div class="mx-auto grid max-w-5xl gap-6">
                    @foreach ($pricingPlan['images'] as $imageIndex => $image)
                        @php
                            $imagePath = is_array($image) ? ($image['path'] ?? '') : $image;
                            $imageName = is_array($image) ? ($image['name'] ?? '') : '';
                            $imageTitle = $imageName !== '' ? $imageName : 'Ảnh bảng giá ' . ($imageIndex + 1);
                        @endphp
                        @continue($imagePath === '')

                        <article class="overflow-hidden rounded-2xl border border-slate-200 bg-white p-3 shadow-[0_24px_70px_-52px_rgb(15_23_42/0.55)]" data-reveal="fade-up" data-reveal-delay="{{ $imageIndex * 80 }}">
                            <div class="border-b border-slate-100 px-2 py-3">
                                <h3 class="text-lg font-extrabold text-slate-950">{{ $imageTitle }}</h3>
                            </div>
                            <a href="{{ asset('storage/' . $imagePath) }}" target="_blank" rel="noopener noreferrer" class="block bg-slate-50">
                                <img
                                    src="{{ asset('storage/' . $imagePath) }}"
                                    alt="{{ $imageTitle }}"
                                    class="max-h-[64rem] w-full object-contain"
                                    loading="lazy"
                                    decoding="async"
                                >
                            </a>
                        </article>
                    @endforeach
                </div>
            @else
                <div class="rounded-2xl border border-dashed border-slate-300 bg-white p-8 text-center shadow-[0_24px_70px_-52px_rgb(15_23_42/0.55)]">
                    <h2 class="text-2xl font-extrabold text-slate-950">Chưa có ảnh bảng giá cho gói này.</h2>
                    <p class="mx-auto mt-3 max-w-2xl text-base font-medium leading-7 text-slate-600">
                        Admin có thể vào dashboard để upload một hoặc nhiều ảnh cho gói này.
                    </p>
                </div>
            @endif
        </div>
    </section>
@endsection
