<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

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

        <div class="flex min-h-screen flex-col items-center bg-slate-50 px-4 py-8 dark:bg-slate-950 sm:justify-center sm:px-6">
            <div class="fixed right-4 top-4">
                <x-ui.theme-toggle />
            </div>

            <div class="mb-6 text-center">
                <a href="/" class="inline-flex items-center gap-3">
                    <span class="flex h-12 w-12 items-center justify-center rounded-xl bg-blue-600 text-base font-bold text-white shadow-lg shadow-blue-200 dark:shadow-blue-950/40">
                        DS
                    </span>
                    <span class="text-xl font-bold text-slate-950 dark:text-white">Digital Signature</span>
                </a>
            </div>

            <div class="w-full max-w-md overflow-hidden rounded-xl border border-slate-200 bg-white p-5 shadow-xl shadow-slate-200/60 dark:border-slate-800 dark:bg-slate-900 dark:shadow-slate-950/60 sm:p-6">
                {{ $slot }}
            </div>
        </div>
    </body>
</html>
