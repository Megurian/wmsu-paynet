@extends('student.layouts.student-dashboard')

@section('title', 'My Payments')
@section('page-title', 'My Payments')

@section('content')
<div class="mb-8">
    <h2 class="text-3xl font-bold text-gray-800">Payment History</h2>
    <p class="text-sm text-gray-500 mt-1">View your payment records and download receipts.</p>
</div>

<div class="bg-white rounded-lg shadow-md p-6">
    <div class="overflow-x-auto">
        <table class="min-w-full bg-white">
            <thead>
                <tr class="bg-gray-100">
                    <th class="py-2 px-4 border-b text-left">Transaction ID</th>
                    <th class="py-2 px-4 border-b text-left">Fee</th>
                    <th class="py-2 px-4 border-b text-left">Amount</th>
                    <th class="py-2 px-4 border-b text-left">Organization</th>
                    <th class="py-2 px-4 border-b text-left">Date</th>
                    <th class="py-2 px-4 border-b text-left">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($payments as $payment)
                <tr class="hover:bg-gray-50">
                    <td class="py-2 px-4 border-b">{{ $payment->transaction_id }}</td>

                    <td class="py-2 px-4 border-b">
                        @foreach($payment->fees as $fee)
                            <div>{{ $fee->fee_name }}</div>
                        @endforeach
                    </td>

                    <td class="py-2 px-4 border-b font-semibold text-green-600">₱{{ number_format($payment->amount_due, 2) }}</td>
                    <td class="py-2 px-4 border-b">{{ $payment->organization->name ?? '-' }}</td>
                    <td class="py-2 px-4 border-b">{{ $payment->created_at->format('M d, Y') }}</td>
                    <td class="py-2 px-4 border-b">
                        <a href="{{ route('student.payments.receipt', $payment) }}" class="text-red-700 hover:underline">Download Receipt</a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center py-6 text-gray-500">
                        No payment records found.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $payments->links() }}
    </div>
</div>
@endsection
