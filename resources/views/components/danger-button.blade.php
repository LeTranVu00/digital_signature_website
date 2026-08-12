<button {{ $attributes->merge(['type' => 'submit', 'class' => 'ui-focus inline-flex items-center justify-center gap-2 rounded-lg border border-transparent bg-red-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition duration-200 ease-out hover:bg-red-700 active:bg-red-800 disabled:cursor-not-allowed disabled:opacity-60 dark:bg-red-500 dark:hover:bg-red-400']) }}>
    {{ $slot }}
</button>
