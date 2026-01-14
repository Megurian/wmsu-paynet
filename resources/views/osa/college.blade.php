@extends('layouts.dashboard')

@section('title', 'OSA Colleges')
@section('page-title', 'OSA College Setup')

@section('content')
<h2 class="text-2xl font-bold mb-4">OSA College Setup</h2>

<a href="{{ route('osa.college.create') }}" class="px-4 py-2 bg-red-700 text-white rounded mb-6 inline-block hover:bg-red-800 transition">
    Add New College
</a>

@if($colleges->count())
<div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
    @foreach($colleges as $college)
    <div class="bg-white rounded shadow p-4 flex flex-col items-center text-center relative">

        <!-- College Logo -->
        <div class="w-24 h-24 mb-4">
            @if($college->logo)
                <img src="{{ asset('storage/'.$college->logo) }}" alt="{{ $college->name }} Logo" class="w-full h-full object-cover rounded-full">
            @else
                <div class="w-full h-full bg-gray-100 flex items-center justify-center rounded-full text-gray-400 italic">
                    No Logo
                </div>
            @endif
        </div>

        <!-- College Name & Code -->
        <h3 class="text-lg font-semibold">{{ $college->name }}</h3>
        <p class="text-gray-600 mb-2"><span class="font-medium">{{ $college->college_code }}</span></p>
 
        <div class="absolute top-2 right-2">
            <button onclick="document.getElementById('menu-{{ $college->id }}').classList.toggle('hidden')" 
                class="w-8 h-5 flex items-center justify-center border border-gray-300 rounded-lg text-gray-700 font-bold text-xl hover:bg-gray-100 focus:outline-none">
                &#x22EF; 
            </button>
            <div id="menu-{{ $college->id }}" class="hidden absolute right-0 mt-2 w-36 bg-white border border-gray-200 rounded shadow-lg z-10">
                <a href="{{ route('osa.college.details', $college->id) }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">View Details</a>
                <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Edit</a>
                <form action="{{ route('osa.college.destroy', $college->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this college?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-gray-100">Delete</button>
                </form>
            </div>
        </div>

    </div>
    @endforeach
</div>
@else
<p class="text-gray-500 italic">No colleges created yet.</p>
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
