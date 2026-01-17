@extends('layouts.dashboard')

@section('title', $orgDetail->name . ' Details')
@section('page-title', $orgDetail->name . ' Overview')

@section('content')

<a href="{{ route('osa.organizations') }}" class="inline-block mb-4 px-4 py-2 bg-gray-200 text-gray-800 rounded hover:bg-gray-300 transition">
    &larr; Back to Organizations
</a>

<!-- Organization Header -->
<div class="bg-white rounded shadow p-6 flex items-center space-x-6 mb-6">
    <div class="w-24 h-24 flex-shrink-0">
        @if($orgDetail->logo)
            <img src="{{ asset('storage/'.$orgDetail->logo) }}" alt="{{ $orgDetail->name }} Logo" class="w-full h-full object-cover rounded-full border">
        @else
            <div class="w-full h-full bg-gray-100 flex items-center justify-center rounded-full text-gray-400 italic border">
                No Logo
            </div>
        @endif
    </div>
    <div>
        <h2 class="text-2xl font-bold">{{ $orgDetail->name }}</h2>
        <p class="text-gray-600"> <span class="font-medium">{{ $orgDetail->org_code ?? 'N/A' }}</span></p>
        <p class="text-gray-600"> <span class="font-medium">
            {{ $orgDetail->role === 'university_org' ? 'University-wide' : ''}}
        </span></p>
        <p class="text-gray-600"> <span class="font-medium">{{ $orgDetail->college?->name ?? ' ' }}</span></p>
    </div>
</div>

<!-- Admin Section -->
<div class="bg-white rounded shadow p-6 mb-6">
    <h3 class="text-xl font-semibold mb-4">Organization Admin</h3>
    @if($orgDetail->admin)
        <div class="border rounded p-4 flex flex-col space-y-1">
            <p class="font-semibold">{{ $orgDetail->admin->name }}</p>
            <p class="text-gray-600 text-sm">{{ $orgDetail->admin->email }}</p>
        </div>
    @else
        <p class="text-gray-500 italic">No admin found for this organization yet.</p>
    @endif
</div>

<!-- Fees Section (Placeholder) -->
<div class="bg-white rounded shadow p-6">
    <h3 class="text-xl font-semibold mb-4">Fees</h3>
    <p class="text-gray-500 italic">Fees information for this organization will appear here.</p>

    <!-- Example placeholder table -->
    <table class="w-full text-left border-collapse mt-4">
        <thead>
            <tr class="bg-gray-100">
                <th class="border px-4 py-2">Fee Name</th>
                <th class="border px-4 py-2">Amount</th>
                <th class="border px-4 py-2">Description</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="border px-4 py-2">Tuition Fee</td>
                <td class="border px-4 py-2">₱0.00</td>
                <td class="border px-4 py-2">Placeholder description</td>
            </tr>
            <tr>
                <td class="border px-4 py-2">Miscellaneous Fee</td>
                <td class="border px-4 py-2">₱0.00</td>
                <td class="border px-4 py-2">Placeholder description</td>
            </tr>
        </tbody>
    </table>
</div>

@endsection
