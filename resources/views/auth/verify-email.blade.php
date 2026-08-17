<x-guest-layout>
    <div class="text-sm leading-6 text-slate-600 dark:text-slate-300">
        Cảm ơn bạn đã đăng ký. Trước khi bắt đầu, vui lòng xác minh email bằng liên kết chúng tôi vừa gửi. Nếu chưa nhận được email, bạn có thể gửi lại bên dưới.
    </div>

    <div class="mt-6 flex flex-col-reverse gap-3 sm:flex-row sm:items-center sm:justify-between">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf

            <x-ui.button type="submit">
                Gửi lại email xác minh
            </x-ui.button>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf

            <button type="submit" class="ui-focus rounded-md text-sm font-semibold text-slate-600 transition hover:text-red-700 dark:text-slate-300 dark:hover:text-amber-200">
                Đăng xuất
            </button>
        </form>
    </div>
</x-guest-layout>
