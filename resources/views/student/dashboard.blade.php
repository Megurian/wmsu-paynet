@extends('student.layouts.student-dashboard')

@section('title', 'Student Dashboard')
@section('page-title', 'Student Dashboard')

@section('content')
<div class="mb-8">
    <h2 class="text-3xl font-bold text-gray-800">Welcome</h2>
    <p class="text-sm text-gray-500 mt-1">View your enrollment details and latest payments.</p>
</div>

<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
    <div class="bg-white p-5 rounded-lg shadow-sm border border-gray-200">
        <p class="text-gray-500 text-sm">Student ID</p>
        <p class="text-lg font-semibold text-gray-800">{{ $student->student_id }}</p>
    </div>

    <div class="bg-white p-5 rounded-lg shadow-sm border border-gray-200">
        <p class="text-gray-500 text-sm">Current Enrollment</p>
        @if($currentEnrollment)
            <p class="text-lg font-semibold text-gray-800">{{ $currentEnrollment->course->name ?? '-' }}</p>
            <p class="text-xs text-gray-500 mt-1">{{ $currentEnrollment->yearLevel->name ?? '-' }} · {{ $currentEnrollment->section->name ?? '-' }}</p>
        @else
            <p class="text-lg font-semibold text-gray-800">Not currently enrolled</p>
        @endif
    </div>

    <div class="bg-white p-5 rounded-lg shadow-sm border border-gray-200">
        <p class="text-gray-500 text-sm">Active Period</p>
        @if($activeSchoolYear && $activeSemester)
            <p class="text-lg font-semibold text-gray-800">
                {{ \Carbon\Carbon::parse($activeSchoolYear->sy_start)->format('Y') }}-{{ \Carbon\Carbon::parse($activeSchoolYear->sy_end)->format('Y') }}
            </p>
            <p class="text-xs text-gray-500 mt-1">{{ $activeSemester->name }}</p>
        @else
            <p class="text-lg font-semibold text-gray-800">Not set</p>
        @endif
    </div>
</div>

<div class="bg-white rounded-lg shadow-md p-6">
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-lg font-semibold text-gray-800">Recent Payments</h3>
        <a href="{{ route('student.payments') }}" class="text-sm text-red-700 hover:underline">View all</a>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full bg-white">
            <thead>
                <tr class="bg-gray-100">
                    <th class="py-2 px-4 border-b text-left">Transaction ID</th>
                    <th class="py-2 px-4 border-b text-left">Organization</th>
                    <th class="py-2 px-4 border-b text-left">Amount</th>
                    <th class="py-2 px-4 border-b text-left">Date</th>
                    <th class="py-2 px-4 border-b text-left">Receipt</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recentPayments as $payment)
                    <tr class="hover:bg-gray-50">
                        <td class="py-2 px-4 border-b">{{ $payment->transaction_id }}</td>
                        <td class="py-2 px-4 border-b">{{ $payment->organization->name ?? '-' }}</td>
                        <td class="py-2 px-4 border-b font-semibold text-green-600">₱{{ number_format($payment->amount_due, 2) }}</td>
                        <td class="py-2 px-4 border-b">{{ $payment->created_at->format('M d, Y') }}</td>
                        <td class="py-2 px-4 border-b">
                            <a href="{{ route('student.payments.receipt', $payment) }}" class="text-red-700 hover:underline">Download</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center py-6 text-gray-500">No payments yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
