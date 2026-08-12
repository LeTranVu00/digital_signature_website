@props([
    'variant' => 'neutral',
    'size' => 'sm',
])

@php
    $variants = [
        'primary' => 'bg-amber-100 text-amber-800 ring-amber-200 dark:bg-amber-500/10 dark:text-amber-200 dark:ring-amber-500/20',
        'info' => 'bg-cyan-100 text-cyan-800 ring-cyan-200 dark:bg-cyan-500/10 dark:text-cyan-200 dark:ring-cyan-500/20',
        'success' => 'bg-green-100 text-green-700 ring-green-200 dark:bg-green-500/10 dark:text-green-200 dark:ring-green-500/20',
        'warning' => 'bg-amber-100 text-amber-700 ring-amber-200 dark:bg-amber-500/10 dark:text-amber-200 dark:ring-amber-500/20',
        'danger' => 'bg-red-100 text-red-700 ring-red-200 dark:bg-red-500/10 dark:text-red-200 dark:ring-red-500/20',
        'dark' => 'bg-slate-900 text-white ring-slate-900 dark:bg-slate-100 dark:text-slate-950 dark:ring-slate-100',
        'neutral' => 'bg-slate-100 text-slate-700 ring-slate-200 dark:bg-slate-800 dark:text-slate-200 dark:ring-slate-700',
        'admin' => 'bg-indigo-100 text-indigo-700 ring-indigo-200 dark:bg-indigo-500/10 dark:text-indigo-200 dark:ring-indigo-500/20',
        'draft' => 'bg-amber-100 text-amber-700 ring-amber-200 dark:bg-amber-500/10 dark:text-amber-200 dark:ring-amber-500/20',
        'published' => 'bg-green-100 text-green-700 ring-green-200 dark:bg-green-500/10 dark:text-green-200 dark:ring-green-500/20',
        'blocked' => 'bg-red-100 text-red-700 ring-red-200 dark:bg-red-500/10 dark:text-red-200 dark:ring-red-500/20',
        'active' => 'bg-green-100 text-green-700 ring-green-200 dark:bg-green-500/10 dark:text-green-200 dark:ring-green-500/20',
    ];

    $sizes = [
        'xs' => 'px-2 py-0.5 text-[11px]',
        'sm' => 'px-2.5 py-1 text-xs',
        'md' => 'px-3 py-1.5 text-sm',
    ];
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center rounded-full font-semibold ring-1 ring-inset ' . ($variants[$variant] ?? $variants['neutral']) . ' ' . ($sizes[$size] ?? $sizes['sm'])]) }}>
    {{ $slot }}
</span>
