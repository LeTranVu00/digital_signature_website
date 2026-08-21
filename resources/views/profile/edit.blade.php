@extends('frontend.layouts.app')

@section('title', 'Hồ sơ cá nhân - CHỮ KÝ SỐ VIP')

@section('content')
    <section class="bg-slate-50 py-10 dark:bg-slate-950 sm:py-14">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <p class="ui-section-kicker">Tài khoản</p>
                    <h1 class="mt-2 text-3xl font-bold tracking-normal text-slate-950 dark:text-white sm:text-4xl">
                        Hồ sơ cá nhân
                    </h1>
                    <p class="mt-3 max-w-2xl text-sm leading-6 text-slate-600 dark:text-slate-400">
                        Quản lý thông tin hiển thị, số điện thoại và các thiết lập bảo mật tài khoản.
                    </p>
                </div>
            </div>

            <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <div class="bg-gradient-to-r from-blue-700 via-blue-600 to-cyan-500 px-4 py-8 text-white sm:px-8">
                    <div class="flex items-center gap-4">
                        <x-ui.avatar :user="$user" size="lg" class="ring-4 ring-white/30" />

                        <div class="min-w-0">
                            <p class="text-sm font-semibold uppercase text-blue-100">Tài khoản cá nhân</p>
                            <h2 class="mt-1 truncate text-2xl font-bold">{{ $user->name }}</h2>
                            <p class="mt-1 truncate text-sm text-blue-50">{{ $user->email }}</p>
                        </div>
                    </div>
                </div>

                <div class="grid gap-4 px-4 py-5 text-sm sm:grid-cols-3 sm:px-8">
                    <div>
                        <p class="font-semibold text-slate-500 dark:text-slate-400">Số điện thoại</p>
                        <p class="mt-1 font-bold text-slate-950 dark:text-white">{{ $user->phone ?: 'Chưa cập nhật' }}</p>
                    </div>

                    <div>
                        <p class="font-semibold text-slate-500 dark:text-slate-400">Trạng thái email</p>
                        <p class="mt-1 font-bold text-slate-950 dark:text-white">
                            {{ $user->hasVerifiedEmail() ? 'Đã xác minh' : 'Chưa xác minh' }}
                        </p>
                    </div>

                    <div>
                        <p class="font-semibold text-slate-500 dark:text-slate-400">Cập nhật gần nhất</p>
                        <p class="mt-1 font-bold text-slate-950 dark:text-white">{{ $user->updated_at?->format('d/m/Y H:i') }}</p>
                    </div>
                </div>
            </div>

            <div class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900 sm:p-8">
                <div class="max-w-xl">
                    @include('profile.partials.update-profile-information-form')
                </div>
            </div>

            <div class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900 sm:p-8">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h2 class="text-lg font-medium text-slate-900 dark:text-white">
                            Bảo mật tài khoản
                        </h2>
                        <p class="mt-1 text-sm text-slate-600 dark:text-slate-400">
                            Mật khẩu được quản lý ở một trang riêng để thao tác rõ ràng và an toàn hơn.
                        </p>
                    </div>

                    <x-ui.button :href="route('profile.password.edit')" variant="secondary">
                        Đổi mật khẩu
                    </x-ui.button>
                </div>
            </div>

            <div class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900 sm:p-8">
                <div class="max-w-xl">
                    @include('profile.partials.delete-user-form')
                </div>
            </div>
        </div>
    </section>
@endsection
