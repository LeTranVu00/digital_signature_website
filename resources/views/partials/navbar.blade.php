<header class="sticky top-0 z-30 border-b border-slate-200 bg-white/90 backdrop-blur dark:border-slate-800 dark:bg-slate-950/90">
    <div class="flex min-h-16 items-center justify-between gap-3 px-4 py-3 sm:px-6 lg:px-8">
        <div class="flex min-w-0 items-center gap-3">
            <button
                type="button"
                class="ui-focus inline-flex h-10 w-10 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-700 shadow-sm transition hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800 md:hidden"
                x-on:click="openSidebar()"
                x-bind:aria-expanded="sidebarOpen.toString()"
                aria-controls="admin-mobile-sidebar"
                aria-label="Mở menu quản trị"
            >
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
            </button>

            <div class="min-w-0">
                <p class="text-xs font-semibold uppercase text-blue-600 dark:text-blue-300">Admin</p>
                <h1 class="truncate text-lg font-bold text-slate-950 dark:text-white sm:text-xl">
                    @yield('title')
                </h1>
            </div>
        </div>

        <div class="flex min-w-0 items-center gap-2 sm:gap-3">
            <x-ui.theme-toggle />
            <x-ui.user-menu :user="auth()->user()" />
        </div>
    </div>
</header>
