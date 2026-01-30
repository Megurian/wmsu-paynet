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
        @keyframes fadeIn {
            from { opacity: 0; transform: scale(0.95); }
            to { opacity: 1; transform: scale(1); }
        }
        .animate-fade-in {
            animation: fadeIn .2s ease-out;
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

        @php
            $user = Auth::user();
        @endphp

        <div class="flex flex-col items-center py-6 border-b border-red-700">

            {{-- Logo --}}
            <div class="h-20 w-20 bg-white rounded-full flex items-center justify-center shadow overflow-hidden">
                @if(in_array($user->role, ['college', 'student_coordinator', 'adviser']) && $currentCollege?->logo)
                    <img src="{{ asset('storage/' . $currentCollege->logo) }}"
                        alt="College Logo"
                        class="h-full w-full object-cover">
                @elseif(in_array($user->role, ['university_org', 'college_org']) && $organization?->logo)
                    <img src="{{ asset('storage/' . $organization->logo) }}"
                        alt="Organization Logo"
                        class="h-full w-full object-cover">
                @else
                    <span class="text-red-800 font-bold text-sm text-center">
                        No<br>Logo
                    </span>
                @endif
            </div>

             @if(in_array($user->role, ['college', 'student_coordinator', 'adviser']) && $currentCollege)
                <h2 class="mt-3 text-lg font-bold text-center break-words max-w-[12rem]">
                    {{ $currentCollege->name }}
                </h2>
                <p class="text-xs opacity-80 text-center">
                    @switch($user->role)
                        @case('college') College Dean @break
                        @case('student_coordinator') Student Coordinator @break
                        @case('adviser') Adviser @break
                    @endswitch
                </p>

            @elseif($user->role === 'osa')
                <h2 class="mt-3 text-lg font-bold text-center max-w-[12rem]">
                    Office of the Student Affairs
                </h2>

            @elseif(in_array($user->role, ['university_org', 'college_org']) && $organization)
                <h2 class="mt-3 text-lg font-bold text-center break-words max-w-[12rem]">
                    {{ $organization->name }}
                </h2>
                <p class="text-xs opacity-80 text-center">
                    {{ $user->role === 'university_org' ? 'University Organization' : 'College Organization' }}
                </p>

            @else
                <h2 class="mt-3 text-lg font-bold text-center">
                    WMSU PayNet
                </h2>
                <p class="text-xs opacity-80 text-center">{{ strtoupper($user->role) }} Panel</p>
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
                <a href="{{ route('osa.fees') }}" class="block px-4 py-2 rounded-md transition
                    {{ request()->routeIs('osa.fees') ? 'bg-red-700 font-semibold' : 'hover:bg-red-700' }}">
                    <span>Fees</span>
                </a>
                <a href="{{ route('osa.organizations') }}" class="block px-4 py-2 rounded-md transition
                    {{ request()->routeIs('osa.organizations') ? 'bg-red-700 font-semibold' : 'hover:bg-red-700' }}">
                    <span>Organizations</span>
                </a>
                <a href="{{ route('osa.setup') }}" class="block px-4 py-2 rounded-md transition
                    {{ request()->routeIs('osa.setup') ? 'bg-red-700 font-semibold' : 'hover:bg-red-700' }}">
                    <span>Academic Year Setup</span>
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
                <a href="{{ route('university_org.offices') }}" class="block px-4 py-2 rounded-md transition
                    {{ request()->routeIs('university_org.offices') ? 'bg-red-700 font-semibold' : 'hover:bg-red-700' }}">
                    <span>Offices</span>
                </a>
                <a href="{{ route('university_org.remittance') }}" class="block px-4 py-2 rounded-md transition
                    {{ request()->routeIs('university_org.remittance') ? 'bg-red-700 font-semibold' : 'hover:bg-red-700' }}">
                    <span>Remittance</span>
                </a>
                <a href="{{ route('university_org.reports') }}" class="block px-4 py-2 rounded-md transition
                    {{ request()->routeIs('university_org.reports') ? 'bg-red-700 font-semibold' : 'hover:bg-red-700' }}">
                    <span>Reports</span>
                </a>

                @elseif(in_array($role, ['college', 'student_coordinator', 'adviser']))
                <a href="{{ route('college.dashboard') }}" class="block px-4 py-2 rounded-md transition
                    {{ request()->routeIs('college.dashboard') ? 'bg-red-700 font-semibold' : 'hover:bg-red-700' }}">
                    <span>Dashboard</span>
                </a>
                <a href="{{ route('college.students') }}" class="block px-4 py-2 rounded-md transition
                    {{ request()->routeIs('college.students') ? 'bg-red-700 font-semibold' : 'hover:bg-red-700' }}">
                    <span>Student Directory</span>
                </a>
                <a href="{{ route('college.history') }}" class="block px-4 py-2 rounded-md transition
                    {{ request()->routeIs('college.history') ? 'bg-red-700 font-semibold' : 'hover:bg-red-700' }}">
                    <span>History</span>
                </a>
                <a href="{{ route('college.students.validate') }}" class="block px-4 py-2 rounded-md transition
                    {{ request()->routeIs('college_org.records') ? 'bg-red-700 font-semibold' : 'hover:bg-red-700' }}">
                    <span>Enrollment Validation</span>
                </a>

                @elseif($role === 'college_org')
                <a href="{{ route('college_org.dashboard') }}" class="block px-4 py-2 rounded-md transition
                    {{ request()->routeIs('college_org.dashboard') ? 'bg-red-700 font-semibold' : 'hover:bg-red-700' }}">
                    <span>Dashboard</span>
                </a>
                <a href="{{ route('college_org.fees') }}" class="block px-4 py-2 rounded-md transition
                    {{ request()->routeIs('college_org.fees') ? 'bg-red-700 font-semibold' : 'hover:bg-red-700' }}">
                    <span>Fees</span>
                </a>
                <a href="{{ route('college_org.payment') }}" class="block px-4 py-2 rounded-md transition
                    {{ request()->routeIs('college_org.payment') ? 'bg-red-700 font-semibold' : 'hover:bg-red-700' }}">
                    <span>Payment</span>
                </a>
                <a href="{{ route('college_org.records') }}" class="block px-4 py-2 rounded-md transition
                    {{ request()->routeIs('college_org.records') ? 'bg-red-700 font-semibold' : 'hover:bg-red-700' }}">
                    <span>Records</span>
                </a>
                @endif

                 @if($role === 'college')
                <a href="{{ route('college.users.index') }}" class="block px-4 py-2 rounded-md transition
                    {{ request()->routeIs('college.users.*') ? 'bg-red-700 font-semibold' : 'hover:bg-red-700' }}">
                    <span>User Management</span>
                </a>
                @endif

                @if(Auth::user()->role === 'adviser')
                    <a href="{{ route('college.students.my-upload') }}" class="block px-4 py-2 rounded-md transition
                        {{ request()->routeIs('college.students.my-upload') ? 'bg-red-700 font-semibold' : 'hover:bg-red-700' }}">
                        <span> Students Upload</span>
                    </a>
                    {{-- <a href="{{ route('college.students.my-upload') }}" class="block px-4 py-2 rounded-md hover:bg-red-700">
                        <span>My Students Upload</span>
                    </a> --}}
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
                {{-- @php
                    use App\Models\Announcement;
                    $announcement = Announcement::active()->latest()->first();
                @endphp

                @if($announcement && auth()->user()->role !== 'osa')
                <div id="announcementBanner" class="mb-6 rounded-xl border border-yellow-300 bg-yellow-50 p-4 animate-fade-in flex justify-between items-start">
                    <div>
                        <h4 class="font-semibold text-yellow-800">
                            {{ $announcement->title }}
                        </h4>
                        <p class="text-sm text-yellow-700 mt-1">
                            {{ $announcement->message }}
                        </p>
                    </div>
                    <button id="closeAnnouncement" class="ml-4 text-yellow-800 font-bold hover:text-yellow-900">&times;</button>
                </div>
                @endif --}}
                @if(session('status'))
                    <div id="successModal"
                        class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40">
                        <div class="bg-white rounded-xl shadow-lg max-w-sm w-full p-6 text-center animate-fade-in">
                            <div class="mx-auto mb-3 w-12 h-12 flex items-center justify-center rounded-full bg-green-100">
                                <svg class="w-6 h-6 text-green-700" fill="none" stroke="currentColor" stroke-width="2"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M5 13l4 4L19 7"/>
                                </svg>
                            </div>

                            <h3 class="text-lg font-semibold mb-1">Success</h3>
                            <p class="text-sm text-gray-600 mb-4">{{ session('status') }}</p>

                            <button onclick="closeSuccessModal()"
                                    class="bg-green-600 hover:bg-green-700 text-white px-5 py-2 rounded-lg">
                                OK
                            </button>
                        </div>
                    </div>
                    @endif
                    @if($errors->any())
                    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40">
                        <div class="bg-white rounded-xl shadow-lg max-w-md w-full p-6 animate-fade-in">
                            <h3 class="text-lg font-semibold text-red-700 mb-3">Something went wrong</h3>

                            <ul class="text-sm text-gray-700 list-disc pl-5 space-y-1">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>

                            <div class="text-right mt-4">
                                <button onclick="this.closest('.fixed').remove()"
                                        class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded">
                                    Close
                                </button>
                            </div>
                        </div>
                    </div>
                    @endif
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

            function closeSuccessModal() {
                const modal = document.getElementById('successModal');
                if (modal) modal.remove();
            }

            setTimeout(() => closeSuccessModal(), 3000);

            

        </script>

    </div>

</body>
</html>
