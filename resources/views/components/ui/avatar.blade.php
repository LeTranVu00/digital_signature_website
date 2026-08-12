@props([
    'user' => null,
    'size' => 'md',
])

@php
    $sizes = [
        'sm' => 'h-8 w-8 text-xs',
        'md' => 'h-10 w-10 text-sm',
        'lg' => 'h-12 w-12 text-base',
    ];

    $name = trim((string) ($user?->name ?? 'User'));
    $parts = collect(preg_split('/\s+/u', $name))->filter()->take(2);
    $initials = $parts
        ->map(fn ($part) => mb_strtoupper(mb_substr($part, 0, 1, 'UTF-8'), 'UTF-8'))
        ->implode('') ?: 'U';
@endphp

@if ($user?->avatar)
    <img
        src="{{ $user->avatar }}"
        alt="{{ $name }}"
        {{ $attributes->merge(['class' => ($sizes[$size] ?? $sizes['md']) . ' rounded-full object-cover ring-2 ring-white dark:ring-slate-800']) }}
    >
@else
    <span {{ $attributes->merge(['class' => ($sizes[$size] ?? $sizes['md']) . ' inline-flex shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-slate-950 to-amber-700 font-bold text-white shadow-sm ring-2 ring-white dark:ring-slate-800']) }}>
        {{ $initials }}
    </span>
@endif
