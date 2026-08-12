<button {{ $attributes->merge(['type' => 'submit', 'class' => 'ui-focus inline-flex items-center justify-center gap-2 rounded-lg border border-transparent bg-amber-400 px-4 py-2.5 text-sm font-semibold text-slate-950 shadow-sm transition duration-200 ease-out hover:bg-amber-300 active:bg-amber-500 disabled:cursor-not-allowed disabled:opacity-60 dark:bg-amber-400 dark:text-slate-950 dark:hover:bg-amber-300 dark:active:bg-amber-500']) }}>
    {{ $slot }}
</button>
