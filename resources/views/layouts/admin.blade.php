<!DOCTYPE html>
<html lang="vi">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title')</title>
    <link rel="icon" type="image/jpeg" href="{{ asset('images/logo.jpg') }}">
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

    @vite(['resources/css/app.css', 'resources/js/app.js'])

</head>

<body
    x-data="adminShell()"
    x-on:keydown.escape.window="closeSidebar()"
    class="bg-slate-50 text-slate-900 antialiased dark:bg-slate-950 dark:text-slate-100"
>

<x-ui.toast-container />

<div
    x-show="sidebarOpen"
    x-cloak
    x-transition.opacity.duration.200ms
    class="fixed inset-0 z-40 bg-slate-950/50 backdrop-blur-sm md:hidden"
    x-on:click="closeSidebar()"
    aria-hidden="true"
></div>

<div
    id="admin-mobile-sidebar"
    x-show="sidebarOpen"
    x-cloak
    x-transition:enter="duration-300 ease-out"
    x-transition:enter-start="-translate-x-full"
    x-transition:enter-end="translate-x-0"
    x-transition:leave="duration-200 ease-in"
    x-transition:leave-start="translate-x-0"
    x-transition:leave-end="-translate-x-full"
    class="fixed inset-y-0 left-0 z-50 md:hidden"
>
    @include('partials.sidebar', ['drawer' => true])
</div>

<div class="flex min-h-screen min-w-0">

    @include('partials.sidebar', ['drawer' => false])

    <div class="min-w-0 flex-1">

        @include('partials.navbar')

        <main class="ui-page-enter p-4 sm:p-6 lg:p-8">
            <div class="mx-auto w-full max-w-screen-2xl">

                @yield('content')

            </div>

        </main>

    </div>

</div>

</body>

</html>
