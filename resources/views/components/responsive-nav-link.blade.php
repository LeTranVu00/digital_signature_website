@props(['active'])

@php
$classes = ($active ?? false)
            ? 'block w-full ps-3 pe-4 py-2 border-l-4 border-amber-500 text-start text-base font-medium text-amber-800 bg-amber-50 focus:outline-none focus:text-amber-900 focus:bg-amber-100 focus:border-amber-700 transition duration-150 ease-in-out dark:bg-amber-500/10 dark:text-amber-200 dark:border-amber-400 dark:focus:bg-amber-500/15 dark:focus:text-amber-100'
            : 'block w-full ps-3 pe-4 py-2 border-l-4 border-transparent text-start text-base font-medium text-slate-600 hover:text-slate-800 hover:bg-slate-50 hover:border-slate-300 focus:outline-none focus:text-slate-800 focus:bg-slate-50 focus:border-slate-300 transition duration-150 ease-in-out dark:text-slate-300 dark:hover:text-white dark:hover:bg-slate-800 dark:hover:border-slate-600 dark:focus:text-white dark:focus:bg-slate-800 dark:focus:border-slate-600';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
