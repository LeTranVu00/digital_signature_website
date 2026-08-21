@extends('frontend.layouts.app')

@section('title', 'Đổi mật khẩu - CHỮ KÝ SỐ VIP')

@section('content')
    <section class="bg-slate-50 py-10 dark:bg-slate-950 sm:py-14">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <p class="ui-section-kicker">Bảo mật</p>
                    <h1 class="mt-2 text-3xl font-bold tracking-normal text-slate-950 dark:text-white sm:text-4xl">
                        Đổi mật khẩu
                    </h1>
                    <p class="mt-3 text-sm leading-6 text-slate-600 dark:text-slate-400">
                        Cập nhật mật khẩu đăng nhập cho tài khoản của bạn.
                    </p>
                </div>

                <x-ui.button :href="route('profile.edit')" variant="secondary" size="sm">
                    Quay lại hồ sơ
                </x-ui.button>
            </div>

            <div class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900 sm:p-8">
                @include('profile.partials.update-password-form')
            </div>
        </div>
    </section>
@endsection
