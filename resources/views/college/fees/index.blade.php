@extends('layouts.dashboard')

@section('title', 'College Fees')
@section('page-title', 'College Fees')

@section('content')
<div class="flex justify-between items-center mb-6">
    <h2 class="text-2xl font-bold text-gray-800">College Fees</h2>
    <a href="{{ route('college.fees.create') }}"
       class="bg-red-700 hover:bg-red-800 text-white px-4 py-2 rounded-lg shadow">
        + Add Fee
    </a>
</div>

<div class="bg-white shadow rounded-lg">
    @if($fees->isEmpty())
        <div class="text-center text-gray-500 py-8">
            No fees found. Click the "Add Fee" button to create one.
        </div>
    @else
        <div class="divide-y">
            @foreach($fees as $fee)
                <div class="p-4 hover:bg-gray-50 transition flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                    <!-- Fee Info -->
                    <div class="flex-1">
                        <h3 class="text-lg font-semibold text-gray-800">{{ $fee->fee_name }}</h3>
                        <p class="text-sm text-gray-600 mt-1">Amount: <span class="font-medium">₱{{ number_format($fee->amount, 2) }}</span></p>
                        <p class="text-sm text-gray-600">
                            <span class="capitalize font-medium">{{ $fee->requirement_level }}</span>
                        </p>

                        {{-- SOURCE DISPLAY (inherited fees) --}}
                        <p class="text-sm text-gray-600 mt-2">
                            From:
                            @if($fee->organization)
                                {{-- If it has a mother org (meaning this is an office) --}}
                                @if($fee->organization->motherOrganization)
                                    <span class="font-semibold">
                                        {{ $fee->organization->name }}
                                    </span>
                                    <span class="text-gray-400">
                                        (Office under {{ $fee->organization->motherOrganization->name }})
                                    </span>
                                @else
                                    <span class="font-semibold">
                                        {{ $fee->organization->name }}
                                    </span>
                                @endif
                            @else
                                <span class="font-semibold text-blue-700">
                                    College Fee (Student Coordinator)
                                </span>
                            @endif
                        </p>

                        <p class="text-sm text-gray-400 mt-1">Created: {{ $fee->created_at->format('M d, Y') }}</p>
                    </div>

                    <!-- Status Badge -->
                    <div class="flex items-center gap-2">
                        @if($fee->status === 'approved')
                            <span class="inline-block bg-green-100 text-green-800 text-xs font-semibold px-3 py-1 rounded-full">
                                Approved
                            </span>
                        @elseif($fee->status === 'pending')
                            <span class="inline-block bg-yellow-100 text-yellow-800 text-xs font-semibold px-3 py-1 rounded-full">
                                Pending
                            </span>
                        @elseif($fee->status === 'rejected')
                            <span class="inline-block bg-red-100 text-red-800 text-xs font-semibold px-3 py-1 rounded-full">
                                Rejected
                            </span>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection