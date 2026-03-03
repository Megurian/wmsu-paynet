@extends('layouts.dashboard')

@section('title', 'Payment Collection Report')
@section('page-title', $motherOrg->name . ' Payment Collection Report')

@section('content')
<h2 class="text-2xl font-bold mb-4">{{ $motherOrg->name }} Payment Collection Report</h2>

@foreach($orgPayments as $orgReport)
    <div class="mb-6 border p-4 rounded bg-gray-50">
        <h3 class="font-semibold">{{ $orgReport['organization']->name }} ({{ $orgReport['organization']->org_code }})</h3>
        <p>Total Collected: ₱ {{ number_format($orgReport['total_collected'], 2) }}</p>

        <table class="w-full border mt-2">
            <thead class="bg-gray-200">
                <tr>
                    <th>#</th>
                    <th>Student</th>
                    <th>Fee</th>
                    <th>Date Paid</th>
                    <th class="text-right">Amount</th>
                </tr>
            </thead>
            <tbody>
                @forelse($orgReport['payments'] as $index => $payment)
                    @foreach($payment->fees as $fee)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $payment->student->last_name }}, {{ $payment->student->first_name }}</td>
                            <td>{{ $fee->fee_name }}</td>
                            <td>{{ $payment->created_at->format('Y-m-d') }}</td>
                            <td class="text-right">₱ {{ number_format($fee->pivot->amount_paid ?? $fee->amount, 2) }}</td>
                        </tr>
                    @endforeach
                @empty
                    <tr>
                        <td colspan="5" class="text-center">No payments yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endforeach
@endsection