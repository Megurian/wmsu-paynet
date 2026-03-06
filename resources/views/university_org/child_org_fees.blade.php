@extends('layouts.dashboard')

@section('title', 'Organization Fees')
@section('page-title', 'Fees of ' . $org->name)

@section('content')
<div class="space-y-4">
    <a href="{{ route('university_org.child_organizations', [
        'school_year_id' => $selectedSY->id,
        'semester_id' => $selectedSem->id
    ]) }}" class="text-sm text-blue-600 hover:underline">
        ← Back to Child Organizations
    </a>

    <h2 class="text-lg font-semibold">Fees</h2>
<div class="p-4 bg-gray-50 rounded mb-4 flex items-center space-x-4">
    <p class="text-sm text-gray-700">
        <span class="font-semibold">School Year:</span> 
        {{ \Carbon\Carbon::parse($selectedSY->sy_start)->year }} - {{ \Carbon\Carbon::parse($selectedSY->sy_end)->year }}
    </p>
    <p class="text-sm text-gray-700">
        <span class="font-semibold">Semester:</span> 
        {{ ucfirst($selectedSem->name) }}
    </p>
</div>
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4 mb-4">

        <div class="p-4 bg-blue-100 rounded shadow text-center relative group">
            <p class="text-sm text-gray-600 flex items-center justify-center">
                Total Fees
                <span class="ml-1 text-xs cursor-pointer font-bold border rounded-full w-4 h-4 flex items-center justify-center bg-gray-200 text-gray-700 relative group">
                    ?
                    <span class="absolute bottom-full mb-1 left-1/2 transform -translate-x-1/2 hidden group-hover:block w-40 bg-gray-700 text-white text-xs rounded px-2 py-1 z-10">
                        Total number of distinct fees created for this organization.
                    </span>
                </span>
            </p>
            <p class="text-2xl font-bold">{{ $fees->count() }}</p>
        </div>

        @php
        $totalPossible = $fees->sum(fn($fee) => ($fee->paid_students->count() + $fee->pending_students->count()) * $fee->amount);
        @endphp

        <div class="p-4 bg-green-100 rounded shadow text-center relative group">
            <p class="text-sm text-gray-600 flex items-center justify-center">
                Total Possible Amount
                <span class="ml-1 text-xs cursor-pointer font-bold border rounded-full w-4 h-4 flex items-center justify-center bg-gray-200 text-gray-700 relative group">
                    ?
                    <span class="absolute bottom-full mb-1 left-1/2 transform -translate-x-1/2 hidden group-hover:block w-48 bg-gray-700 text-white text-xs rounded px-2 py-1 z-10">
                        The total amount all students are expected to pay for all fees.
                    </span>
                </span>
            </p>
            <p class="text-2xl font-bold">PHP {{ number_format($totalPossible, 2) }}</p>
        </div>

        <div class="p-4 bg-yellow-100 rounded shadow text-center relative group">
            <p class="text-sm text-gray-600 flex items-center justify-center">
                Paid Students
                <span class="ml-1 text-xs cursor-pointer font-bold border rounded-full w-4 h-4 flex items-center justify-center bg-gray-200 text-gray-700 relative group">
                    ?
                    <span class="absolute bottom-full mb-1 left-1/2 transform -translate-x-1/2 hidden group-hover:block w-44 bg-gray-700 text-white text-xs rounded px-2 py-1 z-10">
                        Number of students who have completed payment for their fees.
                    </span>
                </span>
            </p>
            <p class="text-2xl font-bold">{{ $fees->sum(fn($fee) => $fee->paid_students->count()) }}</p>
        </div>

        <div class="p-4 bg-red-100 rounded shadow text-center relative group">
            <p class="text-sm text-gray-600 flex items-center justify-center">
                Pending Payments
                <span class="ml-1 text-xs cursor-pointer font-bold border rounded-full w-4 h-4 flex items-center justify-center bg-gray-200 text-gray-700 relative group">
                    ?
                    <span class="absolute bottom-full mb-1 left-1/2 transform -translate-x-1/2 hidden group-hover:block w-44 bg-gray-700 text-white text-xs rounded px-2 py-1 z-10">
                        Number of students who still have unpaid fees.
                    </span>
                </span>
            </p>
            <p class="text-2xl font-bold">{{ $fees->sum(fn($fee) => $fee->pending_students->count()) }}</p>
        </div>

        <div class="p-4 bg-purple-100 rounded shadow text-center relative group">
            <p class="text-sm text-gray-600 flex items-center justify-center">
                Total Payments Collected
                <span class="ml-1 text-xs cursor-pointer font-bold border rounded-full w-4 h-4 flex items-center justify-center bg-gray-200 text-gray-700 relative group">
                    ?
                    <span class="absolute bottom-full mb-1 left-1/2 transform -translate-x-1/2 hidden group-hover:block w-52 bg-gray-700 text-white text-xs rounded px-2 py-1 z-10">
                        Total amount actually paid by students so far.
                    </span>
                </span>
            </p>
            <p class="text-2xl font-bold">
                PHP {{ number_format($fees->sum(fn($fee) => $fee->paid_students->count() * $fee->amount), 2) }}
            </p>
        </div>

        <div class="p-4 bg-indigo-100 rounded shadow text-center relative group">
            <p class="text-sm text-gray-600 flex items-center justify-center">
                Payment Completion Rate
                <span class="ml-1 text-xs cursor-pointer font-bold border rounded-full w-4 h-4 flex items-center justify-center bg-gray-200 text-gray-700 relative group">
                    ?
                    <span class="absolute bottom-full mb-1 left-1/2 transform -translate-x-1/2 hidden group-hover:block w-48 bg-gray-700 text-white text-xs rounded px-2 py-1 z-10">
                        Percentage of students who have completed all payments out of total students.
                    </span>
                </span>
            </p>
            <p class="text-2xl font-bold">
                @php
                $totalStudents = $fees->sum(fn($fee) => $fee->paid_students->count() + $fee->pending_students->count());
                $paidStudents = $fees->sum(fn($fee) => $fee->paid_students->count());
                @endphp
                {{ $totalStudents ? round(($paidStudents / $totalStudents) * 100) : 0 }}%
            </p>
        </div>

    </div>

    @if($fees->isEmpty())
    <p class="text-gray-500">No fees available.</p>
    @else
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        @foreach($fees as $fee)
        <div class="bg-white border rounded shadow space-y-4 p-4">

            {{-- Fee Header --}}
            <div class="flex justify-between items-center">
                <div>
                    <p class="font-semibold text-lg">{{ $fee->fee_name }}</p>
                    <p class="text-sm text-gray-600">
                        {{ $fee->requirement_level }} | PHP {{ number_format($fee->amount, 2) }} | {{ ucfirst($fee->status ?? 'pending') }}
                    </p>
                </div>
                <div class="text-sm text-gray-500">
                    Paid: {{ $fee->paid_students->count() }} | Pending: {{ $fee->pending_students->count() }}
                </div>
            </div>

            {{-- Tab Navigation --}}
            <div x-data="{ tab: 'paid' }" class="space-y-2">
                <div class="flex border-b border-gray-300">
                    <button @click="tab = 'paid'" :class="tab === 'paid' ? 'border-b-2 border-blue-500 text-blue-500' : 'text-gray-600'" class="px-4 py-2 text-sm font-medium focus:outline-none">
                        Paid Students ({{ $fee->paid_students->count() }})
                    </button>
                    <button @click="tab = 'pending'" :class="tab === 'pending' ? 'border-b-2 border-red-500 text-red-500' : 'text-gray-600'" class="px-4 py-2 text-sm font-medium focus:outline-none">
                        Pending Students ({{ $fee->pending_students->count() }})
                    </button>
                </div>

                {{-- Paid Students --}}
                <div x-show="tab === 'paid'" class="space-y-2 p-2 bg-green-50 rounded" x-cloak>
                    @if($fee->paid_students->isEmpty())
                    <p class="text-xs text-gray-500">No students have paid yet.</p>
                    @else
                    @foreach($fee->paid_students as $student)
                    @php
                        $payment = $student->payments()
                        ->where('organization_id', $org->id)
                        ->whereHas('fees', fn($q) => $q->where('fee_id', $fee->id))
                        ->first();
                        $enrollment = $student->enrollments()
                        ->where('college_id', $org->college_id)
                        ->where('school_year_id', $selectedSY->id)
                        ->where('semester_id', $selectedSem->id)
                        ->first();
                    @endphp
                    <div class="border rounded p-2 bg-white shadow-sm text-sm flex flex-col md:flex-row md:justify-between md:items-center">
                        <div class="space-y-1">
                            <p class="font-medium">{{ $student->first_name }} {{ $student->last_name }}</p>
                            <p class="text-gray-500 text-xs">ID: {{ $student->student_id }}</p>
                            <p class="text-gray-500 text-xs"> {{ $enrollment?->course?->name ?? '-' }} {{ $enrollment?->yearLevel?->name ?? '-' }}{{ $enrollment?->section?->name ?? '-' }}</p>
                        </div>
                        <div class="text-right space-y-1">
                            <p class="text-gray-700 font-medium">PHP {{ number_format($payment?->cash_received ?? 0, 2) }}</p>
                            <p class="text-xs text-green-700">Status: Paid</p>
                            <p class="text-xs text-gray-500">Transaction ID: {{ $payment?->transaction_id ?? '-' }}</p>
                            <p class="text-xs text-gray-500">Date: {{ $payment?->created_at?->format('M d, Y') ?? '-' }}</p>
                        </div>
                    </div>
                    @endforeach
                    @endif
                </div>

                {{-- Pending Students --}}
                <div x-show="tab === 'pending'" class="space-y-2 p-2 bg-red-50 rounded" x-cloak>
                    @if($fee->pending_students->isEmpty())
                    <p class="text-xs text-gray-500">No pending students.</p>
                    @else
                    @foreach($fee->pending_students as $student)
                    <div class="border rounded p-2 bg-white shadow-sm text-sm flex flex-col md:flex-row md:justify-between md:items-center">
                        <div class="space-y-1">
                            <p class="font-medium">{{ $student->first_name }} {{ $student->last_name }}</p>
                            <p class="text-gray-500 text-xs">ID: {{ $student->student_id }}</p>
                            <p class="text-gray-500 text-xs"> {{ $enrollment?->course?->name ?? '-' }} {{ $enrollment?->yearLevel?->name ?? '-' }}{{ $enrollment?->section?->name ?? '-' }}</p>
                        </div>
                        <div class="text-right space-y-1">
                            <p class="text-gray-700 font-medium">PHP 0.00</p>
                            <p class="text-xs text-red-700">Status: Pending</p>
                            <p class="text-xs text-gray-500">Date: -</p>
                        </div>
                    </div>
                    @endforeach
                    @endif
                </div>

            </div>

        </div>
        @endforeach
    </div>
    @endif
</div>
@endsection
