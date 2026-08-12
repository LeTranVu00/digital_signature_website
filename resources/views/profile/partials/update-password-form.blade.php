<section>
    <header>
        <h2 class="text-lg font-medium text-slate-900 dark:text-white">
            Đổi mật khẩu
        </h2>

        <p class="mt-1 text-sm text-slate-600 dark:text-slate-400">
            Sử dụng mật khẩu đủ mạnh và không trùng với các tài khoản khác.
        </p>
    </header>

    <form method="post" action="{{ route('password.update') }}" class="mt-6 space-y-6">
        @csrf
        @method('put')

        <div>
            <x-input-label for="update_password_current_password" value="Mật khẩu hiện tại" />
            <x-text-input id="update_password_current_password" name="current_password" type="password" class="mt-1 block w-full" autocomplete="current-password" />
            <x-input-error :messages="$errors->updatePassword->get('current_password')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="update_password_password" value="Mật khẩu mới" />
            <x-text-input id="update_password_password" name="password" type="password" class="mt-1 block w-full" autocomplete="new-password" />
            <x-input-error :messages="$errors->updatePassword->get('password')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="update_password_password_confirmation" value="Xác nhận mật khẩu mới" />
            <x-text-input id="update_password_password_confirmation" name="password_confirmation" type="password" class="mt-1 block w-full" autocomplete="new-password" />
            <x-input-error :messages="$errors->updatePassword->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button>Cập nhật mật khẩu</x-primary-button>
        </div>
    </form>
</section>
