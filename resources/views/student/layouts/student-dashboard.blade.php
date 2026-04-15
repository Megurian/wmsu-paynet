<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Student Portal') | WMSU PayNet</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        :root {
            --student-sidebar-width: 16rem;
            --student-sidebar-collapsed: 5.75rem;
        }

        body.student-sidebar-collapsed {
            --student-sidebar-width: var(--student-sidebar-collapsed);
        }

        #sidebar {
            width: var(--student-sidebar-width);
            transition: width 0.3s ease, transform 0.3s ease;
        }

        #sidebar a span,
        #sidebar .sidebar-brand-copy,
        #sidebar .sidebar-nav-label {
            transition: opacity 0.25s ease, transform 0.25s ease;
        }

        body.student-sidebar-collapsed #sidebar a span,
        body.student-sidebar-collapsed #sidebar .sidebar-brand-copy,
        body.student-sidebar-collapsed #sidebar .sidebar-nav-label {
            opacity: 0;
            transform: translateX(-6px);
            pointer-events: none;
        }

        body.student-sidebar-collapsed #sidebar nav {
            align-items: center;
        }

        body.student-sidebar-collapsed #sidebar nav a {
            justify-content: center;
            padding-left: 0;
            padding-right: 0;
        }

        #main-area {
            margin-left: var(--student-sidebar-width);
            transition: margin-left 0.3s ease;
        }

        #main-header {
            left: var(--student-sidebar-width);
            transition: left 0.3s ease;
        }

        #sidebar-backdrop {
            transition: opacity 0.3s ease;
        }

        @media (max-width: 767px) {
            body {
                overflow-x: hidden;
            }

            #sidebar {
                width: min(18rem, 88vw);
                transform: translateX(-100%);
            }

            body.student-sidebar-open #sidebar {
                transform: translateX(0);
            }

            #main-area {
                margin-left: 0;
            }

            #main-header {
                left: 0;
            }

            body.student-sidebar-open #sidebar-backdrop {
                opacity: 1;
                pointer-events: auto;
            }
        }
    </style>
