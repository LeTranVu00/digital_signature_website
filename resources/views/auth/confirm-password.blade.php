<x-guest-layout>
    <p class="mb-5 text-sm leading-6 text-slate-600 dark:text-slate-300">
        {{ __('This is a secure area of the application. Please confirm your password before continuing.') }}
    </p>

    <form method="POST" action="{{ route('password.confirm') }}" x-data="{ submitting: false }" x-on:submit="submitting = true">
        @csrf

        <x-ui.input
            type="password"
            name="password"
            label="Password"
            required
            autocomplete="current-password"
        />

        <div class="mt-5 flex justify-end">
            <x-ui.submit-button loading-text="Confirming...">
                {{ __('Confirm') }}
            </x-ui.submit-button>
        </div>
    </form>
</x-guest-layout>
