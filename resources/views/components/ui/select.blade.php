@props([
    'name' => null,
    'label' => null,
    'error' => null,
    'helper' => null,
    'required' => false,
    'disabled' => false,
])

@php
    $id = $attributes->get('id') ?? $name;
    $messages = $error ?? ($name ? $errors->get($name) : []);
    $hasError = filled($messages);
    $errorId = $id ? $id . '-error' : null;
    $helperId = ($id && $helper) ? $id . '-helper' : null;
    $describedBy = trim(collect([$helperId, $hasError ? $errorId : null])->filter()->implode(' '));
    $classes = 'ui-control pr-10 ' . ($hasError ? 'ui-control-invalid' : '');
    $defaults = [
        'id' => $id,
        'class' => $classes,
    ];

    if ($name) {
        $defaults['name'] = $name;
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

    <select @required($required) @disabled($disabled) {{ $attributes->merge($defaults) }}>
        {{ $slot }}
    </select>

    <x-ui.form-error :messages="$messages" :id="$errorId" />

    @if ($helper)
        <p id="{{ $helperId }}" class="ui-helper">{{ $helper }}</p>
    @endif
</div>
