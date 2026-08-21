@props([
    'variant' => 'primary',
    'size' => 'md',
    'href' => null,
    'type' => 'button',
    'loading' => false,
    'disabled' => false,
    'full' => false,
])

@php
    $variants = [
        'primary' => 'border-transparent bg-red-600 text-white shadow-sm hover:bg-red-700 active:bg-red-800 dark:bg-red-500 dark:hover:bg-red-400',
        'secondary' => 'border-slate-300 bg-white text-slate-700 shadow-sm hover:border-red-300 hover:bg-red-50 hover:text-red-700 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:border-red-400 dark:hover:bg-slate-800 dark:hover:text-red-200',
        'danger' => 'border-transparent bg-red-600 text-white shadow-sm hover:bg-red-700 active:bg-red-800 dark:bg-red-500 dark:hover:bg-red-400',
        'success' => 'border-transparent bg-green-600 text-white shadow-sm hover:bg-green-700 active:bg-green-800 dark:bg-green-500 dark:hover:bg-green-400',
        'warning' => 'border-transparent bg-amber-500 text-white shadow-sm hover:bg-amber-600 active:bg-amber-700 dark:bg-amber-500 dark:hover:bg-amber-400',
        'dark' => 'border-transparent bg-slate-900 text-white shadow-sm hover:bg-slate-800 active:bg-slate-950 dark:bg-slate-100 dark:text-slate-950 dark:hover:bg-white',
        'ghost' => 'border-transparent bg-transparent text-slate-600 hover:bg-amber-50 hover:text-slate-950 dark:text-slate-300 dark:hover:bg-slate-800 dark:hover:text-white',
    ];

    $sizes = [
        'xs' => 'gap-1.5 rounded-lg px-3 py-2 text-xs',
        'sm' => 'gap-2 rounded-lg px-3.5 py-2 text-sm',
        'md' => 'gap-2 rounded-lg px-4 py-2.5 text-sm',
        'lg' => 'gap-2.5 rounded-xl px-5 py-3 text-base',
    ];

    $classes = implode(' ', [
        'ui-focus inline-flex items-center justify-center border font-semibold transition duration-200 ease-out disabled:cursor-not-allowed disabled:opacity-60',
        $variants[$variant] ?? $variants['primary'],
        $sizes[$size] ?? $sizes['md'],
        $full ? 'w-full' : '',
    ]);
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
        @if ($loading)
            <span class="h-4 w-4 animate-spin rounded-full border-2 border-current border-t-transparent"></span>
        @endif

        {{ $slot }}
    </a>
@else
    <button type="{{ $type }}" @disabled($loading || $disabled) {{ $attributes->merge(['class' => $classes]) }}>
        @if ($loading)
            <span class="h-4 w-4 animate-spin rounded-full border-2 border-current border-t-transparent"></span>
        @endif

        {{ $slot }}
    </button>
@endif
