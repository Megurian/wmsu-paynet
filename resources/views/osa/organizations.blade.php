@extends('layouts.dashboard')

@section('title', 'Organizations')
@section('page-title', 'OSA Organization Setup')

@section('content')
<h2 class="text-2xl font-bold mb-4">OSA Organization Setup</h2>

<a href="{{ route('osa.organizations.create') }}" 
   class="px-4 py-2 bg-red-700 text-white rounded mb-6 inline-block hover:bg-red-800 transition">
   + Add Organization
</a>

@if($organizations->count())
<div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-2 lg:grid-cols-3 gap-6">
    @foreach($organizations as $org)
    <div class="bg-white rounded shadow p-6 flex flex-col items-center text-center relative">

        <div class="w-28 h-28 mb-4">
            @if($org->logo)
                <img src="{{ asset('storage/'.$org->logo) }}" alt="{{ $org->name }} Logo" 
                     class="w-full h-full object-cover rounded-full">
            @else
                <div class="w-full h-full bg-gray-100 flex items-center justify-center rounded-full text-gray-400 italic">
                    No Logo
                </div>
            @endif
        </div>

        <h3 class="text-lg font-semibold">{{ $org->name }}</h3>
        <p class="text-gray-600 mb-2"><span class="font-medium">{{ $org->org_code }}</span></p>

        <p class="text-sm text-gray-600">Type: {{ $org->role === 'university_org' ? 'University-wide' : 'College-based' }}</p>
        @if($org->college)
            <p class="text-sm text-gray-600">College: {{ $org->college->name }}</p>
        @endif

        <div class="absolute top-2 right-2">
            <button onclick="document.getElementById('menu-{{ $org->id }}').classList.toggle('hidden')" 
                class="w-8 h-5 flex items-center justify-center border border-gray-300 rounded-lg text-gray-700 font-bold text-xl hover:bg-gray-100 focus:outline-none">
                &#x22EF; 
            </button>
            <div id="menu-{{ $org->id }}" class="hidden absolute right-0 mt-2 w-36 bg-white border border-gray-200 rounded shadow-lg z-10">
                <a href="{{ route('osa.organizations.show', $org->id) }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">View Details</a>
                <form action="{{ route('osa.organizations.destroy', $org->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this organization?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="w-full text-center px-4 py-2 text-sm text-red-600 hover:bg-gray-100">Delete</button>
                </form>
            </div>
        </div>
    </div>
    @endforeach
</div>
@else
<p class="text-gray-500 italic">No organizations created yet.</p>
@endif

<script>
    document.addEventListener('click', function(e) {
        document.querySelectorAll('[id^="menu-"]').forEach(menu => {
            if (!menu.contains(e.target) && !menu.previousElementSibling.contains(e.target)) {
                menu.classList.add('hidden');
            }
        });
    });
</script>
@endsection
