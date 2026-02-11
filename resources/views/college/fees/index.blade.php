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

<div class="bg-white shadow rounded-lg p-6">
    @if($fees->isEmpty())
        <p class="text-gray-500 text-center py-6">No fees found. Click "Add Fee" to create one.</p>
    @else
        <ul class="space-y-4">
            @foreach($fees as $fee)
                <li class="border border-gray-200 rounded-lg p-4 flex flex-col md:flex-row md:justify-between md:items-center">
                    <div class="flex flex-col space-y-1">
                        <h3 class="text-lg font-semibold text-gray-800">{{ $fee->fee_name }}</h3>
                        <p class="text-gray-600">
                            ₱ {{ number_format($fee->amount, 2) }} • 
                            <span class="font-medium">
                                {{ ucfirst($fee->requirement_level) }}
                            </span>
                        </p>
                        <p class="text-gray-500 text-sm">{{ $fee->description ?? 'No description provided.' }}</p>
                    </div>

                    <div class="mt-3 md:mt-0 flex space-x-2 items-center">
                        @if($fee->status === 'approved')
                            <span class="inline-block bg-green-100 text-green-800 text-xs font-semibold px-2 py-1 rounded-full">
                                Approved
                            </span>
                        @elseif($fee->status === 'pending')
                            <span class="inline-block bg-yellow-100 text-yellow-800 text-xs font-semibold px-2 py-1 rounded-full">
                                Pending Approval
                            </span>
                        @elseif($fee->status === 'rejected')
                            <span class="inline-block bg-red-100 text-red-800 text-xs font-semibold px-2 py-1 rounded-full">
                                Rejected
                            </span>
                        @endif
                    </div>
                </li>
            @endforeach
        </ul>
    @endif
</div>
@endsection