<section>
    <header>
        <h2 class="text-lg font-medium text-slate-900 dark:text-white">
            Thông tin cá nhân
        </h2>

        <p class="mt-1 text-sm text-slate-600 dark:text-slate-400">
            Cập nhật tên hiển thị và số điện thoại. Email đăng nhập đã được khóa để bảo vệ tài khoản.
        </p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" class="mt-6 space-y-6">
        @csrf
        @method('patch')

        @if ($user->avatar)
            <div class="flex items-center gap-4">
                <img
                    src="{{ $user->avatar }}"
                    alt="{{ $user->name }}"
                    class="h-16 w-16 rounded-full object-cover"
                >
                <div>
                    <p class="text-sm font-medium text-slate-900 dark:text-white">
                        {{ __('Google avatar') }}
                    </p>
                    <p class="text-sm text-slate-500 dark:text-slate-400">
                        {{ __('This avatar is provided by your Google account.') }}
                    </p>
                </div>
            </div>
        @endif

        <div>
            <x-input-label for="name" value="Tên hiển thị" />
            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $user->name)" required autofocus autocomplete="name" />
            <x-input-error class="mt-2" :messages="$errors->get('name')" />
        </div>

        <div>
            <x-input-label for="email" value="Email đăng nhập" />
            <x-text-input id="email" type="email" class="mt-1 block w-full" :value="$user->email" disabled autocomplete="username" />
            <p class="mt-2 text-xs leading-5 text-slate-500 dark:text-slate-400">
                Email này dùng để đăng nhập và không thể thay đổi trong trang hồ sơ.
            </p>

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div>
                    <p class="mt-2 text-sm text-slate-800 dark:text-slate-300">
                        {{ __('Your email address is unverified.') }}

                        <button form="send-verification" class="rounded-md text-sm text-slate-600 underline hover:text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:text-slate-300 dark:hover:text-white dark:focus:ring-offset-slate-900">
                            {{ __('Click here to re-send the verification email.') }}
                        </button>
                    </p>

                </div>
            @endif
        </div>

        <x-ui.input
            type="tel"
            name="phone"
            label="Số điện thoại"
            :value="old('phone', $user->phone)"
            autocomplete="tel"
            helper="Số điện thoại sẽ hiển thị trong menu tài khoản để bạn kiểm tra nhanh."
        />

        <div class="flex items-center gap-4">
            <x-primary-button>Lưu thay đổi</x-primary-button>
        </div>
    </form>
</section>
