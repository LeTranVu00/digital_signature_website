@props([
    'type' => 'info',
    'title' => null,
    'message' => null,
    'duration' => 5200,
])

@php
    $message = $message ?? session($type);

    $config = [
        'success' => [
            'title' => $title ?? 'Thành công',
            'classes' => 'border-green-200 bg-white text-green-900 dark:border-green-500/30 dark:bg-slate-900 dark:text-green-100',
            'iconWrap' => 'bg-green-100 text-green-700 dark:bg-green-500/10 dark:text-green-200',
            'bar' => 'bg-green-500',
            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M20 6 9 17l-5-5" />',
        ],
        'error' => [
            'title' => $title ?? 'Có lỗi xảy ra',
            'classes' => 'border-red-200 bg-white text-red-900 dark:border-red-500/30 dark:bg-slate-900 dark:text-red-100',
            'iconWrap' => 'bg-red-100 text-red-700 dark:bg-red-500/10 dark:text-red-200',
            'bar' => 'bg-red-500',
            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 8v5m0 4h.01M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0Z" />',
        ],
        'warning' => [
            'title' => $title ?? 'Cần chú ý',
            'classes' => 'border-amber-200 bg-white text-amber-900 dark:border-amber-500/30 dark:bg-slate-900 dark:text-amber-100',
            'iconWrap' => 'bg-amber-100 text-amber-700 dark:bg-amber-500/10 dark:text-amber-200',
            'bar' => 'bg-amber-500',
            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 9v4m0 4h.01M12 3 2 21h20L12 3Z" />',
        ],
        'info' => [
            'title' => $title ?? 'Thông tin',
            'classes' => 'border-cyan-200 bg-white text-cyan-900 dark:border-cyan-500/30 dark:bg-slate-900 dark:text-cyan-100',
            'iconWrap' => 'bg-cyan-100 text-cyan-700 dark:bg-cyan-500/10 dark:text-cyan-200',
            'bar' => 'bg-cyan-500',
            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 16v-4m0-4h.01M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />',
        ],
    ];

    $toast = $config[$type] ?? $config['info'];
@endphp

@if ($message)
    <div
        x-data="uiToast({{ (int) $duration }})"
        x-init="start()"
        x-show="show"
        x-on:mouseenter="pause()"
        x-on:mouseleave="resume()"
        x-on:focusin="pause()"
        x-on:focusout="resume()"
        x-transition:enter="duration-250 ease-out"
        x-transition:enter-start="translate-y-2 scale-[0.97] opacity-0 sm:translate-x-3 sm:translate-y-0"
        x-transition:enter-end="translate-y-0 scale-100 opacity-100 sm:translate-x-0"
        x-transition:leave="duration-180 ease-in"
        x-transition:leave-start="translate-y-0 scale-100 opacity-100 sm:translate-x-0"
        x-transition:leave-end="-translate-y-1 scale-[0.98] opacity-0 sm:translate-x-2 sm:translate-y-0"
        {{ $attributes->merge(['class' => 'ui-toast pointer-events-auto relative w-full overflow-hidden rounded-lg border p-3.5 pr-10 ' . $toast['classes']]) }}
        role="{{ $type === 'error' ? 'alert' : 'status' }}"
    >
        <div class="flex gap-3">
            <span class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-full {{ $toast['iconWrap'] }}">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    {!! $toast['icon'] !!}
                </svg>
            </span>

            <div class="min-w-0">
                <p class="text-sm font-bold text-slate-950 dark:text-white">{{ $toast['title'] }}</p>
                <p class="mt-1 text-sm leading-5 text-slate-600 dark:text-slate-300">{{ $message }}</p>
            </div>
        </div>

        <button
            type="button"
            class="ui-focus absolute right-2.5 top-2.5 rounded-md p-1.5 text-slate-400 transition hover:bg-slate-100 hover:text-slate-700 dark:hover:bg-slate-800 dark:hover:text-slate-200"
            x-on:click="close()"
            aria-label="Đóng thông báo"
        >
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 6l12 12M18 6 6 18" />
            </svg>
        </button>

        <div class="absolute bottom-0 left-0 h-0.5 w-full bg-slate-100 dark:bg-slate-800">
            <div
                x-ref="progress"
                class="h-full origin-left {{ $toast['bar'] }}"
                style="animation: ui-toast-progress {{ (int) $duration }}ms linear forwards;"
            ></div>
        </div>
    </div>
@endif
