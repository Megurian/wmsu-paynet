@extends('layouts.dashboard')

@section('title', 'Fees')
@section('page-title', ($organization?->org_code ?? 'Organization') . ' Fees')


@section('content')
<div class="mb-8">
    <h2 class="text-3xl font-bold text-gray-800">{{ $organization->org_code }}  Setup of Fees</h2>
    <p class="text-sm text-gray-500 mt-1">
        Welcome, {{ Auth::user()->name }}. Here you can manage the fees associated with different colleges within the university.
    </p>
    <br>
    @if(session('success'))
        <div class="mb-4 p-4 bg-green-100 text-green-800 rounded">
            {{ session('success') }}
        </div>
    @endif
    <a href="{{ route('university_org.fees.create') }}" class="px-4 py-2 bg-red-700 text-white rounded hover:bg-red-800 transition inline-block">
            New Fee
    </a>
</div>

    <!-- Fees Section -->
    <div class="bg-white rounded shadow p-6">
    <h3 class="text-xl font-semibold mb-4">Fees</h3>
    <p class="text-gray-500 italic">Fees information for this organization will appear here.</p>

    <table class="w-full text-left border-collapse mt-4">
        <thead>
            <tr class="bg-gray-100">
                <th class="border px-4 py-2">Fee Name</th>
                <th class="border px-4 py-2">Purpose</th>
                <th class="border px-4 py-2">Amount</th>
                <th class="border px-4 py-2">Requirement</th>
                <th class="border px-4 py-2">Status</th>
                <th class="border px-4 py-2">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($fees as $fee)
                <tr>
                    <td class="border px-4 py-2">{{ $fee->fee_name }}</td>
                    <td class="border px-4 py-2">{{ $fee->purpose }}</td>
                    <td class="border px-4 py-2">₱{{ number_format($fee->amount, 2) }}</td>
                    <td class="border px-4 py-2 capitalize">{{ $fee->requirement_level }}</td>
                    <td class="border px-4 py-2 capitalize">{{ $fee->status }}</td>
                    <td class="border px-4 py-2">
                        <a href="{{ route('university_org.fees.show', $fee->id) }}" class="inline-flex items-center px-3 py-1 bg-blue-600 text-white rounded hover:bg-blue-700">View Details</a>
                        @if($fee->status !== 'approved')
                            <a href="{{ route('university_org.fees.edit', $fee->id) }}" class="ml-2 inline-flex items-center px-3 py-1 bg-yellow-500 text-white rounded hover:bg-yellow-600">Edit</a>
                            <form method="POST" action="{{ route('university_org.fees.destroy', $fee->id) }}" class="inline-block ml-2" onsubmit="return confirm('Are you sure you want to delete this fee? This action cannot be undone.')">
                                @csrf
                                @method('DELETE')
                                <button class="px-3 py-1 bg-red-600 text-white rounded hover:bg-red-700">Delete</button>
                            </form>
                        @else
                            <span class="ml-2 inline-block px-2 py-1 text-xs bg-gray-100 text-gray-600 rounded">Locked</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td class="border px-4 py-2" colspan="6">No fees found for this organization.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection