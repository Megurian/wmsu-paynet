@extends('layouts.dashboard')

@section('title', 'Academic Structure')
@section('page-title', 'Academic Structure')

@section('content')
@php
    $tab = request('tab', 'pending'); // default tab
@endphp

<div class="mb-6 flex space-x-2">
    <a href="{{ route('college.fees.approval', ['tab' => 'pending']) }}"
       class="px-4 py-2 rounded-full font-medium text-sm transition
       {{ $tab === 'pending' ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }}">
        Pending Fees
    </a>
    <a href="{{ route('college.fees.approval', ['tab' => 'approved']) }}"
       class="px-4 py-2 rounded-full font-medium text-sm transition
       {{ $tab === 'approved' ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }}">
        Approved Fees
    </a>
</div>

<div class="space-y-6">
    @if($tab === 'pending')
        @forelse($pendingFees as $fee)
            <div class="bg-white shadow rounded-lg p-4 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <!-- Fee Info -->
                <div class="flex-1">
                    <h3 class="text-lg font-semibold text-gray-800">{{ $fee->fee_name }}</h3>
                    <p class="text-sm text-gray-500 mt-1">Amount: <span class="font-medium">₱{{ number_format($fee->amount, 2) }}</span></p>
                    <p class="text-sm text-gray-500">Requirement: <span class="capitalize font-medium">{{ $fee->requirement_level }}</span></p>
                    <p class="text-sm text-gray-400 mt-1">Submitted on: {{ $fee->created_at->format('M d, Y') }}</p>
                </div>

                <!-- Action Buttons -->
                <div class="flex gap-2 md:gap-3">
                    <form method="POST" action="{{ route('college.fees.approve', $fee) }}">
                        @csrf
                        <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 transition">
                            Approve
                        </button>
                    </form>

                    <form method="POST" action="{{ route('college.fees.reject', $fee) }}">
                        @csrf
                        <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700 transition">
                            Reject
                        </button>
                    </form>
                </div>
            </div>
        @empty
            <div class="text-center text-gray-500 py-6">
                No pending fees to review.
            </div>
        @endforelse
    @elseif($tab === 'approved')
        @forelse($approvedFees as $fee)
            <div class="bg-white shadow rounded-lg p-4 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div class="flex-1">
                    <h3 class="text-lg font-semibold text-gray-800">{{ $fee->fee_name }}</h3>
                    <p class="text-sm text-gray-500 mt-1">Amount: <span class="font-medium">₱{{ number_format($fee->amount, 2) }}</span></p>
                    <p class="text-sm text-gray-500">Requirement: <span class="capitalize font-medium">{{ $fee->requirement_level }}</span></p>
                    <p class="text-sm text-gray-400 mt-1">
                        Approved on: {{ optional(\Illuminate\Support\Carbon::parse($fee->approved_at))->format('M d, Y') }}
                    </p>
                </div>
            </div>
        @empty
            <div class="text-center text-gray-500 py-6">
                No approved fees yet.
            </div>
        @endforelse
    @endif
</div>
@endsection