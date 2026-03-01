<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Student Portal') | WMSU PayNet</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        #sidebar { transition: width 0.3s; }
        #sidebar.collapsed { width: 100px; padding: 10px; }
        #sidebar a span { transition: opacity 0.3s; }
        #sidebar.collapsed a span { opacity: 0; }
        #sidebar.collapsed nav { display: none; }
        #sidebar.collapsed h2 { display: none; }
        #sidebar.collapsed p { display: none; }
    </style>
</head>
<body class="bg-gray-100 min-h-screen">
    @php
        $student = auth()->guard('student')->user();
        $fullName = trim("{$student->last_name}, {$student->first_name} {$student->middle_name} {$student->suffix}");
    @endphp

    <div class="flex">
        <aside id="sidebar" class="fixed top-0 left-0 h-screen w-64 bg-red-800 text-white flex flex-col z-40">
            <button onclick="toggleSidebar()" class="text-white focus:outline-none p-2 self-start">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"></path>
                </svg>
            </button>

            <div class="flex flex-col items-center py-6 border-b border-red-700">
                <div class="h-20 w-20 bg-white rounded-full flex items-center justify-center shadow overflow-hidden">
                    <img src="{{ asset('images/wmsu-logo.jpg') }}" alt="WMSU Logo" class="h-full w-full object-cover">
                </div>
                <h2 class="mt-3 text-lg font-bold text-center break-words max-w-[12rem]">WMSU PayNet</h2>
                <p class="text-xs opacity-80 text-center">Student Portal</p>
            </div>

            <nav class="flex-1 px-2 py-4 space-y-2">
                <a href="{{ route('student.dashboard') }}" class="block px-4 py-2 rounded-md transition {{ request()->routeIs('student.dashboard') ? 'bg-red-700 font-semibold' : 'hover:bg-red-700' }}">
                    <span>Dashboard</span>
                </a>
                <a href="{{ route('student.payments') }}" class="block px-4 py-2 rounded-md transition {{ request()->routeIs('student.payments*') ? 'bg-red-700 font-semibold' : 'hover:bg-red-700' }}">
                    <span>My Payments</span>
                </a>
                <a href="{{ route('student.profile') }}" class="block px-4 py-2 rounded-md transition {{ request()->routeIs('student.profile*') ? 'bg-red-700 font-semibold' : 'hover:bg-red-700' }}">
                    <span>Profile</span>
                </a>
            </nav>
        </aside>

        <div id="main-area" class="flex-1 flex flex-col ml-64 transition-all duration-300">
            <header id="main-header" class="fixed top-0 left-64 right-0 bg-white shadow-sm px-6 py-3 flex items-center justify-between z-30 transition-all duration-300">
                <div class="text-lg font-semibold text-gray-800">@yield('page-title', 'Student Portal')</div>

                <div class="relative">
                    <button type="button" class="flex items-center gap-2 text-sm text-gray-700 hover:text-gray-900 focus:outline-none" onclick="document.getElementById('user-dropdown').classList.toggle('hidden')">
                        <span>{{ $fullName }}</span>
                        <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>

                    <div id="user-dropdown" class="hidden absolute right-0 mt-2 w-48 bg-white border border-gray-200 rounded-md shadow-lg z-20">
                        <a href="{{ route('student.profile') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Profile</a>
                        <form method="POST" action="{{ route('student.logout') }}">
                            @csrf
                            <button type="submit" class="w-full text-left block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Logout</button>
                        </form>
                    </div>
                </div>
            </header>

            <main class="flex-1 p-8 mt-10">
                @if(session('status'))
                    <div class="mb-4 rounded-md border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">
                        {{ session('status') }}
                    </div>
                @endif

                @if($errors->any())
                    <div class="mb-4 rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                        <ul class="list-disc pl-5 space-y-1">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>

    <script>
        const sidebar = document.getElementById('sidebar');
        const mainArea = document.getElementById('main-area');
        const header = document.getElementById('main-header');

        function applySidebarState(collapsed) {
            if (collapsed) {
                sidebar.classList.add('collapsed');
                mainArea.style.marginLeft = '100px';
                header.style.left = '100px';
                header.style.right = '0';
            } else {
                sidebar.classList.remove('collapsed');
                mainArea.style.marginLeft = '16rem';
                header.style.left = '16rem';
                header.style.right = '0';
            }
        }

        function toggleSidebar() {
            const isCollapsed = sidebar.classList.toggle('collapsed');
            applySidebarState(isCollapsed);
            localStorage.setItem('studentSidebarCollapsed', isCollapsed);
        }

        document.addEventListener('DOMContentLoaded', () => {
            const savedState = localStorage.getItem('studentSidebarCollapsed') === 'true';
            applySidebarState(savedState);
        });
    </script>
</body>
</html>
