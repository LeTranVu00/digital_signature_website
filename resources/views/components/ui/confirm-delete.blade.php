@props([
    'action',
    'method' => 'DELETE',
    'trigger' => 'Xoa',
    'title' => 'Xác nhận thao tác',
    'description' => 'Thao tác này cần được xác nhận trước khi tiếp tục.',
    'confirmText' => 'Xác nhận',
    'variant' => 'danger',
    'buttonVariant' => 'danger',
    'buttonSize' => 'xs',
    'triggerClass' => null,
    'disabled' => false,
])

@php
    $modalName = 'confirm-' . md5($action . $method . $title . $confirmText);
@endphp

<span x-data>
    <x-ui.button
        type="button"
        :variant="$buttonVariant"
        :size="$buttonSize"
        :disabled="$disabled"
        :class="$triggerClass"
        x-on:click="$dispatch('open-ui-modal', '{{ $modalName }}')"
    >
        {{ $trigger }}
    </x-ui.button>

    <x-ui.modal :name="$modalName" :title="$title" :description="$description" max-width="md">
        <form action="{{ $action }}" method="POST" class="p-6" data-confirm-modal-name="{{ $modalName }}">
            @csrf
            @method($method)

            {{ $slot }}

            <div class="mt-6 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                <x-ui.button
                    type="button"
                    variant="secondary"
                    x-on:click="$dispatch('close-ui-modal', '{{ $modalName }}')"
                >
                    Hủy
                </x-ui.button>

                <x-ui.button type="submit" :variant="$variant">
                    {{ $confirmText }}
                </x-ui.button>
            </div>
        </form>
    </x-ui.modal>
</span>
