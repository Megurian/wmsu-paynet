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
            padding: 10px;
        }

        #sidebar a span {
            transition: opacity 0.3s;
        }

        #sidebar.collapsed a span {
            opacity: 0;
        }

        #sidebar.collapsed nav {
           display: none;
        }

        #sidebar.collapsed h2 {
            display: none;
        }

        #sidebar.collapsed p {
            display: none;
        }

    </style>
</head>

<body class="bg-gray-100 min-h-screen">

    <div class="flex">

        <!-- SIDEBAR -->
        <aside id="sidebar" class="fixed top-0 left-0 h-screen w-64 bg-red-800 text-white flex flex-col z-40">
            <button onclick="toggleSidebar()" class="text-white focus:outline-none">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"></path>
                </svg>
            </button>

            <!-- LOGO -->
            <div class="flex flex-col items-center py-6 border-b border-red-700">
                <div class="h-20 w-20 bg-white rounded-full flex items-center justify-center shadow overflow-hidden">
                    @if($currentCollege && $currentCollege->logo)
                    <img src="{{ asset('storage/' . $currentCollege->logo) }}" alt="College Logo" class="h-full w-full object-cover">
                    @else
                    {{-- Fallback (OSA / no college) --}}
                    <span class="text-red-800 font-bold text-sm text-center">
                        No<br>logo
                    </span>
                    @endif
                </div>
                @if(Auth::user()->role === 'college' && $currentCollege)
                    <h2 class="mt-3 text-lg font-bold text-center leading-snug break-words max-w-[12rem] mx-auto">
                        {{ $currentCollege->name }}
                    </h2>
                    <p class="text-xs opacity-80 text-center">College Admin</p>
                @else
                    <h2 class="mt-3 text-lg font-bold text-center max-w-[12rem] mx-auto break-words">
                        WMSU PayNet
                    </h2>
                    <p class="text-xs opacity-80 text-center">{{ strtoupper(Auth::user()->role) }} Panel</p>
                @endif

            </div>

            <!-- NAVIGATION -->
            <nav class="flex-1 px-2 py-4 space-y-2">
                @php $role = Auth::user()->role; @endphp

                @if($role === 'osa')
                <a href="{{ route('osa.dashboard') }}" class="block px-4 py-2 rounded-md transition
                        {{ request()->routeIs('osa.dashboard') ? 'bg-red-700 font-semibold' : 'hover:bg-red-700' }}">
                    <span>Dashboard</span>
                </a>
                <a href="{{ route('osa.college') }}" class="block px-4 py-2 rounded-md transition
                        {{ request()->routeIs('osa.college') ? 'bg-red-700 font-semibold' : 'hover:bg-red-700' }}">
                    <span>College</span>
                </a>
                <a href="#" class="block px-4 py-2 rounded-md transition
                    {{ request()->routeIs('osa.reports') ? 'bg-red-700 font-semibold' : 'hover:bg-red-700' }}">
                    <span>Reports</span>
                </a>
                <a href="{{ route('osa.setup') }}" class="block px-4 py-2 rounded-md transition
                    {{ request()->routeIs('osa.setup') ? 'bg-red-700 font-semibold' : 'hover:bg-red-700' }}">
                    <span>Setup</span>
                </a>

                @elseif($role === 'university_org')
                <a href="{{ route('university_org.dashboard') }}" class="block px-4 py-2 rounded-md transition
                    {{ request()->routeIs('university_org.dashboard') ? 'bg-red-700 font-semibold' : 'hover:bg-red-700' }}">
                    <span>Dashboard</span>
                </a>
                <a href="{{ route('university_org.fees') }}" class="block px-4 py-2 rounded-md transition
                    {{ request()->routeIs('university_org.fees') ? 'bg-red-700 font-semibold' : 'hover:bg-red-700' }}">
                    <span>Fees</span>
                </a>
                <a href="{{ route('university_org.remittance') }}" class="block px-4 py-2 rounded-md transition
                    {{ request()->routeIs('university_org.remittance') ? 'bg-red-700 font-semibold' : 'hover:bg-red-700' }}">
                    <span>Remittance</span>
                </a>
                <a href="{{ route('university_org.reports') }}" class="block px-4 py-2 rounded-md transition
                    {{ request()->routeIs('university_org.reports') ? 'bg-red-700 font-semibold' : 'hover:bg-red-700' }}">
                    <span>Reports</span>
                </a>
                <a href="{{ route('university_org.setup') }}" class="block px-4 py-2 rounded-md transition
                    {{ request()->routeIs('university_org.setup') ? 'bg-red-700 font-semibold' : 'hover:bg-red-700' }}">
                    <span>Setup</span>
                </a>

                @elseif($role === 'college')
                <a href="{{ route('college.dashboard') }}" class="block px-4 py-2 rounded-md transition
                    {{ request()->routeIs('college.dashboard') ? 'bg-red-700 font-semibold' : 'hover:bg-red-700' }}">
                    <span>Dashboard</span>
                </a>
                <a href="{{ route('college.students') }}" class="block px-4 py-2 rounded-md transition
                    {{ request()->routeIs('college.students') ? 'bg-red-700 font-semibold' : 'hover:bg-red-700' }}">
                    <span>Student Directory</span>
                </a>
                <a href="#" class="block px-4 py-2 rounded-md transition
                    {{ request()->routeIs('college.requests') ? 'bg-red-700 font-semibold' : 'hover:bg-red-700' }}">
                    <span>Placeholder</span>
                </a>
                @endif
            </nav>
        </aside>

        <!-- MAIN AREA -->
        <div id="main-area" class="flex-1 flex flex-col ml-64 transition-all duration-300">

            <!-- NAVBAR -->
            <header id="main-header" class="fixed top-0 left-64 right-0 bg-white shadow-sm px-6 py-3 flex items-center justify-between z-30 transition-all duration-300">

                <!-- Page Title  -->
                <div class="text-lg font-semibold text-gray-800">
                    @yield('page-title', 'Dashboard')
                </div>

                <!-- Academic Period -->
                <div class="text-sm text-gray-600 flex items-center gap-2">
                    @if($latestSchoolYear)
                    <span class="font-medium text-gray-700">
                        S.Y {{ \Carbon\Carbon::parse($latestSchoolYear->sy_start)->format('Y') }} –
                        {{ \Carbon\Carbon::parse($latestSchoolYear->sy_end)->format('Y') }}
                    </span>
                    <span class="text-gray-400">·</span>
                    <span class="font-medium text-red-700">
                        {{ ucfirst($latestSchoolYear->semesters->firstWhere('is_active', true)?->name ?? 'No active semester') }}
                    </span>
                    @else
                    <span class="italic text-gray-400">No active academic period</span>
                    @endif
                </div>

                <!-- User Dropdown -->
                <div class="relative">
                    <button type="button" class="flex items-center gap-2 text-sm text-gray-700 hover:text-gray-900 focus:outline-none" onclick="document.getElementById('user-dropdown').classList.toggle('hidden')">
                        <span>{{ Auth::user()->name }}</span>
                        <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>

                    <div id="user-dropdown" class="hidden absolute right-0 mt-2 w-48 bg-white border border-gray-200 rounded-md shadow-lg z-20">
                        <a href="{{ route('profile.edit') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Profile</a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="w-full text-left block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Logout</button>
                        </form>
                    </div>
                </div>

            </header>

            <!-- PAGE CONTENT -->
            <main class="flex-1 p-8 mt-10">
                @yield('content')
            </main>
        </div>

        <script>
            function toggleSidebar() {
                const sidebar = document.getElementById('sidebar');
                const mainArea = document.getElementById('main-area');
                const header = document.getElementById('main-header');

                sidebar.classList.toggle('collapsed');

                if (sidebar.classList.contains('collapsed')) {
                    mainArea.style.marginLeft = '100px';
                    header.style.left = '100px';
                    header.style.right = '0';
                } else {
                    mainArea.style.marginLeft = '16rem';
                    header.style.left = '16rem';
                    header.style.right = '0';
                }
            }

        </script>

    </div>

</body>
</html>
