<x-guest-layout>
    <form method="POST" action="{{ route('password.store') }}" x-data="{ submitting: false }" x-on:submit="submitting = true">
        @csrf

        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <div class="space-y-4">
            <x-ui.input
                type="email"
                name="email"
                label="Email"
                :value="old('email', $request->email)"
                required
                autofocus
                autocomplete="username"
            />

            <x-ui.input
                type="password"
                name="password"
                label="Password"
                required
                autocomplete="new-password"
            />

            <x-ui.input
                type="password"
                name="password_confirmation"
                label="Confirm Password"
                required
                autocomplete="new-password"
            />
        </div>

        <div class="mt-5 flex justify-end">
            <x-ui.submit-button loading-text="Resetting...">
                {{ __('Reset Password') }}
            </x-ui.submit-button>
        </div>
    </form>
</x-guest-layout>
