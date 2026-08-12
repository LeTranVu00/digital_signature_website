<x-guest-layout>
    <p class="mb-5 text-sm leading-6 text-slate-600 dark:text-slate-300">
        {{ __('Forgot your password? No problem. Just let us know your email address and we will email you a password reset link that will allow you to choose a new one.') }}
    </p>

    <form method="POST" action="{{ route('password.email') }}" x-data="{ submitting: false }" x-on:submit="submitting = true">
        @csrf

        <x-ui.input
            type="email"
            name="email"
            label="Email"
            :value="old('email')"
            required
            autofocus
        />

        <div class="mt-5 flex justify-end">
            <x-ui.submit-button loading-text="Sending...">
                {{ __('Email Password Reset Link') }}
            </x-ui.submit-button>
        </div>
    </form>
</x-guest-layout>
