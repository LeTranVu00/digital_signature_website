@props([
    'variant' => 'primary',
    'size' => 'md',
    'loadingText' => 'Đang xử lý...',
])

<x-ui.button
    type="submit"
    :variant="$variant"
    :size="$size"
    x-bind:disabled="submitting"
>
    <span
        x-show="submitting"
        x-cloak
        class="h-4 w-4 animate-spin rounded-full border-2 border-current border-t-transparent"
    ></span>
    <span x-show="! submitting">{{ $slot }}</span>
    <span x-show="submitting" x-cloak>{{ $loadingText }}</span>
</x-ui.button>
