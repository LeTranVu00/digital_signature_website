<x-guest-layout>
    <div class="text-sm leading-6 text-slate-600 dark:text-slate-300">
        {{ __('Thanks for signing up! Before getting started, could you verify your email address by clicking on the link we just emailed to you? If you didn\'t receive the email, we will gladly send you another.') }}
    </div>

    <div class="mt-6 flex flex-col-reverse gap-3 sm:flex-row sm:items-center sm:justify-between">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf

            <x-ui.button type="submit">
                {{ __('Resend Verification Email') }}
            </x-ui.button>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf

            <button type="submit" class="ui-focus rounded-md text-sm font-semibold text-slate-600 transition hover:text-blue-700 dark:text-slate-300 dark:hover:text-blue-200">
                {{ __('Log Out') }}
            </button>
        </form>
    </div>
</x-guest-layout>
