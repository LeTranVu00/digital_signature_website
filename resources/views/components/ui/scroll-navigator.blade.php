<div
    x-data="scrollNavigator()"
    x-show="visible"
    x-cloak
    x-transition:enter="duration-200 ease-out"
    x-transition:enter-start="translate-y-3 opacity-0"
    x-transition:enter-end="translate-y-0 opacity-100"
    x-transition:leave="duration-150 ease-in"
    x-transition:leave-start="translate-y-0 opacity-100"
    x-transition:leave-end="translate-y-3 opacity-0"
    class="fixed bottom-4 right-4 z-50 flex flex-col items-end sm:bottom-6 sm:right-6"
>
    <div
        id="scroll-navigator-menu"
        x-show="open"
        x-transition:enter="duration-200 ease-out"
        x-transition:enter-start="translate-y-2 opacity-0"
        x-transition:enter-end="translate-y-0 opacity-100"
        x-transition:leave="duration-150 ease-in"
        x-transition:leave-start="translate-y-0 opacity-100"
        x-transition:leave-end="translate-y-2 opacity-0"
        x-on:click.outside="close()"
        class="mb-3 w-[min(calc(100vw-2rem),21rem)] overflow-hidden rounded-2xl border border-amber-100/90 bg-gradient-to-b from-amber-50/95 via-white/98 to-sky-50/80 shadow-[0_28px_90px_-42px_rgb(15_23_42/0.75)] ring-1 ring-white/70 backdrop-blur-xl dark:border-slate-800 dark:from-slate-900 dark:via-slate-900 dark:to-slate-950 dark:ring-white/10"
    >
        <div class="border-b border-amber-100/80 bg-white/60 px-5 py-4 dark:border-slate-800 dark:bg-slate-900/70">
            <p class="text-base font-extrabold text-slate-950 dark:text-white">Điều hướng đề mục</p>
            <p class="mt-1 text-sm font-medium leading-5 text-slate-500 dark:text-slate-400">Chọn nhanh theo từng vùng nội dung.</p>
        </div>

        <nav class="max-h-80 overflow-y-auto p-2.5" aria-label="Điều hướng đề mục">
            <button
                type="button"
                class="ui-focus flex w-full items-center gap-3 rounded-xl px-3 py-3 text-left text-sm font-bold text-slate-700 transition hover:bg-white/85 hover:text-red-600 hover:shadow-sm dark:text-slate-200 dark:hover:bg-slate-800 dark:hover:text-amber-200"
                x-on:click="scrollToTop()"
            >
                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-slate-100 text-slate-600 ring-1 ring-slate-200 dark:bg-slate-800 dark:text-slate-300">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 19V5m0 0-6 6m6-6 6 6" />
                    </svg>
                </span>
                Đầu trang
            </button>

            <template x-for="section in sections" :key="section.id">
                <button
                    type="button"
                    class="ui-focus mt-1 flex w-full items-center gap-3 rounded-xl px-3 py-3 text-left text-sm font-bold transition"
                    x-bind:class="activeId === section.id ? 'bg-gradient-to-r from-amber-50 via-white to-red-50 text-red-700 shadow-sm ring-1 ring-amber-100 dark:bg-amber-500/10 dark:text-amber-200 dark:ring-amber-500/20' : 'text-slate-700 hover:bg-white/85 hover:text-red-600 hover:shadow-sm dark:text-slate-200 dark:hover:bg-slate-800 dark:hover:text-amber-200'"
                    x-on:click="scrollToSection(section.id)"
                >
                    <span class="min-w-0 truncate" x-text="section.label"></span>
                </button>
            </template>
        </nav>
    </div>

    <button
        type="button"
        class="ui-focus flex h-12 w-12 self-end items-center justify-center rounded-full bg-gradient-to-br from-slate-950 via-slate-900 to-red-950 text-amber-300 shadow-xl shadow-slate-950/30 ring-1 ring-amber-300/40 transition duration-200 ease-out hover:-translate-y-0.5 hover:text-amber-200"
        x-on:click="toggle()"
        x-bind:aria-expanded="open.toString()"
        aria-controls="scroll-navigator-menu"
        aria-label="Mở điều hướng đề mục"
    >
        <svg class="h-5 w-5 transition duration-200" x-bind:class="open ? '-rotate-90' : ''" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 19V5m0 0-6 6m6-6 6 6" />
        </svg>
    </button>
</div>
