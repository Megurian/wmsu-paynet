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
    <div class="bg-white rounded shadow p-4 flex flex-col items-center text-center">
        <div class="w-24 h-24 mb-4">
            @if($college->logo)
                <img src="{{ asset('storage/'.$college->logo) }}" alt="{{ $college->name }} Logo" class="w-full h-full object-cover rounded-full">
            @else
                <div class="w-full h-full bg-gray-100 flex items-center justify-center rounded-full text-gray-400 italic">
                    No Logo
                </div>
            @endif
        </div>

        <h3 class="text-lg font-semibold">{{ $college->name }}</h3>
        <p class="text-gray-600 mb-2">Code: <span class="font-medium">{{ $college->college_code }}</span></p>

        <a href="{{ route('osa.college.details', $college->id) }}" class="mt-auto px-3 py-1 bg-blue-600 text-white rounded hover:bg-blue-700 transition">
            View Details
        </a>
    </div>
    @endforeach
</div>
@else
<p class="text-gray-500 italic">No colleges created yet.</p>
@endif
@endsection
