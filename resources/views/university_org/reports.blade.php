@extends('layouts.dashboard')

@section('title', 'Payment Collection Report')
@section('page-title', $motherOrg->name . ' Payment Collection Report')

@section('content')
<!-- Filters -->
<div class="flex justify-start gap-4 mb-6">
    <form method="GET" class="flex gap-2">
        <select name="school_year_id" class="border rounded px-3 py-2">
            @foreach(\App\Models\SchoolYear::all() as $sy)
                <option value="{{ $sy->id }}" {{ $schoolYearId == $sy->id ? 'selected' : '' }}>
                    {{ $sy->name }}
                </option>
            @endforeach
        </select>
        <select name="semester_id" class="border rounded px-3 py-2">
            @foreach(\App\Models\Semester::all() as $sem)
                <option value="{{ $sem->id }}" {{ $semesterId == $sem->id ? 'selected' : '' }}>
                    {{ $sem->name }}
                </option>
            @endforeach
        </select>
        <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded">Filter</button>
    </form>
</div>

<!-- University Summary Cards -->
<div class="grid grid-cols-4 gap-4 mb-6">
    @php
        $totalStudents = $orgPayments->sum('total_students');
        $totalPaid = $orgPayments->sum('paid_count');
        $totalPending = $orgPayments->sum('pending_count');
        $totalCollected = $orgPayments->sum('total_collected');
        $paidPercentage = $totalStudents > 0 ? round(($totalPaid / $totalStudents) * 100) : 0;
    @endphp

    <div class="bg-white p-4 rounded shadow">
        <p class="text-sm text-gray-500">Total Students</p>
        <h3 class="text-xl font-bold">{{ $totalStudents }}</h3>
    </div>

    <div class="bg-white p-4 rounded shadow flex items-center justify-between">
        <div>
            <p class="text-sm text-gray-500">Students Paid</p>
            <h3 class="text-xl font-bold">{{ $totalPaid }}</h3>
        </div>
        <!-- Donut Chart -->
        <svg class="w-12 h-12" viewBox="0 0 36 36">
            <circle class="text-gray-200" stroke-width="4" stroke="currentColor" fill="transparent" cx="18" cy="18" r="16" />
            <circle
                class="text-green-500"
                stroke-width="4"
                stroke-dasharray="{{ $paidPercentage }}, 100"
                stroke-linecap="round"
                stroke="currentColor"
                fill="transparent"
                cx="18"
                cy="18"
                r="16"
                transform="rotate(-90 18 18)"
            />
            <text x="50%" y="50%" dominant-baseline="middle" text-anchor="middle" class="text-xs text-gray-700 font-semibold">{{ $paidPercentage }}%</text>
        </svg>
    </div>

    <div class="bg-white p-4 rounded shadow flex items-center justify-between">
        <div>
            <p class="text-sm text-gray-500">Pending Payments</p>
            <h3 class="text-xl font-bold">{{ $totalPending }}</h3>
        </div>
        <svg class="w-12 h-12" viewBox="0 0 36 36">
            <circle class="text-gray-200" stroke-width="4" stroke="currentColor" fill="transparent" cx="18" cy="18" r="16" />
            <circle
                class="text-yellow-500"
                stroke-width="4"
                stroke-dasharray="{{ 100 - $paidPercentage }}, 100"
                stroke-linecap="round"
                stroke="currentColor"
                fill="transparent"
                cx="18"
                cy="18"
                r="16"
                transform="rotate(-90 18 18)"
            />
            <text x="50%" y="50%" dominant-baseline="middle" text-anchor="middle" class="text-xs text-gray-700 font-semibold">{{ 100 - $paidPercentage }}%</text>
        </svg>
    </div>

    <div class="bg-white p-4 rounded shadow">
        <p class="text-sm text-gray-500">Total Collected</p>
        <h3 class="text-xl font-bold">₱ {{ number_format($totalCollected, 2) }}</h3>
    </div>
</div>

<!-- College-wise Summary and Breakdown -->
@foreach($orgPayments as $orgReport)
<div class="bg-gray-50 p-4 rounded mb-4 border">
    <div class="flex justify-between items-center">
        <h3 class="font-semibold text-lg">{{ $orgReport['organization']->name }} ({{ $orgReport['organization']->org_code }})</h3>
        <button class="text-blue-500 font-medium" onclick="document.getElementById('college-{{ $loop->index }}').classList.toggle('hidden')">
            View Details
        </button>
    </div>

    <div class="mt-2 flex justify-between text-sm text-gray-600">
        <p>Total Students: {{ $orgReport['total_students'] }}</p>
        <p>Paid: {{ $orgReport['paid_count'] }} / Pending: {{ $orgReport['pending_count'] }}</p>
        <p>Total Collected: ₱ {{ number_format($orgReport['total_collected'], 2) }}</p>
    </div>

    <!-- College Donut Chart -->
    <div class="flex justify-center mt-3">
        <svg class="w-16 h-16" viewBox="0 0 36 36">
            <circle class="text-gray-200" stroke-width="4" stroke="currentColor" fill="transparent" cx="18" cy="18" r="16" />
            <circle
                class="text-green-500"
                stroke-width="4"
                stroke-dasharray="{{ $orgReport['paid_percentage'] }}, 100"
                stroke-linecap="round"
                stroke="currentColor"
                fill="transparent"
                cx="18"
                cy="18"
                r="16"
                transform="rotate(-90 18 18)"
            />
            <text x="50%" y="50%" dominant-baseline="middle" text-anchor="middle" class="text-xs text-gray-700 font-semibold">{{ $orgReport['paid_percentage'] }}%</text>
        </svg>
    </div>

    <!-- Collapsible Table -->
    <div id="college-{{ $loop->index }}" class="mt-4 hidden">
        <table class="w-full border">
            <thead class="bg-gray-200">
                <tr>
                    <th class="px-2 py-1">#</th>
                    <th class="px-2 py-1">Student</th>
                    <th class="px-2 py-1">Fee</th>
                    <th class="px-2 py-1">Status</th>
                    <th class="px-2 py-1">Date Paid</th>
                    <th class="px-2 py-1 text-right">Amount</th>
                </tr>
            </thead>
            <tbody>
                @forelse($orgReport['payments'] as $index => $payment)
                    @foreach($payment->fees as $fee)
                        @php
                            $amountPaid = $fee->pivot->amount_paid ?? 0;
                            $status = $amountPaid >= $fee->amount ? 'Paid' : 'Pending';
                            $rowClass = $status === 'Paid' ? 'bg-green-50' : 'bg-yellow-50';
                        @endphp
                        <tr class="{{ $rowClass }}">
                            <td class="px-2 py-1">{{ $index + 1 }}</td>
                            <td class="px-2 py-1">{{ $payment->student->last_name }}, {{ $payment->student->first_name }}</td>
                            <td class="px-2 py-1">{{ $fee->fee_name }}</td>
                            <td class="px-2 py-1">{{ $status }}</td>
                            <td class="px-2 py-1">{{ $payment->created_at?->format('Y-m-d') ?? '-' }}</td>
                            <td class="px-2 py-1 text-right">₱ {{ number_format($amountPaid, 2) }}</td>
                        </tr>
                    @endforeach
                @empty
                    <tr>
                        <td colspan="6" class="text-center py-2">No payments yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endforeach
@endsection