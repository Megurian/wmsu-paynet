@extends('student.layouts.student-dashboard')

@section('title', 'My Payments')
@section('page-title', 'My Payments')

@section('content')
<div class="mb-8 space-y-1">
    <h2 class="text-2xl font-bold text-gray-900 sm:text-3xl">Payment History</h2>
    <p class="text-sm text-gray-500 mt-1">View your payment records and download receipts.</p>
</div>

<div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-md sm:p-6">
    <div class="space-y-3 md:hidden">
        @forelse($payments as $payment)
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

                <div class="mt-4 rounded-xl bg-white px-3 py-3">
                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Fee</p>
                    <div class="mt-1 space-y-1 text-sm text-gray-700">
                        @foreach($payment->fees as $fee)
                            <div>{{ $fee->fee_name }}</div>
                        @endforeach
                    </div>
                </div>

                <div class="mt-4 flex items-center justify-between gap-3 text-sm">
                    <span class="text-gray-500">{{ $payment->created_at->format('M d, Y') }}</span>
                    <a href="{{ route('student.payments.receipt', $payment) }}" class="font-medium text-red-700 hover:underline">Download Receipt</a>
                </div>
            </article>
        @empty
            <div class="rounded-2xl border border-dashed border-gray-300 bg-white p-6 text-center text-sm text-gray-500">
                No payment records found.
            </div>
        @endforelse
    </div>

    <div class="hidden md:block">
        <div class="overflow-x-auto">
            <table class="min-w-full bg-white">
                <thead>
                    <tr class="bg-gray-100 text-sm text-gray-700">
                        <th class="py-3 px-4 border-b text-left">Transaction ID</th>
                        <th class="py-3 px-4 border-b text-left">Fee</th>
                        <th class="py-3 px-4 border-b text-left">Amount</th>
                        <th class="py-3 px-4 border-b text-left">Organization</th>
                        <th class="py-3 px-4 border-b text-left">Date</th>
                        <th class="py-3 px-4 border-b text-left">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($payments as $payment)
                    <tr class="hover:bg-gray-50">
                        <td class="py-3 px-4 border-b">{{ $payment->transaction_id }}</td>

                        <td class="py-3 px-4 border-b">
                            @foreach($payment->fees as $fee)
                                <div>{{ $fee->fee_name }}</div>
                            @endforeach
                        </td>

                        <td class="py-3 px-4 border-b font-semibold text-green-600">₱{{ number_format($payment->amount_due, 2) }}</td>
                        <td class="py-3 px-4 border-b">{{ $payment->organization->name ?? '-' }}</td>
                        <td class="py-3 px-4 border-b">{{ $payment->created_at->format('M d, Y') }}</td>
                        <td class="py-3 px-4 border-b">
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
    </div>

    <div class="mt-4">
        {{ $payments->links() }}
    </div>
</div>
@endsection
