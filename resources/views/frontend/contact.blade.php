@extends('frontend.layouts.app')

@section('title', 'Liên hệ - CHỮ KÝ SỐ VIP')

@section('content')
    <section class="relative overflow-hidden bg-zinc-950 pb-20 pt-32 text-white sm:pb-24 sm:pt-36" data-scroll-section="Liên hệ">
        <div class="absolute inset-0 ui-mesh-bg opacity-70"></div>
        <div class="relative mx-auto max-w-7xl px-4 text-center sm:px-6 lg:px-8" data-reveal="fade-up">
            <h1 class="site-page-title">
                {{ $contactContent['hero_title'] }}
            </h1>
            <p class="site-page-copy">
                {{ $contactContent['hero_copy'] }}
            </p>
        </div>
    </section>

    <section class="site-section-warm" data-scroll-section="Thông tin liên hệ">
        <div class="mx-auto grid max-w-7xl gap-5 px-4 sm:px-6 lg:grid-cols-3 lg:px-8">
            @foreach ($contacts as $index => $item)
                <article class="site-feature-card" data-reveal="fade-up" data-reveal-delay="{{ $index * 80 }}">
                    <h2 class="text-lg font-extrabold text-slate-950">{{ $item['title'] }}</h2>
                    <p class="mt-2 text-3xl font-extrabold text-red-600 [overflow-wrap:anywhere]">{{ $item['value'] }}</p>
                    <p class="mt-3 text-base font-medium leading-7 text-slate-600">{{ $item['desc'] }}</p>
                </article>
            @endforeach
        </div>
    </section>

    <section class="site-section-cool" data-scroll-section="Gửi yêu cầu">
        <div class="mx-auto grid max-w-7xl gap-10 px-4 sm:px-6 lg:grid-cols-[0.85fr_1.15fr] lg:px-8">
            <div data-reveal="fade-right">
                <h2 class="site-section-title">
                    {{ $contactContent['form_title'] }}
                </h2>
                <p class="mt-5 site-section-copy">
                    {{ $contactContent['form_copy'] }}
                </p>

                @if (! empty($qrCard['image']))
                    <div class="mt-7 flex justify-center">
                        <div class="block w-full max-w-72 rounded-lg border border-amber-200/80 bg-gradient-to-br from-white via-amber-50/40 to-sky-50/70 p-5 text-center shadow-[0_26px_80px_-48px_rgb(15_23_42/0.65)] ring-1 ring-white">
                            <div class="mx-auto flex aspect-square w-full items-center justify-center overflow-hidden rounded-lg border border-slate-200 bg-white p-4 shadow-inner shadow-slate-200/70">
                                <img
                                    src="{{ asset('storage/' . ltrim($qrCard['image'], '/')) }}"
                                    alt="{{ $qrCard['label'] ?: 'Mã QR hỗ trợ' }}"
                                    class="h-full w-full object-contain"
                                    loading="lazy"
                                >
                            </div>
                            @if ($qrCard['label'] !== '')
                                <p class="mt-4 text-sm font-extrabold leading-5 text-slate-950">{{ $qrCard['label'] }}</p>
                            @endif
                        </div>
                    </div>
                @endif
            </div>

            <form action="{{ route('contact.store') }}"
                  method="POST"
                  class="site-feature-card grid gap-5 md:grid-cols-2"
                  data-reveal="fade-left">
                @csrf

                <x-ui.input
                    name="name"
                    label="Họ tên"
                    :value="old('name')"
                    required
                    :show-required-mark="false"
                    maxlength="255"
                />

                <x-ui.input
                    type="email"
                    name="email"
                    label="Email"
                    :value="old('email')"
                    required
                    :show-required-mark="false"
                    maxlength="255"
                />

                <div class="md:col-span-2">
                    <x-ui.input
                        name="phone"
                        label="Điện thoại"
                        :value="old('phone')"
                        maxlength="30"
                    />
                </div>

                <div class="md:col-span-2">
                    <x-ui.textarea
                        name="message"
                        label="Nội dung"
                        :value="old('message')"
                        rows="5"
                        required
                        :show-required-mark="false"
                        maxlength="5000"
                    />
                </div>

                <div class="flex justify-end md:col-span-2">
                    <x-ui.button type="submit" class="!bg-red-600 !text-white hover:!bg-red-700">
                        Gửi yêu cầu tư vấn
                    </x-ui.button>
                </div>
            </form>
        </div>
    </section>

@endsection
