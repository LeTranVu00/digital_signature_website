@extends('frontend.layouts.app')

@section('title', 'Dịch vụ - Digital Signature')

@section('content')
    <section class="relative overflow-hidden bg-zinc-950 py-16 text-white sm:py-20 lg:py-24" data-scroll-section="Dịch vụ">
        <div class="absolute inset-0 ui-mesh-bg opacity-70"></div>
        <div class="relative mx-auto max-w-7xl px-4 text-center sm:px-6 lg:px-8" data-reveal="fade-up">
            @if (! empty($homeContent['hero_title']))
                <h1 class="site-page-title">
                    {{ $homeContent['hero_title'] }}
                </h1>
            @endif
            @if (! empty($homeContent['hero_copy']))
                <p class="site-page-copy">
                    {{ $homeContent['hero_copy'] }}
                </p>
            @endif
        </div>
    </section>

    <section class="site-section-cool" data-scroll-section="Nhóm dịch vụ">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            @if (! empty($homeContent['intro_text']))
                <div class="mx-auto max-w-3xl text-center" data-reveal="fade-up">
                    <p class="site-section-copy">
                        {{ $homeContent['intro_text'] }}
                    </p>
                </div>
            @endif

            @if (! empty($services))
                <div class="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($services as $index => $service)
                        <div data-reveal="fade-up" data-reveal-delay="{{ $index * 70 }}">
                            @include('frontend.components.service-card', $service)
                        </div>
                    @endforeach
                </div>
            @else
                <div class="rounded-xl border border-dashed border-slate-300 bg-white p-6 text-center text-sm font-semibold text-slate-500">
                    Chưa có dịch vụ nào.
                </div>
            @endif
        </div>
    </section>

    @if (! empty($steps))
        <section class="site-section-warm" data-scroll-section="Quy trình">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                @if (! empty($homeContent['process_intro']))
                    <div class="mx-auto max-w-3xl text-center" data-reveal="fade-up">
                        <h2 class="site-section-title">
                            {{ $homeContent['process_intro'] }}
                        </h2>
                    </div>
                @endif

                <div class="mt-10 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    @foreach ($steps as $index => $step)
                        <article class="site-feature-card" data-reveal="fade-up" data-reveal-delay="{{ $index * 80 }}">
                            <div class="text-4xl font-extrabold text-amber-700">{{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}</div>
                            @if (! empty($step['title']))
                                <h3 class="mt-4 text-xl font-extrabold text-slate-950">{{ $step['title'] }}</h3>
                            @endif
                            @if (! empty($step['desc']))
                                <p class="mt-3 text-base font-medium leading-7 text-slate-600">{{ $step['desc'] }}</p>
                            @endif
                        </article>
                    @endforeach
                </div>
            </div>
        </section>
    @endif
@endsection
