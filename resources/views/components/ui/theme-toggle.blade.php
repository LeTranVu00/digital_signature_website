@props(['label' => 'Đổi chế độ sáng tối'])

<button
    type="button"
    x-data="themeToggle()"
    x-on:click="toggle()"
    class="ui-focus inline-flex h-10 w-10 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-700 shadow-sm transition duration-200 ease-out hover:border-amber-200 hover:bg-amber-50 hover:text-amber-700 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:border-amber-500 dark:hover:bg-slate-800 dark:hover:text-amber-200"
    x-bind:aria-label="dark ? 'Chuyển sang chế độ sáng' : 'Chuyển sang chế độ tối'"
    x-bind:title="dark ? 'Chuyển sang chế độ sáng' : 'Chuyển sang chế độ tối'"
>
    <svg x-show="! dark" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2m0 14v2m9-9h-2M5 12H3m15.36-6.36-1.42 1.42M7.06 16.94l-1.42 1.42m12.72 0-1.42-1.42M7.06 7.06 5.64 5.64" />
        <circle cx="12" cy="12" r="4" />
    </svg>

    <svg x-show="dark" x-cloak class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M21 12.8A8.5 8.5 0 1 1 11.2 3a6.5 6.5 0 0 0 9.8 9.8Z" />
    </svg>
</button>
