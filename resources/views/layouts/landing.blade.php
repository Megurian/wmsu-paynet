<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'WMSU PayNet')</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-white text-gray-900 page-shell">
    <main class="min-h-screen grid lg:grid-cols-12">
        <section class="hidden lg:flex lg:col-span-5 xl:col-span-4 bg-red-700 from-red-800 to-red-900 text-white flex-col items-center justify-center px-12 py-16">
            <img
                src="{{ asset('images/wmsu-logo.jpg') }}"
                alt="WMSU Logo"
                class="h-32 w-32 mb-6 rounded-full bg-white/10 p-4 shadow-2xl"
            >

            <h1 class="text-4xl font-extrabold tracking-tight mb-4">
                WMSU PayNet
            </h1>

            <p class="max-w-xs text-sm leading-7 opacity-90">
                Modern university payment management for student accounts, billing workflows, and financial reporting.
                Built to support staff and students under one unified access point.
            </p>

            <p class="mt-10 text-xs uppercase tracking-[0.3em] text-white/70">
                Western Mindanao State University
            </p>
        </section>

        <section class="lg:col-span-7 xl:col-span-8 relative flex items-center justify-center px-6 py-12 sm:px-10 overflow-hidden">
            <canvas id="landing-particles" class="absolute inset-0 w-full h-full pointer-events-none" aria-hidden="true"></canvas>
            <div class="relative z-10 w-full max-w-5xl">
                @yield('content')
            </div>
        </section>
    </main>
</body>
</html>
