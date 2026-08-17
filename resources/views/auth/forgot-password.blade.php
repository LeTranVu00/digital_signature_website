<x-guest-layout>
    <p class="mb-5 text-sm leading-6 text-slate-600 dark:text-slate-300">
        Nhập email đã đăng ký, chúng tôi sẽ gửi liên kết để bạn tạo mật khẩu mới.
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
            <x-ui.submit-button loading-text="Đang gửi...">
                Gửi liên kết đặt lại mật khẩu
            </x-ui.submit-button>
        </div>
    </form>
</x-guest-layout>
