<!DOCTYPE html>
<html lang="vi">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>@yield('title')</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

</head>

<body class="bg-gray-100">

<div class="flex">

    @include('partials.sidebar')

    <div class="flex-1">

        @include('partials.navbar')

        <main class="p-8">

            @yield('content')

        </main>

    </div>

</div>

</body>

</html>
