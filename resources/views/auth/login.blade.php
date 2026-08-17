<x-guest-layout>
    @php
        $googleLoginEnabled = filled(config('services.google.client_id'))
            && filled(config('services.google.client_secret'))
            && filled(config('services.google.redirect'));
    @endphp

    @if ($googleLoginEnabled)
        <a
            href="{{ route('google.redirect') }}"
            class="ui-focus mb-6 flex w-full items-center justify-center gap-2 rounded-lg border border-slate-200 bg-white px-4 py-3 text-sm font-bold text-slate-800 shadow-sm transition hover:border-amber-200 hover:bg-amber-50 hover:text-red-700 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100 dark:hover:border-amber-400 dark:hover:bg-slate-800 dark:hover:text-amber-200"
        >
            <span class="flex h-5 w-5 items-center justify-center rounded-full bg-white text-xs font-black text-red-600 shadow-sm ring-1 ring-slate-200">G</span>
            Đăng nhập bằng Google
        </a>

        <div class="mb-6 flex items-center gap-3">
            <div class="h-px flex-1 bg-slate-200 dark:bg-slate-800"></div>
            <span class="text-xs font-bold uppercase tracking-normal text-slate-400 dark:text-slate-500">Hoặc đăng nhập bằng email</span>
            <div class="h-px flex-1 bg-slate-200 dark:bg-slate-800"></div>
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}" x-data="{ submitting: false }" x-on:submit="submitting = true">
        @csrf

        <div class="space-y-4">
            <x-ui.input
                type="email"
                name="email"
                label="Email"
                :value="old('email')"
                required
                autofocus
                autocomplete="username"
            />

            <x-ui.input
                type="password"
                name="password"
                label="Mật khẩu"
                required
                autocomplete="current-password"
            />
        </div>

        <div class="mt-4">
            <label for="remember_me" class="inline-flex items-center">
                <input id="remember_me" type="checkbox" class="rounded border-slate-300 text-amber-500 shadow-sm focus:ring-amber-500 dark:border-slate-700 dark:bg-slate-950 dark:focus:ring-amber-400" name="remember">
                <span class="ms-2 text-sm font-medium text-slate-600 dark:text-slate-300">Ghi nhớ đăng nhập</span>
            </label>
        </div>

        <div class="mt-5 flex flex-col-reverse gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div class="grid gap-2">
                @if (Route::has('password.request'))
                    <a class="ui-focus rounded-md text-sm font-semibold text-slate-600 transition hover:text-red-700 dark:text-slate-300 dark:hover:text-amber-200" href="{{ route('password.request') }}">
                        Quên mật khẩu?
                    </a>
                @endif

                @if (Route::has('register'))
                    <a class="ui-focus rounded-md text-sm font-semibold text-slate-600 transition hover:text-red-700 dark:text-slate-300 dark:hover:text-amber-200" href="{{ route('register') }}">
                        Chưa có tài khoản? Đăng ký
                    </a>
                @endif
            </div>

            <x-ui.submit-button loading-text="Đang đăng nhập...">
                Đăng nhập
            </x-ui.submit-button>
        </div>
    </form>
</x-guest-layout>
