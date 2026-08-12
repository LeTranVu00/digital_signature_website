@props([
    'title' => null,
    'description' => null,
    'padding' => 'md',
    'hover' => false,
])

@php
    $paddings = [
        'none' => '',
        'sm' => 'p-4',
        'md' => 'p-5 sm:p-6',
        'lg' => 'p-6 sm:p-8',
    ];

    $classes = trim('ui-card ' . ($paddings[$padding] ?? $paddings['md']) . ' ' . ($hover ? 'ui-card-hover' : ''));
@endphp

<section {{ $attributes->merge(['class' => $classes]) }}>
    @if ($title || $description || isset($actions))
        <div class="mb-5 flex flex-wrap items-start justify-between gap-4">
            <div>
                @if ($title)
                    <h3 class="text-base font-bold text-slate-950 dark:text-white sm:text-lg">{{ $title }}</h3>
                @endif

                @if ($description)
                    <p class="mt-1 text-sm leading-6 text-slate-500 dark:text-slate-400">{{ $description }}</p>
                @endif
            </div>

            @isset($actions)
                <div class="flex flex-wrap items-center gap-2">
                    {{ $actions }}
                </div>
            @endisset
        </div>
    @endif

    {{ $slot }}
</section>
