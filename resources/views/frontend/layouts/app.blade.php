<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>@yield('title', 'Digital Signature')</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-white text-gray-900">
    <header class="sticky top-0 z-40 border-b border-gray-200 bg-white/95 backdrop-blur">
        <div class="mx-auto flex max-w-7xl items-center justify-between px-4 py-4 sm:px-6 lg:px-8">
            <a href="{{ route('home') }}"
               class="text-xl font-bold text-blue-700">
                Digital Signature
            </a>

            <nav class="hidden items-center gap-7 text-sm font-medium text-gray-700 md:flex">
                <a href="{{ route('home') }}" class="hover:text-blue-700">Trang chủ</a>
                <a href="{{ route('about') }}" class="hover:text-blue-700">Giới thiệu</a>
                <a href="{{ route('services') }}" class="hover:text-blue-700">Dịch vụ</a>
                <a href="{{ route('blog.index') }}" class="hover:text-blue-700">Tin tức</a>
                <a href="{{ route('contact') }}" class="hover:text-blue-700">Liên hệ</a>
            </nav>

            <div class="hidden items-center gap-3 md:flex">
                @auth
                    <a href="{{ route('profile.edit') }}"
                       class="text-sm font-semibold text-gray-700 hover:text-blue-700">
                        Tài khoản
                    </a>
                @else
                    <a href="{{ route('login') }}"
                       class="text-sm font-semibold text-gray-700 hover:text-blue-700">
                        Đăng nhập
                    </a>
                @endauth

                <a href="{{ route('contact') }}"
                   class="rounded-lg bg-blue-700 px-4 py-2.5 text-sm font-semibold text-white hover:bg-blue-800">
                    Tư vấn ngay
                </a>
            </div>

            <details class="relative md:hidden">
                <summary class="cursor-pointer rounded-lg border border-gray-300 px-3 py-2 text-sm font-medium text-gray-700">
                    Menu
                </summary>

                <nav class="absolute right-0 mt-3 grid w-52 gap-2 rounded-lg border border-gray-200 bg-white p-4 text-sm font-medium text-gray-700 shadow-lg">
                    <a href="{{ route('home') }}" class="hover:text-blue-700">Trang chủ</a>
                    <a href="{{ route('about') }}" class="hover:text-blue-700">Giới thiệu</a>
                    <a href="{{ route('services') }}" class="hover:text-blue-700">Dịch vụ</a>
                    <a href="{{ route('blog.index') }}" class="hover:text-blue-700">Tin tức</a>
                    <a href="{{ route('contact') }}" class="hover:text-blue-700">Liên hệ</a>
                    @auth
                        <a href="{{ route('profile.edit') }}" class="hover:text-blue-700">Tài khoản</a>
                    @else
                        <a href="{{ route('login') }}" class="hover:text-blue-700">Đăng nhập</a>
                    @endauth
                </nav>
            </details>
        </div>
    </header>

    <main>
        @yield('content')
    </main>

    <footer class="bg-gray-950 text-white">
        <div class="mx-auto grid max-w-7xl gap-10 px-4 py-12 sm:px-6 md:grid-cols-4 lg:px-8">
            <div class="md:col-span-2">
                <h2 class="text-xl font-bold">Digital Signature</h2>
                <p class="mt-4 max-w-xl text-sm leading-6 text-gray-300">
                    Cung cấp giải pháp chữ ký số, hóa đơn điện tử và hợp đồng điện tử cho cá nhân, hộ kinh doanh và doanh nghiệp.
                </p>
            </div>

            <div>
                <h3 class="font-semibold">Dịch vụ</h3>
                <ul class="mt-4 space-y-2 text-sm text-gray-300">
                    <li>Chữ ký số cá nhân</li>
                    <li>Chữ ký số doanh nghiệp</li>
                    <li>Hóa đơn điện tử</li>
                    <li>Hợp đồng điện tử</li>
                </ul>
            </div>

            <div>
                <h3 class="font-semibold">Liên hệ</h3>
                <ul class="mt-4 space-y-2 text-sm text-gray-300">
                    <li>Hotline: 0900 000 000</li>
                    <li>Email: support@example.com</li>
                    <li>Thời gian: 8:00 - 17:30</li>
                </ul>
            </div>
        </div>

        <div class="border-t border-white/10 px-4 py-5 text-center text-sm text-gray-400">
            © {{ date('Y') }} Digital Signature. All rights reserved.
        </div>
    </footer>
</body>

</html>
