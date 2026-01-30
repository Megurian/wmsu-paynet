@extends('layouts.dashboard')

@section('title', 'Offices')
@section('page-title', ($organization?->org_code ?? 'Organization') . ' Offices Setup')

@section('content')
<div class="mb-8">
    <h2 class="text-3xl font-bold text-gray-800"> {{ ($organization?->org_code ?? 'Organization') . " Offices" }} </h2>
    <p class="text-sm text-gray-500 mt-1">
        Welcome, {{ Auth::user()->name }}. Here you can manage the Offices associated with different colleges within the university.
    </p> <br>
    <a class="px-4 py-2 bg-red-700 text-white rounded hover:bg-red-800 transition" href="{{ route('university_org.offices.create') }}">
            New Office
    </a>
</div>

@if($organizations->isEmpty())
    <div class="bg-white rounded-lg shadow-md p-6">
        <p class="text-gray-600">No offices have been created yet.</p>
    </div>
@else
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach($organizations as $org)
            <div class="bg-white rounded-lg shadow-md p-6 flex flex-col items-center text-center relative">
                <div class="w-28 h-28 mb-4 bg-gray-100 rounded-full flex items-center justify-center text-gray-400 overflow-hidden">
                    @if($org->logo)
                        <img src="{{ asset('storage/' . $org->logo) }}" alt="{{ $org->name }} logo" class="w-full h-full object-cover" />
                    @else
                        <span>Logo</span>
                    @endif
                </div>
                <h3 class="text-lg font-semibold">{{ $org->name }}</h3>
                <p class="text-gray-600 mb-2"><span class="font-medium">{{ $org->org_code }}</span></p>
                <p class="text-sm text-gray-600">Type: {{ $org->role === 'college_org' ? 'College-based' : ucfirst($org->role) }}</p>
                @if($org->college)
                    <p class="text-sm text-gray-600">{{ $org->college->name }}</p>
                @endif

                <div class="absolute top-2 right-2">
                    <button onclick="toggleMenu('menu-{{ $loop->iteration }}')" 
                        class="w-8 h-8 flex items-center justify-center border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-100">
                        ⋮
                    </button>
                    <div id="menu-{{ $loop->iteration }}" class="hidden absolute right-0 mt-2 w-36 bg-white border border-gray-200 rounded shadow-lg z-10">
                        <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">View Details</a>
                        <button class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-gray-100">Delete</button>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endif
</div>

<script>
    function toggleMenu(menuId) {
        document.querySelectorAll('[id^="menu-"]').forEach(menu => {
            if (menu.id !== menuId) {
                menu.classList.add('hidden');
            }
        });
        const menu = document.getElementById(menuId);
        if (menu) {
            menu.classList.toggle('hidden');
        }
    }

    document.addEventListener('click', function(e) {
        if (!e.target.closest('[onclick^="toggleMenu"]')) {
            document.querySelectorAll('[id^="menu-"]').forEach(menu => {
                menu.classList.add('hidden');
            });
        }
    });
</script>
@endsection