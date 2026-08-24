<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'CHỮ KÝ SỐ VIP') }}</title>
        <link rel="icon" type="image/svg+xml" href="{{ asset('images/favicon.svg') }}">
        <link rel="apple-touch-icon" href="{{ asset('images/logo.jpg') }}">

        <script>
            (() => {
                const storedTheme = localStorage.getItem('theme');
                const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;

                if (storedTheme === 'dark' || (! storedTheme && prefersDark)) {
                    document.documentElement.classList.add('dark');
                }
            })();

            if (! window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
                document.documentElement.classList.add('reveal-enabled');
            }
        </script>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-slate-900 antialiased dark:bg-slate-950 dark:text-slate-100">
        <x-ui.toast-container />

        @php
            $authMeta = match (true) {
                request()->routeIs('register') => [
                    'title' => 'Tạo tài khoản',
                    'copy' => 'Đăng ký để tham gia diễn đàn và quản lý thông tin cá nhân.',
                ],
                request()->routeIs('password.request') => [
                    'title' => 'Khôi phục mật khẩu',
                    'copy' => 'Nhập email tài khoản, hệ thống sẽ gửi liên kết đặt lại mật khẩu.',
                ],
                request()->routeIs('password.reset') => [
                    'title' => 'Đặt lại mật khẩu',
                    'copy' => 'Tạo mật khẩu mới để tiếp tục sử dụng tài khoản.',
                ],
                request()->routeIs('password.confirm') => [
                    'title' => 'Xác nhận mật khẩu',
                    'copy' => 'Vui lòng nhập lại mật khẩu để tiếp tục thao tác bảo mật.',
                ],
                request()->routeIs('verification.notice') => [
                    'title' => 'Xác minh email',
                    'copy' => 'Kiểm tra hộp thư để hoàn tất xác minh tài khoản.',
                ],
                default => [
                    'title' => 'Đăng nhập',
                    'copy' => 'Truy cập tài khoản CHỮ KÝ SỐ VIP của bạn.',
                ],
            };
        @endphp

        <div class="relative flex min-h-screen flex-col items-center overflow-hidden bg-gradient-to-br from-amber-50 via-white to-sky-50 px-4 py-8 dark:from-slate-950 dark:via-slate-950 dark:to-slate-900 sm:justify-center sm:px-6">
            <div class="pointer-events-none absolute inset-x-0 top-0 h-44 bg-gradient-to-b from-amber-200/40 via-red-100/40 to-transparent dark:from-amber-500/10 dark:via-red-500/10"></div>

            <div class="fixed right-4 top-4">
                <x-ui.theme-toggle />
            </div>

            <div class="relative mb-7 text-center">
                <a href="/" class="inline-flex items-center gap-3">
                    <img
                        src="{{ asset('images/logo.jpg') }}"
                        alt="CHỮ KÝ SỐ VIP"
                        class="h-12 w-12 rounded-full object-cover shadow-lg shadow-amber-900/20 ring-4 ring-white dark:ring-slate-800"
                    >
                    <span class="text-xl font-extrabold text-slate-950 dark:text-white">CHỮ KÝ SỐ VIP</span>
                </a>
            </div>

            <div class="relative w-full max-w-md overflow-hidden rounded-lg border border-amber-100/80 bg-white/95 p-5 shadow-[0_30px_90px_-55px_rgb(15_23_42/0.7)] ring-1 ring-white/70 backdrop-blur dark:border-slate-800 dark:bg-slate-900/95 dark:ring-slate-800 sm:p-6">
                <div class="mb-6">
                    <h1 class="text-2xl font-extrabold leading-tight text-slate-950 dark:text-white">
                        {{ $authMeta['title'] }}
                    </h1>
                    <p class="mt-2 text-sm font-medium leading-6 text-slate-600 dark:text-slate-300">
                        {{ $authMeta['copy'] }}
                    </p>
                </div>

                {{ $slot }}
            </div>
        </div>
    </body>
</html>
