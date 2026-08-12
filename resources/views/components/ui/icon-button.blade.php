@props([
    'variant' => 'secondary',
    'size' => 'md',
    'href' => null,
    'type' => 'button',
    'label' => null,
])

@php
    $variants = [
        'primary' => 'border-transparent bg-amber-400 text-slate-950 hover:bg-amber-300 dark:bg-amber-400 dark:text-slate-950 dark:hover:bg-amber-300',
        'secondary' => 'border-slate-300 bg-white text-slate-600 hover:border-amber-300 hover:bg-amber-50 hover:text-slate-950 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:border-amber-400 dark:hover:bg-slate-800 dark:hover:text-amber-200',
        'danger' => 'border-transparent bg-red-600 text-white hover:bg-red-700 dark:bg-red-500 dark:hover:bg-red-400',
        'ghost' => 'border-transparent bg-transparent text-slate-500 hover:bg-slate-100 hover:text-slate-900 dark:text-slate-300 dark:hover:bg-slate-800 dark:hover:text-white',
    ];

    $sizes = [
        'sm' => 'h-8 w-8 rounded-lg',
        'md' => 'h-10 w-10 rounded-lg',
        'lg' => 'h-11 w-11 rounded-xl',
    ];

    $classes = implode(' ', [
        'ui-focus inline-flex shrink-0 items-center justify-center border shadow-sm transition duration-200 ease-out disabled:cursor-not-allowed disabled:opacity-60',
        $variants[$variant] ?? $variants['secondary'],
        $sizes[$size] ?? $sizes['md'],
    ]);
@endphp

@if ($href)
    <a href="{{ $href }}" aria-label="{{ $label }}" title="{{ $label }}" {{ $attributes->merge(['class' => $classes]) }}>
        {{ $slot }}
    </a>
@else
    <button type="{{ $type }}" aria-label="{{ $label }}" title="{{ $label }}" {{ $attributes->merge(['class' => $classes]) }}>
        {{ $slot }}
    </button>
@endif
