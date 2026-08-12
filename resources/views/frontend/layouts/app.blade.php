<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>@yield('title', 'Digital Signature')</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">

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
                    alt="Digital Signature"
                    class="h-10 w-10 rounded-full object-cover"
                >
                <span class="hidden sm:inline">Digital Signature</span>
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
                        class="{{ $link['active'] ? 'bg-red-50 text-red-600 shadow-sm ring-1 ring-red-100' : 'text-slate-600 hover:bg-amber-50 hover:text-red-600' }} rounded-full px-5 py-2 transition-all"
                    >
                        {{ $link['label'] }}
                    </a>
                @endforeach
            </div>

            <div class="hidden items-center gap-4 md:flex">
                @auth
                    <x-ui.user-menu :user="auth()->user()" />
                @else
                    <a href="{{ route('login') }}"
                       class="text-sm font-semibold text-slate-700 transition-all hover:text-red-600">
                        Đăng nhập
                    </a>
                @endauth

                <x-ui.button :href="route('contact')" size="sm" class="!rounded-full !bg-red-600 !px-6 !py-2.5 !text-white hover:!bg-red-700">
                    Gửi yêu cầu
                </x-ui.button>

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
                        <a
                            href="{{ route('login') }}"
                            x-on:click="closeMobileMenu()"
                            class="rounded-lg px-3 py-2.5 transition hover:bg-red-50 hover:text-red-600"
                        >
                            Đăng nhập
                        </a>
                    @endauth

                    <x-ui.button :href="route('contact')" full class="!rounded-full !bg-red-600 !text-white hover:!bg-red-700">
                        Gửi yêu cầu
                    </x-ui.button>
                </div>
            </nav>
        </div>
    </header>
    @endunless

    <main>
        @yield('content')
    </main>

    @unless (trim($__env->yieldContent('hide_footer')))
        <x-ui.scroll-navigator />

    <footer class="border-t border-amber-400/20 bg-slate-950 text-white">
        <div class="mx-auto grid max-w-7xl gap-8 px-4 py-12 sm:px-6 md:grid-cols-2 lg:grid-cols-5 lg:px-8">
            <div class="lg:col-span-2">
                <a href="{{ route('home') }}" class="inline-flex items-center gap-3">
                    <img
                        src="{{ asset('images/logo.jpg') }}"
                        alt="Digital Signature"
                        class="h-11 w-11 rounded-full object-cover ring-2 ring-white/80"
                    >
                    <span class="text-xl font-bold text-white">Digital Signature</span>
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
                    <li><a href="{{ route('home') }}" class="transition hover:text-amber-300">Trang chủ</a></li>
                    <li><a href="{{ route('pricing') }}" class="transition hover:text-amber-300">Báo giá</a></li>
                    <li><a href="{{ route('blog.index') }}" class="transition hover:text-amber-300">Diễn đàn</a></li>
                    <li><a href="{{ route('software') }}" class="transition hover:text-amber-300">Phần mềm hỗ trợ</a></li>
                    <li><a href="{{ route('contact') }}" class="transition hover:text-amber-300">Liên hệ</a></li>
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

            <div class="md:col-span-2 lg:col-span-5">
                <div class="grid gap-4 rounded-lg border border-amber-400/20 bg-white/[0.06] p-4 text-sm text-zinc-200 sm:grid-cols-3">
                    <div>
                        <span class="block text-xs font-semibold uppercase text-zinc-300/80">Hotline</span>
                        <span class="mt-1 block text-white">0900 000 000</span>
                    </div>
                    <div>
                        <span class="block text-xs font-semibold uppercase text-zinc-300/80">Email</span>
                        <span class="mt-1 block text-white">support@example.com</span>
                    </div>
                    <div>
                        <span class="block text-xs font-semibold uppercase text-zinc-300/80">Thời gian</span>
                        <span class="mt-1 block text-white">8:00 - 17:30, Thứ 2 - Thứ 6</span>
                    </div>
                </div>
            </div>

        </div>

        <div class="border-t border-white/10 px-4 py-5 text-center text-sm text-zinc-300">
            © {{ date('Y') }} Digital Signature. All rights reserved.
        </div>
    </footer>
    @endunless
</body>

</html>
