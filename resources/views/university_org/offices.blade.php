@extends('layouts.dashboard')

@section('title', 'Offices')
@section('page-title', 'USC Office Setup')

@section('content')
<div class="mb-8">
    <h2 class="text-3xl font-bold text-gray-800"> USC Offices</h2>
    <p class="text-sm text-gray-500 mt-1">
        Welcome, {{ Auth::user()->name }}. Here you can manage the Offices associated with different colleges within the university.
    </p> <br>
    <button class="px-4 py-2 bg-red-700 text-white rounded hover:bg-red-800 transition">
            New Office
    </button>
</div>

    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <!-- Sample Organization Card 1 -->
        <div class="bg-white rounded-lg shadow-md p-6 flex flex-col items-center text-center relative">
            <div class="w-28 h-28 mb-4 bg-gray-100 rounded-full flex items-center justify-center text-gray-400">
                <span>Logo</span>
            </div>
            <h3 class="text-lg font-semibold">Student Council</h3>
            <p class="text-gray-600 mb-2"><span class="font-medium">SC</span></p>
            <p class="text-sm text-gray-600">Type: College-based</p>
            
            <div class="absolute top-2 right-2">
                <button onclick="toggleMenu('menu-1')" 
                    class="w-8 h-8 flex items-center justify-center border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-100">
                    ⋮
                </button>
                <div id="menu-1" class="hidden absolute right-0 mt-2 w-36 bg-white border border-gray-200 rounded shadow-lg z-10">
                    <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">View Details</a>
                    <button class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-gray-100">Delete</button>
                </div>
            </div>
        </div>

        <!-- Sample Organization Card 2 -->
        <div class="bg-white rounded-lg shadow-md p-6 flex flex-col items-center text-center relative">
            <div class="w-28 h-28 mb-4 bg-gray-100 rounded-full flex items-center justify-center text-gray-400">
                <span>Logo</span>
            </div>
            <h3 class="text-lg font-semibold">Computer Society</h3>
            <p class="text-gray-600 mb-2"><span class="font-medium">CS</span></p>
            <p class="text-sm text-gray-600">Type: College-based</p>
            <p class="text-sm text-gray-600">College of Computing Studies</p>
            
            <div class="absolute top-2 right-2">
                <button onclick="toggleMenu('menu-2')" 
                    class="w-8 h-8 flex items-center justify-center border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-100">
                    ⋮
                </button>
                <div id="menu-2" class="hidden absolute right-0 mt-2 w-36 bg-white border border-gray-200 rounded shadow-lg z-10">
                    <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">View Details</a>
                    <button class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-gray-100">Delete</button>
                </div>
            </div>
        </div>

        <!-- Sample Organization Card 3 -->
        <div class="bg-white rounded-lg shadow-md p-6 flex flex-col items-center text-center relative">
            <div class="w-28 h-28 mb-4 bg-gray-100 rounded-full flex items-center justify-center text-gray-400">
                <span>Logo</span>
            </div>
            <h3 class="text-lg font-semibold">Math Club</h3>
            <p class="text-gray-600 mb-2"><span class="font-medium">MATH</span></p>
            <p class="text-sm text-gray-600">Type: College-based</p>
            
            <div class="absolute top-2 right-2">
                <button onclick="toggleMenu('menu-3')" 
                    class="w-8 h-8 flex items-center justify-center border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-100">
                    ⋮
                </button>
                <div id="menu-3" class="hidden absolute right-0 mt-2 w-36 bg-white border border-gray-200 rounded shadow-lg z-10">
                    <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">View Details</a>
                    <button class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-gray-100">Delete</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function toggleMenu(menuId) {
        // Close all other menus
        document.querySelectorAll('[id^="menu-"]').forEach(menu => {
            if (menu.id !== menuId) {
                menu.classList.add('hidden');
            }
        });
        // Toggle the clicked menu
        const menu = document.getElementById(menuId);
        if (menu) {
            menu.classList.toggle('hidden');
        }
    }

    // Close menu when clicking outside
    document.addEventListener('click', function(e) {
        if (!e.target.closest('[onclick^="toggleMenu"]')) {
            document.querySelectorAll('[id^="menu-"]').forEach(menu => {
                menu.classList.add('hidden');
            });
        }
    });
</script>
@endsection