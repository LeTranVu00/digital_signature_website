<x-guest-layout>
    <a
        href="{{ route('google.redirect') }}"
        class="ui-focus mb-6 flex w-full items-center justify-center rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-200 dark:hover:border-blue-500 dark:hover:bg-slate-800 dark:hover:text-blue-200"
    >
        Register with Google
    </a>

    <div class="mb-6 flex items-center gap-3">
        <div class="h-px flex-1 bg-slate-200 dark:bg-slate-800"></div>
        <span class="text-xs font-semibold uppercase text-slate-400 dark:text-slate-500">or create account with email</span>
        <div class="h-px flex-1 bg-slate-200 dark:bg-slate-800"></div>
    </div>

    <form method="POST" action="{{ route('register') }}" x-data="{ submitting: false }" x-on:submit="submitting = true">
        @csrf

        <div class="space-y-4">
            <x-ui.input
                name="name"
                label="Name"
                :value="old('name')"
                required
                autofocus
                autocomplete="name"
            />

            <x-ui.input
                type="email"
                name="email"
                label="Email"
                :value="old('email')"
                required
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

        <div class="mt-5 flex flex-col-reverse gap-3 sm:flex-row sm:items-center sm:justify-between">
            <a class="ui-focus rounded-md text-sm font-semibold text-slate-600 transition hover:text-blue-700 dark:text-slate-300 dark:hover:text-blue-200" href="{{ route('login') }}">
                {{ __('Already registered?') }}
            </a>

            <x-ui.submit-button loading-text="Creating...">
                {{ __('Register') }}
            </x-ui.submit-button>
        </div>
    </form>
</x-guest-layout>
