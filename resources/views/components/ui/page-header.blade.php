@props([
    'title',
    'description' => null,
])

<div {{ $attributes->merge(['class' => 'mb-6 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between']) }}>
    <div>
        @isset($breadcrumb)
            <div class="mb-3">
                {{ $breadcrumb }}
            </div>
        @endisset

        <h2 class="text-2xl font-bold tracking-normal text-slate-950 dark:text-white sm:text-3xl">
            {{ $title }}
        </h2>

        @if ($description)
            <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-500 dark:text-slate-400">
                {{ $description }}
            </p>
        @endif
    </div>

    @isset($actions)
        <div class="flex flex-wrap items-center gap-2 sm:justify-end">
            {{ $actions }}
        </div>
    @endisset
</div>