</head>
<body class="bg-slate-100 min-h-screen">
    @php
        $student = auth()->guard('student')->user();
        $fullName = trim("{$student->last_name}, {$student->first_name} {$student->middle_name} {$student->suffix}");
    @endphp

    <div class="flex min-h-screen">
        <div id="sidebar-backdrop" class="fixed inset-0 z-30 bg-slate-900/50 opacity-0 pointer-events-none md:hidden"></div>

        <aside id="sidebar" class="fixed inset-y-0 left-0 z-40 flex h-screen flex-col bg-red-800 text-white shadow-xl md:shadow-none">
            <button type="button" onclick="toggleSidebar()" class="self-start p-3 text-white focus:outline-none md:p-2" aria-label="Toggle navigation">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"></path>
                </svg>
            </button>

            <div class="flex flex-col items-center gap-3 border-b border-red-700 px-4 py-6">
                <div class="h-16 w-16 overflow-hidden rounded-full bg-white shadow md:h-20 md:w-20">
                    <img src="{{ asset('images/wmsu-logo.jpg') }}" alt="WMSU Logo" class="h-full w-full object-cover">
                </div>
                <div class="sidebar-brand-copy text-center">
                    <h2 class="text-lg font-bold break-words">WMSU PayNet</h2>
                    <p class="sidebar-nav-label text-xs opacity-80">Student Portal</p>
                </div>
            </div>

            <nav class="flex flex-1 flex-col gap-2 px-2 py-4">
                <a href="{{ route('student.dashboard') }}" class="flex items-center gap-3 rounded-xl px-4 py-3 transition {{ request()->routeIs('student.dashboard') ? 'bg-red-700 font-semibold' : 'hover:bg-red-700/80' }}">
                    <svg class="h-5 w-5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                    </svg>
                    <span>Dashboard</span>
                </a>
                <a href="{{ route('student.payments') }}" class="flex items-center gap-3 rounded-xl px-4 py-3 transition {{ request()->routeIs('student.payments*') ? 'bg-red-700 font-semibold' : 'hover:bg-red-700/80' }}">
                    <svg class="h-5 w-5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a4 4 0 10-8 0v2M5 9h14l1 12H4L5 9z"></path>
                    </svg>
                    <span>My Payments</span>
                </a>
                <a href="{{ route('student.promissory_notes.index') }}" class="flex items-center gap-3 rounded-xl px-4 py-3 transition {{ request()->routeIs('student.promissory_notes*') ? 'bg-red-700 font-semibold' : 'hover:bg-red-700/80' }}">
                    <svg class="h-5 w-5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    <span>Promissory Notes</span>
                </a>
                <a href="{{ route('student.profile') }}" class="flex items-center gap-3 rounded-xl px-4 py-3 transition {{ request()->routeIs('student.profile*') ? 'bg-red-700 font-semibold' : 'hover:bg-red-700/80' }}">
                    <svg class="h-5 w-5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5.121 17.804A4 4 0 018 16h8a4 4 0 012.879 1.196M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                    <span>Profile</span>
                </a>
            </nav>
        </aside>

        <div id="main-area" class="flex min-h-screen flex-1 flex-col">
            <header id="main-header" class="fixed top-0 right-0 z-30 flex h-16 items-center justify-between border-b border-slate-200 bg-white/95 px-4 shadow-sm backdrop-blur md:px-6">
                <div class="flex items-center gap-3">
                    <button type="button" onclick="toggleSidebar()" class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-slate-200 text-slate-700 hover:bg-slate-100 md:hidden" aria-label="Open menu">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"></path>
                        </svg>
                    </button>
                    <div class="min-w-0">
                        <div class="text-base font-semibold text-gray-900 md:text-lg">@yield('page-title', 'Student Portal')</div>
                        <div class="hidden text-xs text-slate-500 sm:block">Western Mindanao State University</div>
                    </div>
                </div>

                <div class="relative shrink-0">
                    <button type="button" class="flex max-w-[12rem] items-center gap-2 rounded-xl px-2 py-2 text-sm text-gray-700 hover:bg-slate-100 hover:text-gray-900 focus:outline-none md:max-w-none" onclick="document.getElementById('user-dropdown').classList.toggle('hidden')">
                        <span class="truncate text-right">{{ $fullName }}</span>
                        <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>

                    <div id="user-dropdown" class="hidden absolute right-0 mt-2 w-52 overflow-hidden rounded-xl border border-gray-200 bg-white shadow-lg z-20">
                        <a href="{{ route('student.profile') }}" class="block px-4 py-3 text-sm text-gray-700 hover:bg-gray-100">Profile</a>
                        <form method="POST" action="{{ route('student.logout') }}">
                            @csrf
                            <button type="submit" class="block w-full px-4 py-3 text-left text-sm text-gray-700 hover:bg-gray-100">Logout</button>
                        </form>
                    </div>
                </div>
            </header>

            <main class="flex-1 px-4 pb-8 pt-24 sm:px-6 lg:px-8">
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
        const backdrop = document.getElementById('sidebar-backdrop');
        const mobileQuery = window.matchMedia('(max-width: 767px)');

        function isMobile() {
            return mobileQuery.matches;
        }

        function closeMobileSidebar() {
            document.body.classList.remove('student-sidebar-open');
            backdrop.classList.remove('pointer-events-auto', 'opacity-100');
            backdrop.classList.add('pointer-events-none', 'opacity-0');
        }

        function applySidebarState(collapsed) {
            if (isMobile()) {
                document.body.classList.remove('student-sidebar-collapsed');
                closeMobileSidebar();
                return;
            }

            document.body.classList.toggle('student-sidebar-collapsed', collapsed);
            closeMobileSidebar();
        }

        function toggleSidebar() {
            if (isMobile()) {
                const isOpen = document.body.classList.toggle('student-sidebar-open');
                backdrop.classList.toggle('pointer-events-auto', isOpen);
                backdrop.classList.toggle('opacity-100', isOpen);
                backdrop.classList.toggle('pointer-events-none', !isOpen);
                backdrop.classList.toggle('opacity-0', !isOpen);
                return;
            }

            const isCollapsed = !document.body.classList.contains('student-sidebar-collapsed');
            applySidebarState(isCollapsed);
            localStorage.setItem('studentSidebarCollapsed', String(isCollapsed));
        }

        document.addEventListener('DOMContentLoaded', () => {
            const savedState = localStorage.getItem('studentSidebarCollapsed') === 'true' && !isMobile();
            applySidebarState(savedState);

            backdrop.addEventListener('click', closeMobileSidebar);

            document.querySelectorAll('#sidebar nav a').forEach((link) => {
                link.addEventListener('click', () => {
                    if (isMobile()) {
                        closeMobileSidebar();
                    }
                });
            });

            mobileQuery.addEventListener('change', () => {
                if (isMobile()) {
                    document.body.classList.remove('student-sidebar-collapsed');
                    closeMobileSidebar();
                } else {
                    applySidebarState(localStorage.getItem('studentSidebarCollapsed') === 'true');
                }
            });
        });
    </script>
</body>
</html>
