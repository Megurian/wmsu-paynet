@extends('layouts.dashboard')

@section('title', 'College Dashboard')
@section('page-title', 'College Dashboard')

@section('content')
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
    <!-- Total Students -->
    <div class="bg-white shadow p-4 rounded-lg border border-gray-200">
        <h3 class="text-gray-500 text-sm font-semibold">Total Students</h3>
        <p class="text-2xl font-bold">{{ $totalStudents }}</p>
    </div>

    <div class="bg-white shadow p-4 rounded-lg border border-gray-200">
        <h3 class="text-gray-500 text-sm font-semibold">Total Fees</h3>
        <p class="text-2xl font-bold">{{ $totalFees }}</p>
    </div>

    <div class="bg-white shadow p-4 rounded-lg border border-gray-200">
        <h3 class="text-gray-500 text-sm font-semibold">Payments Collected</h3>
        <p class="text-2xl font-bold">{{ $totalPayments }}</p>
    </div>

    <div class="bg-white shadow p-4 rounded-lg border border-gray-200">
        <h3 class="text-gray-500 text-sm font-semibold">Pending Fee Approvals</h3>
        <p class="text-2xl font-bold">{{ $pendingApprovals }}</p>
    </div>
</div>

<div class="mb-6 bg-white shadow rounded-lg border border-gray-200 p-4">
    <h3 class="text-lg font-semibold mb-3">Recently Enrolled Students</h3>
    @if($recentStudents->isEmpty())
        <p class="text-gray-500">No recent students found.</p>
    @else
        <ul class="divide-y divide-gray-200">
            @foreach($recentStudents as $enrollment)
                <li class="py-2 flex justify-between">
                    <span>{{ $enrollment->student->full_name ?? $enrollment->student->first_name.' '.$enrollment->student->last_name }}</span>
                    <span class="text-gray-400 text-sm">{{ $enrollment->created_at->format('M d, Y') }}</span>
                </li>
            @endforeach
        </ul>
    @endif
</div>

<div class="mb-6 bg-white shadow rounded-lg border border-gray-200 p-4">
    <h3 class="text-lg font-semibold mb-3">Recently Added Fees</h3>
    @if($recentFees->isEmpty())
        <p class="text-gray-500">No recent fees found.</p>
    @else
        <ul class="divide-y divide-gray-200">
            @foreach($recentFees as $fee)
                <li class="py-2 flex justify-between">
                    <span>{{ $fee->fee_name }}</span>
                    <span class="text-gray-400 text-sm">{{ $fee->created_at->format('M d, Y') }}</span>
                </li>
            @endforeach
        </ul>
    @endif
</div>

<div class="mb-6 bg-white shadow rounded-lg border border-gray-200 p-4">
    <h3 class="text-lg font-semibold mb-3">Organizations under this College</h3>
    @if($organizations->isEmpty())
        <p class="text-gray-500">No organizations found.</p>
    @else
        <ul class="divide-y divide-gray-200">
            @foreach($organizations as $org)
                <li class="py-2 flex justify-between items-center">
                    <span>{{ $org->name }}</span>
                    <span class="text-gray-400 text-sm">{{ $org->role }}</span>
                </li>
            @endforeach
        </ul>
    @endif
</div>
@endsection