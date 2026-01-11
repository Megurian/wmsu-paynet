<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'WMSU PayNet')</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-gray-100">

<main class="min-h-screen flex">

    <!-- LEFT COLUMN -->
    <div class="hidden md:flex w-1/2 bg-gradient-to-br from-red-700 to-red-800
                text-white flex-col items-center justify-center px-12 text-center">

        <img
            src="{{ asset('images/wmsu-logo.jpg') }}"
            alt="WMSU Logo"
            class="h-36 w-36 mb-6 rounded-full bg-white p-3 shadow-lg"
        >

        <h1 class="text-4xl font-bold mb-3 tracking-wide">
            WMSU PayNet
        </h1>

        <p class="text-sm opacity-90 max-w-md leading-relaxed">
            A secure and centralized online payment and transaction management
            system for Western Mindanao State University.
        </p>

        <p class="text-xs opacity-70 mt-10">
            © {{ date('Y') }} Western Mindanao State University
        </p>
    </div>

    <!-- RIGHT COLUMN -->
    <div class="w-full md:w-1/2 flex items-center justify-center px-6">
        <div
            class="bg-white w-full max-w-md rounded-xl shadow-xl p-8
                transition-all duration-300 ease-in-out
                animate-slide-in-right"
        >
            @yield('content')
        </div>
    </div>


</main>

</body>
</html>
