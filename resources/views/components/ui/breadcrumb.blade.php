@props(['items' => []])

<nav {{ $attributes->merge(['class' => 'flex flex-wrap items-center gap-2 text-sm font-medium text-slate-500 dark:text-slate-400']) }} aria-label="Breadcrumb">
    @forelse ($items as $item)
        @if (! empty($item['url']) && ! $loop->last)
            <a href="{{ $item['url'] }}" class="transition hover:text-amber-700 dark:hover:text-amber-300">
                {{ $item['label'] }}
            </a>
            <span class="text-slate-300 dark:text-slate-700">/</span>
        @else
            <span class="{{ $loop->last ? 'text-slate-800 dark:text-slate-200' : '' }}">
                {{ $item['label'] }}
            </span>
            @unless ($loop->last)
                <span class="text-slate-300 dark:text-slate-700">/</span>
            @endunless
        @endif
    @empty
        {{ $slot }}
    @endforelse
</nav>
