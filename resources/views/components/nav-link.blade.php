@props(['active'])

@php
$classes = ($active ?? false)
            ? 'inline-flex items-center px-1 pt-1 border-b-2 border-amber-500 text-sm font-medium leading-5 text-slate-900 focus:outline-none focus:border-amber-700 transition duration-150 ease-in-out dark:text-white dark:border-amber-400 dark:focus:border-amber-300'
            : 'inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm font-medium leading-5 text-slate-500 hover:text-slate-700 hover:border-slate-300 focus:outline-none focus:text-slate-700 focus:border-slate-300 transition duration-150 ease-in-out dark:text-slate-300 dark:hover:text-white dark:hover:border-slate-600 dark:focus:text-white dark:focus:border-slate-600';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
