@extends('student.layouts.student-dashboard')

@section('title', 'Student Dashboard')
@section('page-title', 'Student Dashboard')

@section('content')
<div class="mb-8 space-y-1">
    <h2 class="text-2xl font-bold text-gray-900 sm:text-3xl">Welcome</h2>
    <p class="text-sm text-gray-500 mt-1">View your enrollment details and latest payments.</p>
</div>

<div class="grid grid-cols-1 gap-4 mb-8 md:grid-cols-3">
    <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
        <p class="text-gray-500 text-sm">Student ID</p>
        <p class="break-words text-lg font-semibold text-gray-900">{{ $student->student_id }}</p>
    </div>
    <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
        <p class="text-gray-500 text-sm">Student Name</p>
        <p class="break-words text-lg font-semibold text-gray-900">{{ $student->full_name }}</p>
    </div>
    <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
        <p class="text-gray-500 text-sm">Current Enrollment</p>
        @if($currentEnrollment)
            <p class="text-lg font-semibold text-gray-900">{{ $currentEnrollment->course->name ?? '-' }}</p>
            <p class="mt-1 text-xs text-gray-500">{{ $currentEnrollment->yearLevel->name ?? '-' }} · {{ $currentEnrollment->section->name ?? '-' }}</p>
            <p class="mt-2 text-xs font-semibold uppercase tracking-wide text-gray-500">Status</p>
            <p class="text-base font-semibold text-gray-900">{{ str_replace('_', ' ', ucfirst(strtoupper($currentEnrollment->status))) }}</p>
        @else
            <p class="text-lg font-semibold text-gray-900">Not currently enrolled</p>
            <p class="mt-2 text-xs font-semibold uppercase tracking-wide text-gray-500">Status</p>
            <p class="text-base font-semibold text-gray-900">Not enrolled</p>
        @endif
    </div>
    {{-- @if(auth('student')->user()->hasOrganizationAccess())
    <form method="POST" action="{{ route('student.switch.org') }}">
        @csrf
        <button class="bg-blue-600 text-white px-3 py-1 rounded">
            Switch to Organization
        </button>
    </form>
@endif --}}

    <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
        <p class="text-gray-500 text-sm">Active Period</p>
        @if($activeSchoolYear && $activeSemester)
            <p class="text-lg font-semibold text-gray-900">
                {{ \Carbon\Carbon::parse($activeSchoolYear->sy_start)->format('Y') }}-{{ \Carbon\Carbon::parse($activeSchoolYear->sy_end)->format('Y') }}
            </p>
            <p class="text-xs text-gray-500 mt-1">{{ $activeSemester->name }}</p>
        @else
            <p class="text-lg font-semibold text-gray-900">Not set</p>
        @endif
    </div>
</div>

<div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-md sm:p-6">
    <div class="mb-4 flex items-center justify-between gap-3">
        <h3 class="text-lg font-semibold text-gray-900">Recent Payments</h3>
        <a href="{{ route('student.payments') }}" class="text-sm font-medium text-red-700 hover:underline">View all</a>
    </div>

    <div class="space-y-3 md:hidden">
        @forelse($recentPayments as $payment)
            <article class="rounded-2xl border border-gray-200 bg-gray-50 p-4">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Transaction ID</p>
                        <p class="break-all text-sm font-semibold text-gray-900">{{ $payment->transaction_id }}</p>
                        <p class="mt-2 text-xs text-gray-500">{{ $payment->organization->name ?? '-' }}</p>
                    </div>
                    <div class="shrink-0 text-right">
                        <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Amount</p>
                        <p class="text-base font-bold text-emerald-700">₱{{ number_format($payment->amount_due, 2) }}</p>
                    </div>
                </div>
                <div class="mt-4 flex items-center justify-between gap-3 text-sm">
                    <span class="text-gray-500">{{ $payment->created_at->format('M d, Y') }}</span>
                    <a href="{{ route('student.payments.receipt', $payment) }}" class="font-medium text-red-700 hover:underline">Download</a>
                </div>
            </article>
        @empty
            <div class="rounded-2xl border border-dashed border-gray-300 bg-white p-6 text-center text-sm text-gray-500">
                No payments yet.
            </div>
        @endforelse
    </div>

    <div class="hidden md:block">
        <div class="overflow-x-auto">
            <table class="min-w-full bg-white">
                <thead>
                    <tr class="bg-gray-100 text-sm text-gray-700">
                        <th class="py-3 px-4 border-b text-left">Transaction ID</th>
                        <th class="py-3 px-4 border-b text-left">Organization</th>
                        <th class="py-3 px-4 border-b text-left">Amount</th>
                        <th class="py-3 px-4 border-b text-left">Date</th>
                        <th class="py-3 px-4 border-b text-left">Receipt</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentPayments as $payment)
                        <tr class="hover:bg-gray-50">
                            <td class="py-3 px-4 border-b">{{ $payment->transaction_id }}</td>
                            <td class="py-3 px-4 border-b">{{ $payment->organization->name ?? '-' }}</td>
                            <td class="py-3 px-4 border-b font-semibold text-green-600">₱{{ number_format($payment->amount_due, 2) }}</td>
                            <td class="py-3 px-4 border-b">{{ $payment->created_at->format('M d, Y') }}</td>
                            <td class="py-3 px-4 border-b">
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
</div>
@endsection
