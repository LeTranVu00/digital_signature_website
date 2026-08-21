<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>@yield('title', 'CHỮ KÝ SỐ VIP')</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" type="image/jpeg" href="{{ asset('images/logo.jpg') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/logo.jpg') }}">

    <script>
        if (! window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
            document.documentElement.classList.add('reveal-enabled');
        }
    </script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="overflow-x-hidden bg-white text-gray-900 antialiased">
    <x-ui.toast-container />

    @unless (trim($__env->yieldContent('hide_header')))
    <header
        x-data="frontendHeader()"
        x-on:scroll.window="updateScrolled()"
        x-on:keydown.escape.window="closeMobileMenu()"
        class="fixed left-0 right-0 top-4 z-50 px-4"
    >
        <nav
            class="site-header-shell mx-auto flex w-full max-w-6xl items-center justify-between gap-4 rounded-full border border-amber-100/80 px-4 py-3 shadow-[0_18px_55px_-34px_rgb(15_23_42/0.75)] ring-1 ring-amber-200/70 backdrop-blur-xl transition-all duration-300 sm:px-6"
            x-bind:class="scrolled ? 'border-amber-200/90 shadow-[0_24px_70px_-36px_rgb(15_23_42/0.85)] ring-amber-300/70' : ''"
        >
            <a href="{{ route('home') }}"
               class="inline-flex shrink-0 items-center gap-3 text-base font-bold text-slate-950 sm:text-lg">
                <img
                    src="{{ asset('images/logo.jpg') }}"
                    alt="CHỮ KÝ SỐ VIP"
                    width="40"
                    height="40"
                    decoding="async"
                    class="h-10 w-10 rounded-full bg-white object-contain ring-1 ring-red-100"
                >
                <span class="hidden sm:inline">CHỮ KÝ SỐ VIP</span>
            </a>

            @php
                $frontendLinks = [
                    ['label' => 'Trang chủ', 'url' => route('home'), 'active' => request()->routeIs('home')],
                    ['label' => 'Báo giá', 'url' => route('pricing'), 'active' => request()->routeIs('pricing')],
                    ['label' => 'Diễn đàn', 'url' => route('blog.index'), 'active' => request()->routeIs('blog.*')],
                    ['label' => 'Phần mềm hỗ trợ', 'url' => route('software'), 'active' => request()->routeIs('software')],
                    ['label' => 'Liên hệ', 'url' => route('contact'), 'active' => request()->routeIs('contact')],
                ];
            @endphp

            <div class="hidden items-center gap-1 text-sm font-semibold text-slate-700 md:flex">
                @foreach ($frontendLinks as $link)
                    <a
                        href="{{ $link['url'] }}"
                        class="{{ $link['active'] ? 'bg-red-50 text-red-600 shadow-sm ring-1 ring-red-100' : 'text-slate-600 hover:bg-red-50 hover:text-red-600' }} rounded-full px-5 py-2 transition-all"
                    >
                        {{ $link['label'] }}
                    </a>
                @endforeach
            </div>

            <div class="hidden items-center gap-4 md:flex">
                @auth
                    <x-ui.user-menu :user="auth()->user()" />
                @else
                    <x-ui.button :href="route('login')" size="sm" class="!rounded-full !bg-red-600 !px-6 !py-2.5 !text-white hover:!bg-red-700">
                        Đăng nhập
                    </x-ui.button>
                @endauth
            </div>

            <button
                type="button"
                class="ui-focus inline-flex h-10 w-10 items-center justify-center rounded-full border border-slate-200 bg-white text-slate-700 transition hover:border-red-200 hover:bg-red-50 hover:text-red-600 md:hidden"
                x-on:click="toggleMobileMenu()"
                x-bind:aria-expanded="mobileMenuOpen.toString()"
                x-bind:aria-label="mobileMenuOpen ? 'Đóng menu' : 'Mở menu'"
                aria-controls="frontend-mobile-menu"
            >
                <svg x-show="! mobileMenuOpen" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
                <svg x-show="mobileMenuOpen" x-cloak class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 6l12 12M18 6L6 18" />
                </svg>
            </button>
        </nav>

        <div
            id="frontend-mobile-menu"
            x-show="mobileMenuOpen"
            x-cloak
            x-transition:enter="duration-200 ease-out"
            x-transition:enter-start="-translate-y-2 opacity-0"
            x-transition:enter-end="translate-y-0 opacity-100"
            x-transition:leave="duration-150 ease-in"
            x-transition:leave-start="translate-y-0 opacity-100"
            x-transition:leave-end="-translate-y-2 opacity-0"
            class="site-header-menu mx-auto mt-2 max-w-6xl overflow-hidden rounded-3xl border border-amber-100 shadow-[0_24px_70px_-36px_rgb(15_23_42/0.8)] backdrop-blur-xl md:hidden"
            x-on:click.outside="closeMobileMenu()"
        >
            <nav class="grid gap-1 px-4 py-4 text-sm font-semibold text-slate-700 sm:px-5">
                @foreach ($frontendLinks as $link)
                    <a
                        href="{{ $link['url'] }}"
                        x-on:click="closeMobileMenu()"
                        class="{{ $link['active'] ? 'bg-red-50 text-red-600' : 'hover:bg-red-50 hover:text-red-600' }} rounded-lg px-3 py-2.5 transition"
                    >
                        {{ $link['label'] }}
                    </a>
                @endforeach

                    <div class="mt-2 grid gap-2 border-t border-red-100 pt-3">
                    @auth
                        <div class="flex items-center gap-3 rounded-lg bg-red-50 p-3">
                            <x-ui.avatar :user="auth()->user()" />

                            <div class="min-w-0">
                                <p class="truncate text-sm font-bold text-slate-950">{{ auth()->user()->name }}</p>
                                <p class="truncate text-xs text-slate-500">{{ auth()->user()->email }}</p>
                                <p class="mt-1 truncate text-xs text-slate-500">
                                    {{ auth()->user()->phone ?: 'Chưa cập nhật số điện thoại' }}
                                </p>
                            </div>
                        </div>

                        <a
                            href="{{ route('profile.edit') }}"
                            x-on:click="closeMobileMenu()"
                            class="rounded-lg px-3 py-2.5 transition hover:bg-red-50 hover:text-red-600"
                        >
                            Hồ sơ cá nhân
                        </a>

                        <a
                            href="{{ route('profile.password.edit') }}"
                            x-on:click="closeMobileMenu()"
                            class="rounded-lg px-3 py-2.5 transition hover:bg-red-50 hover:text-red-600"
                        >
                            Đổi mật khẩu
                        </a>

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf

                            <button
                                type="submit"
                                class="w-full rounded-lg px-3 py-2.5 text-left transition hover:bg-red-50 hover:text-red-600"
                            >
                                Đăng xuất
                            </button>
                        </form>
                    @else
                        <x-ui.button :href="route('login')" full class="!rounded-full !bg-red-600 !text-white hover:!bg-red-700">
                            Đăng nhập
                        </x-ui.button>
                    @endauth
                </div>
            </nav>
        </div>
    </header>
    @endunless

    @unless (request()->routeIs('home', 'blog.index'))
        <section class="border-b border-slate-300 bg-white py-6 pt-28" data-scroll-section="Tìm kiếm">
            <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
                <form action="{{ route('blog.index') }}" method="GET">
                    <label for="site-search" class="sr-only">Tìm kiếm trong diễn đàn</label>
                    <div class="flex flex-col gap-3 rounded-lg border-2 border-slate-400 bg-white p-2 shadow-sm sm:flex-row sm:items-center">
                        <div class="flex min-h-12 flex-1 items-center gap-3 px-3 text-slate-500">
                            <svg class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.35-4.35M10.5 18a7.5 7.5 0 1 1 0-15 7.5 7.5 0 0 1 0 15Z" />
                            </svg>
                            <input
                                id="site-search"
                                type="search"
                                name="search"
                                class="w-full border-0 bg-transparent p-0 text-base font-semibold text-slate-900 placeholder:text-slate-400 focus:ring-0"
                                placeholder="Tìm kiếm chủ đề, chữ ký số, hóa đơn..."
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
            </div>
        </section>
    @endunless

    <main>
        @yield('content')
    </main>

    @unless (trim($__env->yieldContent('hide_footer')))
        @php
            $contactSettings = \App\Models\SiteSetting::valueFor('contact');
            $footerContactRows = collect($contactSettings['cards'] ?? [])
                ->filter(fn (array $row): bool => trim((string) ($row['title'] ?? '')) !== '' || trim((string) ($row['value'] ?? '')) !== '')
                ->take(3)
                ->values();
            $floatingSupportLinks = collect($contactSettings['support_links'] ?? [])
                ->filter(fn (array $row): bool => trim((string) ($row['url'] ?? '')) !== '')
                ->values();
            $floatingZaloLinks = $floatingSupportLinks
                ->where('type', 'zalo')
                ->values();
            $floatingPhoneLink = $floatingSupportLinks
                ->firstWhere('type', 'phone');
        @endphp

        <x-ui.scroll-navigator />

        @if ($floatingZaloLinks->isNotEmpty() || $floatingPhoneLink)
            <div
                x-data="{ zaloOpen: false }"
                x-on:keydown.escape.window="zaloOpen = false"
                class="fixed bottom-20 right-4 z-50 flex flex-col items-end gap-3 sm:bottom-24 sm:right-6"
            >
                @if ($floatingZaloLinks->isNotEmpty())
                    <div
                        x-show="zaloOpen"
                        x-cloak
                        x-transition:enter="duration-200 ease-out"
                        x-transition:enter-start="translate-y-2 opacity-0"
                        x-transition:enter-end="translate-y-0 opacity-100"
                        x-transition:leave="duration-150 ease-in"
                        x-transition:leave-start="translate-y-0 opacity-100"
                        x-transition:leave-end="translate-y-2 opacity-0"
                        x-on:click.outside="zaloOpen = false"
                        class="mb-1 w-[min(calc(100vw-2rem),22rem)] overflow-hidden rounded-2xl border border-sky-100 bg-white shadow-[0_24px_80px_-34px_rgb(15_23_42/0.65)] ring-1 ring-white/80"
                    >
                        <div class="border-b border-sky-100 bg-sky-50 px-4 py-3">
                            <p class="text-sm font-extrabold text-slate-950">Danh sách Zalo hỗ trợ</p>
                            <p class="mt-1 text-xs font-medium text-slate-500">Chọn kênh phù hợp để được tư vấn nhanh.</p>
                        </div>

                        <div class="grid gap-2 p-2.5">
                            @foreach ($floatingZaloLinks as $index => $link)
                                <a
                                    href="{{ $link['url'] }}"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="ui-focus flex items-center gap-3 rounded-xl px-3 py-3 text-left transition hover:bg-sky-50"
                                >
                                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-[#0068ff] text-[0.65rem] font-black text-white">
                                        Zalo
                                    </span>
                                    <span class="min-w-0">
                                        <span class="block truncate text-sm font-extrabold text-slate-950">
                                            {{ $link['label'] ?: 'Zalo hỗ trợ ' . ($index + 1) }}
                                        </span>
                                        <span class="mt-0.5 block truncate text-xs font-medium text-slate-500">
                                            {{ $link['url'] }}
                                        </span>
                                    </span>
                                </a>
                            @endforeach
                        </div>
                    </div>

                    <button
                        type="button"
                        class="floating-support-button ui-focus flex h-14 w-14 items-center justify-center rounded-full bg-[#0068ff] text-[0.72rem] font-black text-white shadow-xl shadow-sky-950/30 ring-2 ring-white transition hover:-translate-y-0.5"
                        x-on:click="zaloOpen = ! zaloOpen"
                        x-bind:aria-expanded="zaloOpen.toString()"
                        aria-label="Mở danh sách Zalo hỗ trợ"
                    >
                        <span>Zalo</span>
                    </button>
                @endif

                @if ($floatingPhoneLink)
                    <a
                        href="{{ $floatingPhoneLink['url'] }}"
                        class="floating-support-button floating-support-phone ui-focus flex h-14 w-14 items-center justify-center rounded-full bg-red-600 text-white shadow-xl shadow-red-950/30 ring-2 ring-white transition hover:-translate-y-0.5"
                        aria-label="{{ $floatingPhoneLink['label'] ?: 'Gọi hỗ trợ' }}"
                    >
                        <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="M6.6 10.8c1.44 2.83 3.77 5.14 6.6 6.6l2.2-2.2c.28-.28.68-.37 1.04-.25 1.14.38 2.37.58 3.56.58.55 0 1 .45 1 1V20c0 .55-.45 1-1 1C10.61 21 3 13.39 3 4c0-.55.45-1 1-1h3.48c.55 0 1 .45 1 1 0 1.2.2 2.42.58 3.56.11.36.03.76-.25 1.04l-2.21 2.2Z" fill="currentColor" />
                        </svg>
                    </a>
                @endif
            </div>
        @endif

    <footer class="border-t border-amber-400/20 bg-slate-950 text-white">
        <div class="mx-auto grid max-w-7xl gap-8 px-4 py-12 sm:px-6 md:grid-cols-2 lg:grid-cols-5 lg:px-8">
            <div class="lg:col-span-2">
                <a href="{{ route('home') }}" class="inline-flex items-center gap-3">
                    <img
                        src="{{ asset('images/logo.jpg') }}"
                        alt="CHỮ KÝ SỐ VIP"
                        class="h-11 w-11 rounded-full object-cover ring-2 ring-white/80"
                    >
                    <span class="text-xl font-bold text-white">CHỮ KÝ SỐ VIP</span>
                </a>
                <p class="mt-4 max-w-md text-sm leading-6 text-zinc-200">
                    Cung cấp giải pháp chữ ký số, hóa đơn điện tử và hợp đồng điện tử cho cá nhân, hộ kinh doanh và doanh nghiệp.
                </p>
                <p class="mt-4 text-sm text-zinc-300">
                    Hỗ trợ doanh nghiệp chuẩn hóa giao dịch số an toàn, nhanh gọn và dễ kiểm soát.
                </p>
            </div>

            <div>
                <h3 class="font-semibold text-white">Liên kết nhanh</h3>
                <ul class="mt-4 space-y-2 text-sm text-zinc-200">
                    <li><a href="{{ route('home') }}" class="transition hover:text-red-300">Trang chủ</a></li>
                    <li><a href="{{ route('pricing') }}" class="transition hover:text-red-300">Báo giá</a></li>
                    <li><a href="{{ route('blog.index') }}" class="transition hover:text-red-300">Diễn đàn</a></li>
                    <li><a href="{{ route('software') }}" class="transition hover:text-red-300">Phần mềm hỗ trợ</a></li>
                    <li><a href="{{ route('contact') }}" class="transition hover:text-red-300">Liên hệ</a></li>
                </ul>
            </div>

            <div>
                <h3 class="font-semibold text-white">Nội dung chính</h3>
                <ul class="mt-4 space-y-2 text-sm text-zinc-200">
                    <li>Báo giá chữ ký số</li>
                    <li>Diễn đàn kế toán</li>
                    <li>Phần mềm hỗ trợ</li>
                    <li>Hỏi đáp doanh nghiệp</li>
                </ul>
            </div>

            <div>
                <h3 class="font-semibold text-white">Chính sách</h3>
                <ul class="mt-4 space-y-2 text-sm text-zinc-200">
                    <li>Bảo mật thông tin</li>
                    <li>Quy trình xử lý hồ sơ</li>
                    <li>Hỗ trợ sau kích hoạt</li>
                    <li>Cam kết dịch vụ</li>
                </ul>
            </div>

            @if ($footerContactRows->isNotEmpty())
                <div class="md:col-span-2 lg:col-span-5">
                    <div class="grid gap-4 rounded-lg border border-amber-400/20 bg-white/[0.06] p-4 text-sm text-zinc-200 sm:grid-cols-3">
                        @foreach ($footerContactRows as $row)
                            <div>
                                @if (! empty($row['title']))
                                    <span class="block text-xs font-semibold uppercase text-zinc-300/80">{{ $row['title'] }}</span>
                                @endif
                                @if (! empty($row['value']))
                                    <span class="mt-1 block text-white [overflow-wrap:anywhere]">{{ $row['value'] }}</span>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

        </div>

        <div class="border-t border-white/10 px-4 py-5 text-center text-sm text-zinc-300">
            © {{ date('Y') }} CHỮ KÝ SỐ VIP. All rights reserved.
        </div>
    </footer>
    @endunless
</body>

</html>
