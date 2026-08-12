@props([
    'title' => null,
    'description' => null,
    'icon' => null,
])

<div {{ $attributes->merge(['class' => 'rounded-xl border border-dashed border-slate-300 bg-slate-50/80 px-6 py-10 text-center dark:border-slate-700 dark:bg-slate-900/70']) }}>
    @if ($icon)
        <div class="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-full bg-white text-amber-700 shadow-sm dark:bg-slate-800 dark:text-amber-300">
            {!! $icon !!}
        </div>
    @endif

    @if ($title)
        <h3 class="text-sm font-bold text-slate-900 dark:text-white">{{ $title }}</h3>
    @endif

    @if ($description)
        <p class="mx-auto mt-2 max-w-md text-sm leading-6 text-slate-500 dark:text-slate-400">{{ $description }}</p>
    @else
        <div class="text-sm leading-6 text-slate-500 dark:text-slate-400">{{ $slot }}</div>
    @endif

    @isset($actions)
        <div class="mt-5 flex justify-center gap-2">
            {{ $actions }}
        </div>
    @endisset
</div>
