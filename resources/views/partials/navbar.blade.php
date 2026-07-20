<header class="bg-white shadow">

    <div class="flex justify-between items-center p-5">

        <h1 class="text-2xl font-bold">
            @yield('title')
        </h1>

        <div class="flex items-center gap-4">

            <span class="text-gray-600">
                {{ auth()->user()->name }}
            </span>

            <form action="{{ route('logout') }}" method="POST">
                @csrf

                <button
                    class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded">
                    Logout
                </button>

            </form>

        </div>

    </div>

</header>