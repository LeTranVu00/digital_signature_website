@props([
    'label',
    'value',
    'variant' => 'primary',
])

@php
    $variants = [
        'primary' => 'bg-amber-50 text-amber-800 ring-amber-100 dark:bg-amber-500/10 dark:text-amber-200 dark:ring-amber-500/20',
        'success' => 'bg-green-50 text-green-700 ring-green-100 dark:bg-green-500/10 dark:text-green-200 dark:ring-green-500/20',
        'warning' => 'bg-amber-50 text-amber-700 ring-amber-100 dark:bg-amber-500/10 dark:text-amber-200 dark:ring-amber-500/20',
        'danger' => 'bg-red-50 text-red-700 ring-red-100 dark:bg-red-500/10 dark:text-red-200 dark:ring-red-500/20',
        'info' => 'bg-cyan-50 text-cyan-700 ring-cyan-100 dark:bg-cyan-500/10 dark:text-cyan-200 dark:ring-cyan-500/20',
        'neutral' => 'bg-slate-50 text-slate-700 ring-slate-100 dark:bg-slate-800 dark:text-slate-200 dark:ring-slate-700',
    ];
@endphp

<x-ui.card hover class="overflow-hidden">
    <div class="flex items-start justify-between gap-4">
        <div class="min-w-0">
            <p class="truncate text-sm font-semibold text-slate-500 dark:text-slate-400">{{ $label }}</p>
            <p class="mt-3 text-3xl font-bold tracking-normal text-slate-950 dark:text-white">
                {{ $value }}
            </p>
        </div>

        <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl ring-1 {{ $variants[$variant] ?? $variants['primary'] }}">
            {{ $icon }}
        </div>
    </div>
</x-ui.card>
