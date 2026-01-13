<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Dashboard') | WMSU PayNet</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        #sidebar {
            transition: width 0.3s;
        }
        #sidebar.collapsed {
            width: 100px;
            padding:10px;
        }
        #sidebar a span {
            transition: opacity 0.3s;
        }
        #sidebar.collapsed a span {
            opacity: 0;
        }

         #sidebar.collapsed h2 {
              display:none;
        }

         #sidebar.collapsed p {
            display:none;
        }
    </style>
</head>

<body class="bg-gray-100 min-h-screen">

<div class="flex min-h-screen">

    <!-- SIDEBAR -->
    <aside id="sidebar" class="w-64 bg-red-800 text-white flex flex-col">
        <!-- Collapse Toggle -->
        <div class="flex justify-end px-3 py-2">
            <button onclick="document.getElementById('sidebar').classList.toggle('collapsed')"
                    class="text-white focus:outline-none">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2"
                     viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M4 6h16M4 12h16M4 18h16"></path>
                </svg>
            </button>
        </div>

        <!-- LOGO -->
        <div class="flex flex-col items-center py-6 border-b border-red-700">
            <div class="h-20 w-20 bg-white rounded-full flex items-center justify-center shadow">
                <span class="text-red-800 font-bold">Logo</span>
            </div>
            <h2 class="mt-3 text-xl font-bold text-center">WMSU PayNet</h2>
            <p class="text-xs opacity-80 text-center">{{ strtoupper(Auth::user()->role) }} Panel</p>
        </div>

        <!-- NAVIGATION -->
        <nav class="flex-1 px-2 py-4 space-y-2">
            @php $role = Auth::user()->role; @endphp

            @if($role === 'osa')
                <a href="{{ route('osa.dashboard') }}" class="block px-4 py-2 rounded-md transition
                    {{ request()->routeIs('osa.dashboard') ? 'bg-red-700 font-semibold' : 'hover:bg-red-700' }}">
                    <span>Dashboard</span>
                </a>
                <a href="#" class="block px-4 py-2 rounded-md transition
                    {{ request()->routeIs('osa.payments') ? 'bg-red-700 font-semibold' : 'hover:bg-red-700' }}">
                    <span>Payments</span>
                </a>
                <a href="#" class="block px-4 py-2 rounded-md transition
                    {{ request()->routeIs('osa.reports') ? 'bg-red-700 font-semibold' : 'hover:bg-red-700' }}">
                    <span>Reports</span>
                
                <a href="{{ route('osa.setup') }}" class=" block px-4 py-2 rounded-md transition
                {{ request()->routeIs('osa.setup') ? 'bg-red-700 font-semibold' : 'hover:bg-red-700' }}">Setup</a>

            @elseif($role === 'usc')
                <a href="{{ route('usc.dashboard') }}" class="block px-4 py-2 rounded-md transition
                    {{ request()->routeIs('usc.dashboard') ? 'bg-red-700 font-semibold' : 'hover:bg-red-700' }}">
                    <span>Dashboard</span>
                </a>
                <a href="#" class="block px-4 py-2 rounded-md transition
                    {{ request()->routeIs('usc.requests') ? 'bg-red-700 font-semibold' : 'hover:bg-red-700' }}">
                    <span>Student Requests</span>
                </a>
                <a href="#" class="block px-4 py-2 rounded-md transition
                    {{ request()->routeIs('usc.approvals') ? 'bg-red-700 font-semibold' : 'hover:bg-red-700' }}">
                    <span>Approvals</span>
                </a>
                <a href="#" class="block px-4 py-2 rounded-md transition hover:bg-red-700">
                    <span>Setup</span>
                </a>

            @elseif($role === 'college')
                <a href="{{ route('college.dashboard') }}" class="block px-4 py-2 rounded-md transition
                    {{ request()->routeIs('college.dashboard') ? 'bg-red-700 font-semibold' : 'hover:bg-red-700' }}">
                    <span>Dashboard</span>
                </a>
                <a href="#" class="block px-4 py-2 rounded-md transition
                    {{ request()->routeIs('college.reports') ? 'bg-red-700 font-semibold' : 'hover:bg-red-700' }}">
                    <span>Finance Reports</span>
                </a>
                <a href="#" class="block px-4 py-2 rounded-md transition
                    {{ request()->routeIs('college.requests') ? 'bg-red-700 font-semibold' : 'hover:bg-red-700' }}">
                    <span>Faculty Requests</span>
                </a>
            @endif
        </nav>
    </aside>

    <!-- MAIN AREA -->
    <div class="flex-1 flex flex-col">

        <!-- NAVBAR -->
        <header class="bg-white shadow-sm px-6 py-4 flex justify-between items-center">
            <h1 class="text-lg font-semibold text-gray-800">@yield('page-title', 'Dashboard')</h1>

            <div class="relative">
                <button type="button" class="flex items-center gap-2 text-sm text-gray-700 hover:text-gray-900 focus:outline-none"
                        onclick="document.getElementById('user-dropdown').classList.toggle('hidden')">
                    <span>{{ Auth::user()->name }}</span>
                    <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>

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
        </header>

        <!-- PAGE CONTENT -->
        <main class="flex-1 p-6">
            @yield('content')
        </main>

    </div>
</div>

</body>
</html>
