@extends('layouts.dashboard')

@section('title', 'Organization Fees')
@section('page-title', 'Fees of ' . $org->name)

@section('content')

<div class="max-w-6xl mx-auto space-y-6">

    {{-- Back Button --}}
    <div>
        <a href="{{ route('university_org.reports', [
            'school_year_id' => $selectedSY->id,
            'semester_id' => $selectedSem->id
        ]) }}" class="text-blue-600 hover:underline text-sm font-medium">
            ← Back to Reports
        </a>
    </div>


    {{-- Organization Info --}}
    <div class="bg-white rounded-2xl shadow-md border p-6 space-y-4">

        <div class="flex flex-col md:flex-row md:justify-between md:items-center">

            <div class="flex items-center gap-4">

                {{-- Logo --}}
                @if($org->logo)
                <img src="{{ asset('storage/'.$org->logo) }}" class="w-16 h-16 rounded-xl border object-cover">
                @else
                <div class="w-16 h-16 bg-gray-200 rounded-xl flex items-center justify-center text-xs text-gray-500">
                    No Logo
                </div>
                @endif

                <div>
                    <h1 class="text-2xl font-bold text-gray-900">
                        {{ $org->name }}
                    </h1>

                    <p class="text-sm text-gray-500">
                        Organization Code:
                        <span class="font-medium text-gray-700">
                            {{ $org->org_code ?? '-' }}
                        </span>
                    </p>
                </div>

            </div>

        </div>


        {{-- Info Grid --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">

            <div class="bg-gray-100 rounded-xl p-4 shadow-sm flex flex-col justify-center h-20">
                <span class="text-xs font-semibold text-gray-500">College</span>
                <span class="text-sm text-gray-800">
                    {{ $org->college?->name ?? '-' }}
                </span>
            </div>

            <div class="bg-gray-100 rounded-xl p-4 shadow-sm flex flex-col justify-center h-20">
                <span class="text-xs font-semibold text-gray-500">Organization Admin</span>
                <span class="text-sm text-gray-800">
                    {{ $org->orgAdmin?->first_name ?? '-' }}
                    {{ $org->orgAdmin?->last_name ?? '' }}
                </span>
            </div>

            <div class="bg-gray-100 rounded-xl p-4 shadow-sm flex flex-col justify-center h-20">
                <span class="text-xs font-semibold text-gray-500">Mother Organization</span>
                <span class="text-sm text-gray-800">
                    {{ $org->motherOrganization?->name ?? 'None' }}
                </span>
            </div>

            <div class="bg-gray-100 rounded-xl p-4 shadow-sm flex flex-col justify-center h-20">
                <span class="text-xs font-semibold text-gray-500">School Year & Semester</span>
                <span class="text-sm text-gray-800">
                    {{ \Carbon\Carbon::parse($selectedSY->sy_start)->year }} -
                    {{ \Carbon\Carbon::parse($selectedSY->sy_end)->year }}
                    • {{ ucfirst($selectedSem->name) }}
                </span>
            </div>

        </div>

    </div>


    {{-- Fee Statistics --}}
    @php
    $totalPossible = $fees->sum(fn($fee) => ($fee->paid_students->count() + $fee->pending_students->count()) * $fee->amount);
    $paidStudents = $fees->sum(fn($fee) => $fee->paid_students->count());
    $pendingStudents = $fees->sum(fn($fee) => $fee->pending_students->count());
    $totalStudents = $paidStudents + $pendingStudents;
    $collected = $fees->sum(fn($fee) => $fee->paid_students->count() * $fee->amount);
    @endphp


    <div class="bg-white rounded-2xl shadow-md border p-6 space-y-6">

        <h2 class="text-xl font-semibold text-gray-900">
            Fee Statistics
        </h2>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

            <div class="bg-gray-100 rounded-xl p-4 shadow-sm text-center">
                <p class="text-sm text-gray-600 flex items-center justify-center">
                    Total Fees
                    <span class="ml-1 text-xs cursor-pointer font-bold border rounded-full w-4 h-4 flex items-center justify-center bg-gray-200 text-gray-700 relative group"> ? <span class="absolute bottom-full mb-1 left-1/2 transform -translate-x-1/2 hidden group-hover:block w-40 bg-gray-700 text-white text-xs rounded px-2 py-1 z-10">
                            Total number of distinct fees created for this organization.
                        </span>
                    </span>
                </p>
                <p class="text-2xl font-bold">{{ $fees->count() }}</p>
            </div>

            <div class="bg-gray-100 rounded-xl p-4 shadow-sm text-center">
                <p class="text-sm text-gray-600 flex items-center justify-center"> Total Possible Amount <span class="ml-1 text-xs cursor-pointer font-bold border rounded-full w-4 h-4 flex items-center justify-center bg-gray-200 text-gray-700 relative group"> ? <span class="absolute bottom-full mb-1 left-1/2 transform -translate-x-1/2 hidden group-hover:block w-48 bg-gray-700 text-white text-xs rounded px-2 py-1 z-10">
                            The total amount all students are expected to pay for all fees.
                        </span>
                    </span>
                </p>
                <p class="text-2xl font-bold">PHP {{ number_format($totalPossible, 2) }}</p>
            </div>

            <div class="bg-gray-100 rounded-xl p-4 shadow-sm text-center">
                <p class="text-sm text-gray-600 flex items-center justify-center"> Total Payments Collected <span class="ml-1 text-xs cursor-pointer font-bold border rounded-full w-4 h-4 flex items-center justify-center bg-gray-200 text-gray-700 relative group"> ? <span class="absolute bottom-full mb-1 left-1/2 transform -translate-x-1/2 hidden group-hover:block w-52 bg-gray-700 text-white text-xs rounded px-2 py-1 z-10"> Total amount actually paid by students so far. </span> </span> </p>
                <p class="text-2xl font-bold"> PHP {{ number_format($fees->sum(fn($fee) => $fee->paid_students->count() * $fee->amount), 2) }} </p>
            </div>

            <div class="bg-gray-100 rounded-xl p-4 shadow-sm text-center">
                <p class="text-sm text-gray-600 flex items-center justify-center"> Paid Students <span class="ml-1 text-xs cursor-pointer font-bold border rounded-full w-4 h-4 flex items-center justify-center bg-gray-200 text-gray-700 relative group"> ? <span class="absolute bottom-full mb-1 left-1/2 transform -translate-x-1/2 hidden group-hover:block w-44 bg-gray-700 text-white text-xs rounded px-2 py-1 z-10"> Number of students who have completed payment for their fees. </span> </span> </p>
                <p class="text-2xl font-bold">{{ $fees->sum(fn($fee) => $fee->paid_students->count()) }}</p>
            </div>

            <div class="bg-gray-100 rounded-xl p-4 shadow-sm text-center">
                <p class="text-sm text-gray-600 flex items-center justify-center"> Pending Payments <span class="ml-1 text-xs cursor-pointer font-bold border rounded-full w-4 h-4 flex items-center justify-center bg-gray-200 text-gray-700 relative group"> ? <span class="absolute bottom-full mb-1 left-1/2 transform -translate-x-1/2 hidden group-hover:block w-44 bg-gray-700 text-white text-xs rounded px-2 py-1 z-10"> Number of students who still have unpaid fees. </span> </span> </p>
                <p class="text-2xl font-bold">{{ $fees->sum(fn($fee) => $fee->pending_students->count()) }}</p>
            </div>

            <div class="bg-gray-100 rounded-xl p-4 shadow-sm text-center">
                <p class="text-sm text-gray-600 flex items-center justify-center"> Payment Completion Rate <span class="ml-1 text-xs cursor-pointer font-bold border rounded-full w-4 h-4 flex items-center justify-center bg-gray-200 text-gray-700 relative group"> ? <span class="absolute bottom-full mb-1 left-1/2 transform -translate-x-1/2 hidden group-hover:block w-48 bg-gray-700 text-white text-xs rounded px-2 py-1 z-10"> Percentage of students who have completed all payments out of total students. </span> </span> </p>
                <p class="text-lg font-semibold text-gray-900 mt-1">
                    {{ $totalStudents ? round(($paidStudents/$totalStudents)*100) : 0 }}%
                </p>
            </div>

        </div>

    </div>


    {{-- Fees List --}}
    <div class="bg-white rounded-2xl shadow-md border p-6 space-y-6">

        <h2 class="text-xl font-semibold text-gray-900">
            Organization Fees
        </h2>

        @if($fees->isEmpty())
        <p class="text-gray-500">No fees available.</p>
        @else <div class="grid grid-cols-1 md:grid-cols-1 gap-4">
            @foreach($fees as $fee) <div class="bg-white border rounded shadow space-y-4 p-4">
                {{-- Fee Header --}}
                <div class="flex justify-between items-center">
                    <div>
                        <p class="font-semibold text-lg">{{ $fee->fee_name }}</p>
                        <p class="text-sm text-gray-600"> {{ $fee->requirement_level }} | PHP {{ number_format($fee->amount, 2) }} | {{ ucfirst($fee->status ?? 'pending') }} </p>
                    </div>
                    <div class="text-sm text-gray-500"> Paid: {{ $fee->paid_students->count() }} | Pending: {{ $fee->pending_students->count() }} </div>
                </div> {{-- Tab Navigation --}}
                <div x-data="{ tab: 'paid' }" class="space-y-2">
                    <div class="flex border-b border-gray-300"> <button @click="tab = 'paid'" :class="tab === 'paid' ? 'border-b-2 border-blue-500 text-blue-500' : 'text-gray-600'" class="px-4 py-2 text-sm font-medium focus:outline-none"> Paid Students ({{ $fee->paid_students->count() }}) </button> <button @click="tab = 'pending'" :class="tab === 'pending' ? 'border-b-2 border-red-500 text-red-500' : 'text-gray-600'" class="px-4 py-2 text-sm font-medium focus:outline-none"> Pending Students ({{ $fee->pending_students->count() }}) </button> </div> {{-- Paid Students --}}
                    <div x-show="tab === 'paid'" class="space-y-2 p-2 bg-green-50 rounded" x-cloak> @if($fee->paid_students->isEmpty()) <p class="text-xs text-gray-500">No students have paid yet.</p> @else @foreach($fee->paid_students as $student) @php
    $payment = $student->payments()
        ->with('fees')
        ->where('organization_id', $org->id)
        ->whereHas('fees', fn($q) => $q->where('fee_id', $fee->id))
        ->first();

    $enrollment = $student->enrollments()
        ->where('college_id', $org->college_id)
        ->where('school_year_id', $selectedSY->id)
        ->where('semester_id', $selectedSem->id)
        ->first();
@endphp <div class="border rounded p-2 bg-white shadow-sm text-sm flex flex-col md:flex-row md:justify-between md:items-center">
                            <div class="space-y-1">
                                <p class="font-medium">{{ $student->first_name }} {{ $student->last_name }}</p>
                                <p class="text-gray-500 text-xs">ID: {{ $student->student_id }}</p>
                                <p class="text-gray-500 text-xs"> {{ $enrollment?->course?->name ?? '-' }} {{ $enrollment?->yearLevel?->name ?? '-' }}{{ $enrollment?->section?->name ?? '-' }}</p>
                            </div>
                            @php
                                $feePayment = $payment?->fees->firstWhere('id', $fee->id);
                                $feeAmountPaid = $feePayment?->pivot->amount_paid ?? 0;
                            @endphp
                            <div class="text-right space-y-1">
                                <p class="text-gray-700 font-medium">PHP {{ number_format($feeAmountPaid, 2) }}</p>
                                <p class="text-xs text-green-700">Status: Paid</p>
                                <p class="text-xs text-gray-500">Transaction ID: {{ $payment?->transaction_id ?? '-' }}</p>
                                <p class="text-xs text-gray-500">Date: {{ $payment?->created_at?->format('M d, Y') ?? '-' }}</p>
                            </div>
                        </div> @endforeach @endif </div> {{-- Pending Students --}}
                    <div x-show="tab === 'pending'" class="space-y-2 p-2 bg-red-50 rounded" x-cloak> @if($fee->pending_students->isEmpty()) <p class="text-xs text-gray-500">No pending students.</p> @else @foreach($fee->pending_students as $student) <div class="border rounded p-2 bg-white shadow-sm text-sm flex flex-col md:flex-row md:justify-between md:items-center">
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
                        </div> @endforeach @endif </div>
                </div>
            </div> @endforeach </div> @endif
    </div>

</div>

@endsection
