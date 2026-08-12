@props(['paginator'])

@if ($paginator->hasPages())
    <div {{ $attributes->merge(['class' => 'mt-6 rounded-xl border border-slate-200 bg-white px-4 py-3 shadow-sm dark:border-slate-800 dark:bg-slate-900 dark:shadow-slate-950/30']) }}>
        {{ $paginator->links() }}
    </div>
@endif
