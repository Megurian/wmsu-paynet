<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Dashboard') | WMSU PayNet</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-gray-100 min-h-screen">

<div class="flex min-h-screen">

    <!-- SIDEBAR -->
    <aside class="w-64 bg-red-800 text-white hidden md:flex flex-col">
        <div class="px-6 py-6 border-b border-red-700">
            <h2 class="text-xl font-bold">
                WMSU PayNet
            </h2>
            <p class="text-xs opacity-80">
                OSA Panel
            </p>
        </div>

        <nav class="flex-1 px-4 py-4 space-y-2">
            <a href="{{ route('osa.dashboard') }}"
               class="block px-4 py-2 rounded-md hover:bg-red-700 transition">
                Dashboard
            </a>

            <a href="#"
               class="block px-4 py-2 rounded-md hover:bg-red-700 transition">
                Payments
            </a>

            <a href="#"
               class="block px-4 py-2 rounded-md hover:bg-red-700 transition">
                Reports
            </a>
        </nav>
    </aside>

    <!-- MAIN AREA -->
    <div class="flex-1 flex flex-col">

        <!-- NAVBAR -->
        <header class="bg-white shadow-sm px-6 py-4 flex justify-between items-center">
            <div>
                <h1 class="text-lg font-semibold text-gray-800">
                    @yield('page-title', 'Dashboard')
                </h1>
            </div>

            <div class="flex items-center gap-4">
                <!-- ROLE BADGE -->
                <span class="text-xs font-semibold bg-red-100 text-red-700 px-3 py-1 rounded-full">
                    OSA
                </span>

                <!-- USER -->
                <span class="text-sm text-gray-700">
                    {{ Auth::user()->name }}
                </span>

                <!-- LOGOUT -->
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button
                        type="submit"
                        class="text-sm text-red-700 hover:underline"
                    >
                        Logout
                    </button>
                </form>
            </div>
        </header>

        <!-- PAGE CONTENT -->
        <main class="flex-1 p-6">
            @yield('content')
        </main>

    </div>
</div>

</body>
</html>
