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

    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4 mb-4">

    <div class="p-4 bg-blue-100 rounded shadow text-center">
        <p class="text-sm text-gray-600">Total Fees</p>
        <p class="text-2xl font-bold">{{ $fees->count() }}</p>
    </div>

    <div class="p-4 bg-green-100 rounded shadow text-center">
        <p class="text-sm text-gray-600">Total Amount</p>
        <p class="text-2xl font-bold">
            PHP {{ number_format($fees->sum('amount'), 2) }}
        </p>
    </div>

    <div class="p-4 bg-yellow-100 rounded shadow text-center">
        <p class="text-sm text-gray-600">Paid Students</p>
        <p class="text-2xl font-bold">
            {{ $fees->sum(fn($fee) => $fee->paid_students->count()) }}
        </p>
    </div>

    <div class="p-4 bg-red-100 rounded shadow text-center">
        <p class="text-sm text-gray-600">Pending Students</p>
        <p class="text-2xl font-bold">
            {{ $fees->sum(fn($fee) => $fee->pending_students->count()) }}
        </p>
    </div>

    <div class="p-4 bg-purple-100 rounded shadow text-center">
        <p class="text-sm text-gray-600">Total Payments Collected</p>
        <p class="text-2xl font-bold">
            PHP {{ number_format($fees->sum(fn($fee) => $fee->paid_students->count() * $fee->amount), 2) }}
        </p>
    </div>

    <div class="p-4 bg-indigo-100 rounded shadow text-center">
        <p class="text-sm text-gray-600">Payment Completion Rate</p>
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
        <ul class="space-y-4">
            @foreach($fees as $fee)
            <li class="p-3 bg-gray-50 border rounded space-y-2">

                <div class="flex justify-between items-center">
                    <div>
                        <p class="font-medium">{{ $fee->fee_name }}</p>
                        <p class="text-sm text-gray-600">
                            {{ $fee->requirement_level }} | PHP {{ number_format($fee->amount, 2) }} | {{ ucfirst($fee->status ?? 'pending') }}
                        </p>
                    </div>
                    <div class="text-xs text-gray-500">
                        Paid: {{ $fee->paid_students->count() }} | Pending: {{ $fee->pending_students->count() }}
                    </div>
                </div>

                <div class="bg-green-50 p-2 rounded">
                    <p class="text-sm font-semibold text-green-700 mb-1">Paid Students ({{ $fee->paid_students->count() }})</p>
                    @if($fee->paid_students->isEmpty())
                        <p class="text-xs text-gray-500">No students have paid yet.</p>
                    @else
                        <ul class="list-disc list-inside text-xs text-gray-700">
                            @foreach($fee->paid_students as $student)
                                <li>{{ $student->first_name }} {{ $student->last_name }} (ID: {{ $student->student_id }})</li>
                            @endforeach
                        </ul>
                    @endif
                </div>

                <div class="bg-red-50 p-2 rounded">
                    <p class="text-sm font-semibold text-red-700 mb-1">Pending Students ({{ $fee->pending_students->count() }})</p>
                    @if($fee->pending_students->isEmpty())
                        <p class="text-xs text-gray-500">No pending students.</p>
                    @else
                        <ul class="list-disc list-inside text-xs text-gray-700">
                            @foreach($fee->pending_students as $student)
                                <li>{{ $student->first_name }} {{ $student->last_name }} (ID: {{ $student->student_id }})</li>
                            @endforeach
                        </ul>
                    @endif
                </div>

            </li>
            @endforeach
        </ul>
    @endif
</div>
@endsection