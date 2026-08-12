@props([
    'user' => auth()->user(),
    'showName' => true,
])

@if ($user)
    <x-dropdown align="right" width="72">
        <x-slot name="trigger">
            <button
                type="button"
                class="ui-focus inline-flex items-center gap-2 rounded-full border border-slate-200 bg-white p-1 pr-2 text-left shadow-sm transition duration-200 ease-out hover:border-amber-200 hover:bg-amber-50 dark:border-slate-700 dark:bg-slate-900 dark:hover:border-amber-500 dark:hover:bg-slate-800"
                aria-label="Mở menu tài khoản"
                x-bind:aria-expanded="open.toString()"
            >
                <x-ui.avatar :user="$user" size="sm" />

                @if ($showName)
                    <span class="hidden max-w-32 truncate text-sm font-semibold text-slate-700 dark:text-slate-200 sm:block">
                        {{ $user->name }}
                    </span>
                @endif

                <svg class="h-4 w-4 text-slate-400 dark:text-slate-500" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 11.17l3.71-3.94a.75.75 0 1 1 1.08 1.04l-4.25 4.5a.75.75 0 0 1-1.08 0l-4.25-4.5a.75.75 0 0 1 .02-1.06Z" clip-rule="evenodd" />
                </svg>
            </button>
        </x-slot>

        <x-slot name="content">
            <div class="border-b border-slate-200 px-4 py-4 dark:border-slate-800">
                <div class="flex items-center gap-3">
                    <x-ui.avatar :user="$user" size="lg" />

                    <div class="min-w-0">
                        <p class="truncate text-sm font-bold text-slate-950 dark:text-white">{{ $user->name }}</p>
                        <p class="truncate text-xs text-slate-500 dark:text-slate-400">{{ $user->email }}</p>
                    </div>
                </div>

                <div class="mt-4 grid gap-2 text-xs text-slate-500 dark:text-slate-400">
                    <div class="flex items-center justify-between gap-3">
                        <span>Điện thoại</span>
                        <span class="max-w-36 truncate font-semibold text-slate-700 dark:text-slate-200">
                            {{ $user->phone ?: 'Chưa cập nhật' }}
                        </span>
                    </div>
                </div>
            </div>

            <x-dropdown-link :href="route('profile.edit')">
                Hồ sơ cá nhân
            </x-dropdown-link>

            <x-dropdown-link :href="route('profile.password.edit')">
                Đổi mật khẩu
            </x-dropdown-link>

            @if ($user->isAdmin())
                <x-dropdown-link :href="route('admin.dashboard')">
                    Trang quản trị
                </x-dropdown-link>
            @endif

            <form method="POST" action="{{ route('logout') }}">
                @csrf

                <x-dropdown-link
                    :href="route('logout')"
                    onclick="event.preventDefault(); this.closest('form').submit();"
                >
                    Đăng xuất
                </x-dropdown-link>
            </form>
        </x-slot>
    </x-dropdown>
@endif
