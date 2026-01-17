@extends('layouts.dashboard')

@section('title', 'Organizations')
@section('page-title', 'Organizations')

@section('content')
<div class="flex justify-between items-center mb-6">
    <h2 class="text-2xl font-bold">Organizations</h2>
    <a href="{{ route('osa.organizations.create') }}" class="bg-red-700 text-white px-4 py-2 rounded hover:bg-red-800">+ Add Organization</a>
</div>

<div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6">
    @foreach($organizations as $org)
        <div class="border rounded-lg p-4 shadow hover:shadow-md transition">
            <h3 class="font-semibold text-lg">{{ $org->name }}</h3>
            <p class="text-sm text-gray-600">Type: {{ $org->role === 'university_org' ? 'University-wide' : 'College' }}</p>
            @if($org->college)
                <p class="text-sm text-gray-600">College: {{ $org->college->name }}</p>
            @endif
            <p class="text-sm text-gray-500">Admin: {{ $org->admin?->name ?? 'N/A' }}</p>
        </div>
    @endforeach
</div>
@endsection
