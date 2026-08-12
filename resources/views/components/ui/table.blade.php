@props(['empty' => false])

<div {{ $attributes->merge(['class' => 'ui-card overflow-hidden']) }}>
    <div class="border-b border-slate-200 bg-slate-50 px-4 py-2 text-xs font-medium text-slate-500 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-400 sm:hidden">
        Vuốt ngang để xem thêm
    </div>

    <div class="overflow-x-auto">
        <table class="ui-table">
            @isset($head)
                <thead>
                    {{ $head }}
                </thead>
            @endisset

            <tbody class="divide-y divide-slate-100 bg-white dark:divide-slate-800 dark:bg-slate-900">
                {{ $slot }}
            </tbody>
        </table>
    </div>
</div>
