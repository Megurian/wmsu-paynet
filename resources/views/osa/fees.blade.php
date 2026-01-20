@extends('layouts.dashboard')

@section('title', 'OSA Fees')
@section('page-title', 'OSA Fees')

@section('content')
<div class="mb-8">
    <h2 class="text-3xl font-bold text-gray-800">OSA Fee Approval</h2>
    <p class="text-sm text-gray-500 mt-1">
        Welcome, {{ Auth::user()->name }}. Here you can manage the fees associated with different colleges within the university.
    </p>
    <br>
</div>

<!-- Fees Section -->
<div class="bg-white rounded shadow p-6">
    <h3 class="text-xl font-semibold mb-4">Fee Approval Request</h3>
    <p class="text-gray-500 italic">Fee approval request for every organization will appear here.</p>

    <table class="w-full text-left border-collapse mt-4">
        <thead>
            <tr class="bg-gray-100">
                <th class="border px-4 py-2 text-center">Organization</th>
                <th class="border px-4 py-2 text-center">Action</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="border px-4 py-2">
                    <div class="flex items-center space-x-4">
                        <div class="w-10 h-10 bg-gray-200 rounded-full flex items-center justify-center">
                            <span class="text-gray-500">LOGO</span>
                        </div>
                        <span>Supreme Student Government</span>
                    </div>
                </td>
                <td class="border px-4 py-2">
                    <div class="relative flex justify-center">
                        <button onclick="toggleMenu('menu-1')" 
                            class="w-8 h-8 flex items-center justify-center border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-100">
                            ⋮
                        </button>
                        <div id="menu-1" class="hidden absolute right-0 mt-8 w-48 bg-white rounded-md shadow-lg z-10">
                            <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">View Details</a>
                        </div>
                    </div>
                </td>
            </tr>
            <tr>
                <td class="border px-4 py-2">
                    <div class="flex items-center space-x-4">
                        <div class="w-10 h-10 bg-gray-200 rounded-full flex items-center justify-center">
                            <span class="text-gray-500">LOGO</span>
                        </div>
                        <span>Computer Science Club</span>
                    </div>
                </td>
                <td class="border px-4 py-2">
                    <div class="relative flex justify-center">
                        <button onclick="toggleMenu('menu-2')" 
                            class="w-8 h-8 flex items-center justify-center border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-100">
                            ⋮
                        </button>
                        <div id="menu-2" class="hidden absolute right-0 mt-8 w-48 bg-white rounded-md shadow-lg z-10">
                            <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">View Details</a>
                        </div>
                    </div>
                </td>
            </tr>
        </tbody>
    </table>
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
document.addEventListener('click', function(event) {
    if (!event.target.matches('button') && !event.target.closest('.relative')) {
        document.querySelectorAll('[id^="menu-"]').forEach(menu => {
            menu.classList.add('hidden');
        });
    }
});
</script>

<style>
/* Add smooth transition for dropdown */
[id^="menu-"] {
    transition: opacity 0.2s ease-in-out;
}
</style>
@endsection