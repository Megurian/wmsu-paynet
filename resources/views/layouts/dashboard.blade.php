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
            <h2 class="text-xl font-bold">WMSU PayNet</h2>
            <p class="text-xs opacity-80">{{ strtoupper(Auth::user()->role) }} Panel</p>
        </div>

        <nav class="flex-1 px-4 py-4 space-y-2">

            @php
                $role = Auth::user()->role;
            @endphp

            @if($role === 'osa')
                <a href="{{ route('osa.dashboard') }}"
                class="block px-4 py-2 rounded-md transition
                        {{ request()->routeIs('osa.dashboard') ? 'bg-red-700 font-semibold' : 'hover:bg-red-700' }}">
                    Dashboard
                </a>
                <a href="#"
                class="block px-4 py-2 rounded-md transition
                        {{ request()->routeIs('osa.payments') ? 'bg-red-700 font-semibold' : 'hover:bg-red-700' }}">
                    Payments
                </a>
                <a href="#"
                class="block px-4 py-2 rounded-md transition
                        {{ request()->routeIs('osa.reports') ? 'bg-red-700 font-semibold' : 'hover:bg-red-700' }}">
                    Reports
                </a>
                <a href="#"
                class="block px-4 py-2 rounded-md transition
                        {{ request()->routeIs('osa.setup') ? 'bg-red-700 font-semibold' : 'hover:bg-red-700' }}">
                    Setup
                </a>

            @elseif($role === 'usc')
                <a href="{{ route('usc.dashboard') }}"
                class="block px-4 py-2 rounded-md transition
                        {{ request()->routeIs('usc.dashboard') ? 'bg-red-700 font-semibold' : 'hover:bg-red-700' }}">
                    Dashboard
                </a>
                <a href="#"
                class="block px-4 py-2 rounded-md transition
                        {{ request()->routeIs('usc.requests') ? 'bg-red-700 font-semibold' : 'hover:bg-red-700' }}">
                    Student Requests
                </a>
                <a href="#"
                class="block px-4 py-2 rounded-md transition
                        {{ request()->routeIs('usc.approvals') ? 'bg-red-700 font-semibold' : 'hover:bg-red-700' }}">
                    Approvals
                </a>

            @elseif($role === 'college')
                <a href="{{ route('college.dashboard') }}"
                class="block px-4 py-2 rounded-md transition
                        {{ request()->routeIs('college.dashboard') ? 'bg-red-700 font-semibold' : 'hover:bg-red-700' }}">
                    Dashboard
                </a>
                <a href="#"
                class="block px-4 py-2 rounded-md transition
                        {{ request()->routeIs('college.reports') ? 'bg-red-700 font-semibold' : 'hover:bg-red-700' }}">
                    Finance Reports
                </a>
                <a href="#"
                class="block px-4 py-2 rounded-md transition
                        {{ request()->routeIs('college.requests') ? 'bg-red-700 font-semibold' : 'hover:bg-red-700' }}">
                    Faculty Requests
                </a>
            @endif
        </nav>
    </aside>

    <!-- MAIN AREA -->
    <div class="flex-1 flex flex-col">

        <!-- NAVBAR -->
        <header class="bg-white shadow-sm px-6 py-4 flex justify-between items-center">
            <div>
                <h1 class="text-lg font-semibold text-gray-800">@yield('page-title', 'Dashboard')</h1>
            </div>

            <div class="flex items-center gap-4">
                <!-- ROLE BADGE -->
                <div class="relative">
                <!-- Trigger Button -->
                <button type="button" class="flex items-center gap-2 text-sm text-gray-700 hover:text-gray-900 focus:outline-none" id="user-menu-button" aria-expanded="false" aria-haspopup="true" onclick="document.getElementById('user-dropdown').classList.toggle('hidden')">
                    <span>{{ Auth::user()->name }}</span>
                    <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>

                <!-- Dropdown Menu -->
                <div id="user-dropdown" class="hidden absolute right-0 mt-2 w-48 bg-white border border-gray-200 rounded-md shadow-lg z-20">
                    <a href="{{ route('profile.edit') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                        Profile
                    </a>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="w-full text-left block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                            Logout
                        </button>
                    </form>
                </div>
            </div>

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
