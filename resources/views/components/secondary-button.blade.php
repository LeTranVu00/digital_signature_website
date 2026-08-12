<button {{ $attributes->merge(['type' => 'button', 'class' => 'ui-focus inline-flex items-center justify-center gap-2 rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition duration-200 ease-out hover:border-amber-300 hover:bg-amber-50 hover:text-slate-950 disabled:cursor-not-allowed disabled:opacity-60 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:border-amber-400 dark:hover:bg-slate-800 dark:hover:text-amber-200']) }}>
    {{ $slot }}
</button>
