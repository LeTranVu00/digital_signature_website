@extends('frontend.layouts.app')

@section('title', 'Liên hệ - Digital Signature')

@section('content')
    <section class="relative overflow-hidden bg-zinc-950 py-20 text-white sm:py-24" data-scroll-section="Liên hệ">
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
                    <p class="mt-2 text-3xl font-extrabold text-red-600">{{ $item['value'] }}</p>
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

                <div class="mt-6 grid gap-4 sm:grid-cols-2">
                    @foreach ($qrCards as $card)
                        <div class="site-feature-card p-4">
                            <img
                                src="{{ asset($card['image']) }}"
                                alt="{{ $card['alt'] ?: $card['label'] }}"
                                class="aspect-square w-full rounded-lg object-cover"
                                loading="lazy"
                            >
                            <p class="text-sm font-semibold text-slate-950">{{ $card['label'] }}</p>
                        </div>
                    @endforeach
                </div>
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
                    maxlength="255"
                />

                <x-ui.input
                    type="email"
                    name="email"
                    label="Email"
                    :value="old('email')"
                    required
                    maxlength="255"
                />

                <x-ui.input
                    name="phone"
                    label="Điện thoại"
                    :value="old('phone')"
                    maxlength="30"
                />

                <x-ui.input
                    name="company"
                    label="Công ty"
                    :value="old('company')"
                    maxlength="255"
                />

                <div class="md:col-span-2">
                    <x-ui.select name="service" label="Dịch vụ quan tâm">
                        <option value="">Chọn dịch vụ</option>

                        @foreach (\App\Models\Contact::SERVICES as $value => $label)
                            <option value="{{ $value }}" @selected(old('service') === $value)>
                                {{ $label }}
                            </option>
                        @endforeach
                    </x-ui.select>
                </div>

                <div class="md:col-span-2">
                    <x-ui.textarea
                        name="message"
                        label="Nội dung"
                        :value="old('message')"
                        rows="5"
                        required
                        maxlength="5000"
                    />
                </div>

                <div class="md:col-span-2">
                    <x-ui.button type="submit">
                        Gửi yêu cầu tư vấn
                    </x-ui.button>
                </div>
            </form>
        </div>
    </section>

    <section class="site-section-mist" data-scroll-section="Thông tin công ty">
        <div class="mx-auto grid max-w-7xl gap-8 px-4 sm:px-6 lg:grid-cols-2 lg:px-8">
            <article class="site-feature-card" data-reveal="fade-right">
                <h2 class="text-3xl font-extrabold text-slate-950">{{ $contactContent['company_name'] }}</h2>
                <dl class="mt-5 grid gap-4 text-base font-medium text-slate-700">
                    <div>
                        <dt class="font-bold text-slate-950">Địa chỉ</dt>
                        <dd class="mt-1">{{ $contactContent['address'] }}</dd>
                    </div>
                    <div>
                        <dt class="font-bold text-slate-950">Số điện thoại</dt>
                        <dd class="mt-1">{{ $contactContent['phone'] }}</dd>
                    </div>
                    <div>
                        <dt class="font-bold text-slate-950">Email</dt>
                        <dd class="mt-1">{{ $contactContent['email'] }}</dd>
                    </div>
                </dl>
            </article>

            <article class="site-feature-card" data-reveal="fade-left">
                <h2 class="text-3xl font-extrabold text-slate-950">Thông tin thanh toán</h2>
                <div class="mt-5 grid gap-4">
                    @foreach ($bankAccounts as $account)
                        <div class="rounded-lg border border-amber-200/80 bg-amber-50/70 p-4 text-base font-medium text-slate-700">
                            <p class="font-bold text-slate-950">{{ $account['bank'] }}</p>
                            <p class="mt-2">Số tài khoản: <span class="font-semibold">{{ $account['account'] }}</span></p>
                            <p class="mt-1">Chủ tài khoản: <span class="font-semibold">{{ $account['owner'] }}</span></p>
                        </div>
                    @endforeach
                </div>
            </article>
        </div>
    </section>
@endsection
