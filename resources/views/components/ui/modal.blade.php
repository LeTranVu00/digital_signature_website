@props([
    'name',
    'show' => false,
    'maxWidth' => 'lg',
    'title' => null,
    'description' => null,
    'panelClass' => '',
    'sessionKey' => null,
])

@php
    $maxWidthClass = [
        'sm' => 'sm:max-w-sm',
        'md' => 'sm:max-w-md',
        'lg' => 'sm:max-w-lg',
        'xl' => 'sm:max-w-xl',
        '2xl' => 'sm:max-w-2xl',
    ][$maxWidth] ?? 'sm:max-w-lg';

    $titleId = $name . '-title';
    $descriptionId = $name . '-description';
@endphp

<div
    x-data="uiModal({ name: @js($name), show: @js($show), sessionKey: @js($sessionKey) })"
    x-on:open-ui-modal.window="openFromEvent($event)"
    x-on:close-ui-modal.window="closeFromEvent($event)"
    x-on:keydown.escape.window="show ? close() : null"
    x-show="show"
    x-cloak
    class="fixed inset-0 z-[70] overflow-y-auto px-4 py-6 sm:px-0"
    role="dialog"
    aria-modal="true"
    @if ($title)
        aria-labelledby="{{ $titleId }}"
    @endif
    @if ($description)
        aria-describedby="{{ $descriptionId }}"
    @endif
>
    <div
        x-show="show"
        x-transition.opacity.duration.200ms
        class="fixed inset-0 bg-slate-950/50 backdrop-blur-sm"
        x-on:click="close()"
    ></div>

    <div
        x-ref="panel"
        tabindex="-1"
        x-show="show"
        x-on:keydown.tab="trapTab($event)"
        x-transition:enter="duration-200 ease-out"
        x-transition:enter-start="translate-y-3 opacity-0 sm:scale-95"
        x-transition:enter-end="translate-y-0 opacity-100 sm:scale-100"
        x-transition:leave="duration-150 ease-in"
        x-transition:leave-start="translate-y-0 opacity-100 sm:scale-100"
        x-transition:leave-end="translate-y-3 opacity-0 sm:scale-95"
        class="relative mx-auto mb-6 mt-10 w-full {{ $maxWidthClass }} overflow-hidden rounded-xl bg-white shadow-2xl ring-1 ring-slate-900/10 dark:bg-slate-900 dark:ring-white/10 {{ $panelClass }}"
    >
        @if ($title || $description)
            <div class="border-b border-slate-200 px-6 py-5 dark:border-slate-800">
                @if ($title)
                    <h2 id="{{ $titleId }}" class="text-lg font-bold text-slate-950 dark:text-white">{{ $title }}</h2>
                @endif

                @if ($description)
                    <p id="{{ $descriptionId }}" class="mt-1 text-sm leading-6 text-slate-500 dark:text-slate-400">{{ $description }}</p>
                @endif
            </div>
        @endif

        {{ $slot }}
    </div>
</div>
