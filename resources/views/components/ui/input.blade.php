@props([
    'name' => null,
    'label' => null,
    'value' => null,
    'error' => null,
    'helper' => null,
    'required' => false,
    'disabled' => false,
])

@php
    $id = $attributes->get('id') ?? $name;
    $type = $attributes->get('type', 'text');
    $messages = $error ?? ($name ? $errors->get($name) : []);
    $hasError = filled($messages);
    $errorId = $id ? $id . '-error' : null;
    $helperId = ($id && $helper) ? $id . '-helper' : null;
    $describedBy = trim(collect([$helperId, $hasError ? $errorId : null])->filter()->implode(' '));
    $fileClasses = $type === 'file'
        ? ' p-0 text-slate-600 file:mr-4 file:border-0 file:bg-amber-400 file:px-4 file:py-3 file:text-sm file:font-semibold file:text-slate-950 hover:file:bg-amber-300 dark:text-slate-300 dark:file:bg-amber-400 dark:hover:file:bg-amber-300'
        : '';
    $classes = 'ui-control ' . $fileClasses . ' ' . ($hasError ? 'ui-control-invalid' : '');
    $defaults = [
        'id' => $id,
        'type' => $type,
        'class' => $classes,
    ];

    if ($name) {
        $defaults['name'] = $name;
    }

    if ($type !== 'file' && ! is_null($value)) {
        $defaults['value'] = $value;
    }

    if ($hasError) {
        $defaults['aria-invalid'] = 'true';
    }

    if ($describedBy !== '') {
        $defaults['aria-describedby'] = $describedBy;
    }
@endphp

<div>
    @if ($label)
        <label for="{{ $id }}" class="ui-label">
            {{ $label }}
            @if ($required)
                <span class="text-red-500">*</span>
            @endif
        </label>
    @endif

    <input @required($required) @disabled($disabled) {{ $attributes->merge($defaults) }}>

    <x-ui.form-error :messages="$messages" :id="$errorId" />

    @if ($helper)
        <p id="{{ $helperId }}" class="ui-helper">{{ $helper }}</p>
    @endif
</div>
