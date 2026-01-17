@extends('layouts.dashboard')

@section('title', 'Organization Details')
@section('page-title', 'Organization Details')

@section('content')
<div class="bg-white rounded shadow p-6 max-w-2xl mx-auto">

    <div class="w-32 h-32 mb-4 mx-auto">
        @if($organization->logo)
            <img src="{{ asset('storage/'.$organization->logo) }}" alt="{{ $organization->name }} Logo" 
                 class="w-full h-full object-cover rounded-full">
        @else
            <div class="w-full h-full bg-gray-100 flex items-center justify-center rounded-full text-gray-400 italic">
                No Logo
            </div>
        @endif
    </div>

    <h2 class="text-xl font-bold mb-2">{{ $organization->name }}</h2>
    <p class="text-gray-600 mb-1"><strong>Code:</strong> {{ $organization->org_code }}</p>
    <p class="text-gray-600 mb-1"><strong>Type:</strong> {{ $organization->role === 'university_org' ? 'University-wide' : 'College-based' }}</p>

    @if($organization->college)
        <p class="text-gray-600 mb-1"><strong>College:</strong> {{ $organization->college->name }}</p>
    @endif

    <p class="text-gray-600 mb-1"><strong>Initial Admin:</strong> {{ $organization->admin?->name ?? 'N/A' }}</p>
    <p class="text-gray-600 mb-1"><strong>Email:</strong> {{ $organization->admin?->email ?? 'N/A' }}</p>

    <a href="{{ route('osa.organizations') }}" class="mt-4 inline-block px-4 py-2 bg-gray-500 text-white rounded hover:bg-gray-600">Back to Organizations</a>
</div>
@endsection
