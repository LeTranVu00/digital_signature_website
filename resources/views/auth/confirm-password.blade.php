<x-guest-layout>
    <p class="mb-5 text-sm leading-6 text-slate-600 dark:text-slate-300">
        Đây là khu vực bảo mật. Vui lòng xác nhận mật khẩu trước khi tiếp tục.
    </p>

    <form method="POST" action="{{ route('password.confirm') }}" x-data="{ submitting: false }" x-on:submit="submitting = true">
        @csrf

        <x-ui.input
            type="password"
            name="password"
            label="Mật khẩu"
            required
            autocomplete="current-password"
        />

        <div class="mt-5 flex justify-end">
            <x-ui.submit-button loading-text="Đang xác nhận...">
                Xác nhận
            </x-ui.submit-button>
        </div>
    </form>
</x-guest-layout>
