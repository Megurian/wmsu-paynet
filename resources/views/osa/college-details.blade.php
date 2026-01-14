@extends('layouts.dashboard')

@section('title', $college->name . ' Details')
@section('page-title', $college->name . ' Overview')

@section('content')

<a href="{{ route('osa.college') }}" class="inline-block mb-4 px-4 py-2 bg-gray-200 text-gray-800 rounded hover:bg-gray-300 transition">
    &larr; Back to Colleges
</a>

<!-- College Header -->
<div class="bg-white rounded shadow p-6 flex items-center space-x-6 mb-6">
    <div class="w-24 h-24 flex-shrink-0">
        @if($college->logo)
            <img src="{{ asset('storage/'.$college->logo) }}" alt="{{ $college->name }} Logo" class="w-full h-full object-cover rounded-full border">
        @else
            <div class="w-full h-full bg-gray-100 flex items-center justify-center rounded-full text-gray-400 italic border">
                No Logo
            </div>
        @endif
    </div>
    <div>
        <h2 class="text-2xl font-bold">{{ $college->name }}</h2>
        <p class="text-gray-600">College Code: <span class="font-medium">{{ $college->college_code }}</span></p>
    </div>
</div>

<!-- Admins Section -->
<div class="bg-white rounded shadow p-6 mb-6">
    <h3 class="text-xl font-semibold mb-4">College Admins</h3>
    @if($college->admins->count())
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
            @foreach($college->admins as $admin)
            <div class="border rounded p-4 flex flex-col space-y-1">
                <p class="font-semibold">{{ $admin->name }}</p>
                <p class="text-gray-600 text-sm">{{ $admin->email }}</p>
            </div>
            @endforeach
        </div>
    @else
        <p class="text-gray-500 italic">No admins found for this college yet.</p>
    @endif
</div>

<!-- Organizations Section -->
<div class="bg-white rounded shadow p-6">
    <h3 class="text-xl font-semibold mb-4">Organizations</h3>
    @if(count($organizations))
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
            @foreach($organizations as $org)
            <div class="border rounded p-4 flex flex-col space-y-1">
                <p class="font-semibold">{{ $org->name }}</p>
                <p class="text-gray-600 text-sm">{{ $org->description }}</p>
            </div>
            @endforeach
        </div>
    @else
        <p class="text-gray-500 italic">No organizations found for this college yet.</p>
    @endif
</div>

@endsection
