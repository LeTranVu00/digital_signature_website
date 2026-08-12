@props(['text' => 'Dang tai...'])

<div {{ $attributes->merge(['class' => 'flex items-center justify-center gap-3 rounded-xl border border-dashed border-slate-300 bg-slate-50 px-6 py-10 text-sm font-semibold text-slate-500 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-400']) }}>
    <span class="h-5 w-5 animate-spin rounded-full border-2 border-amber-500 border-t-transparent"></span>
    <span>{{ $text }}</span>
</div>
