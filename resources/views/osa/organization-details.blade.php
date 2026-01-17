@extends('layouts.dashboard')

@section('title', 'Organization Details')
@section('page-title', 'Organization Details')

@section('content')
<div class="bg-white rounded shadow p-6 max-w-2xl mx-auto">

    <div class="w-32 h-32 mb-4 mx-auto">
        @if($orgDetail?->logo)
            <img src="{{ asset('storage/'.$orgDetail->logo) }}" 
                 alt="{{ $orgDetail->name ?? 'Organization' }} Logo" 
                 class="w-full h-full object-cover rounded-full">
        @else
            <div class="w-full h-full bg-gray-100 flex items-center justify-center rounded-full text-gray-400 italic">
                No Logo
            </div>
        @endif
    </div>

    <h2 class="text-xl font-bold mb-2">{{ $orgDetail->name }}</h2>
    <p class="text-gray-600 mb-1"><strong>Code:</strong> {{ $orgDetail->org_code ?? 'N/A' }}</p>
    <p class="text-gray-600 mb-1"><strong>Type:</strong> 
        {{ $orgDetail->role === 'university_org' ? 'University-wide' : ($orgDetail->role === 'college_org' ? 'College-based' : 'N/A') }}
    </p>

    <p class="text-gray-600 mb-1"><strong>College:</strong> {{ $orgDetail->college?->name ?? 'N/A' }}</p>

    <p class="text-gray-600 mb-1"><strong>Initial Admin:</strong> {{ $orgDetail->admin?->name ?? 'N/A' }}</p>
    <p class="text-gray-600 mb-1"><strong>Email:</strong> {{ $orgDetail->admin?->email ?? 'N/A' }}</p>

    <a href="{{ route('osa.organizations') }}" 
       class="mt-4 inline-block px-4 py-2 bg-gray-500 text-white rounded hover:bg-gray-600">
        Back to Organizations
    </a>
</div>
@endsection
