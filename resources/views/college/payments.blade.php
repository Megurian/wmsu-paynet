{{-- resources/views/college/history/payments.blade.php --}}
@if($payments->isEmpty())
<div class="p-8 text-center">
    <p class="text-gray-500 text-sm">No payment records found for the selected filters.</p>
</div>
@else
<table class="min-w-full text-sm">
    <thead class="bg-gray-50 border-b border-gray-200">
        <tr class="text-left text-[11px] font-semibold uppercase tracking-wide text-gray-600">
            <th class="px-4 py-3">#</th>
            <th class="px-5 py-3">Organization</th>
            <th class="px-5 py-3">Student</th>
            <th class="px-5 py-3">Fee Name</th>
            <th class="px-5 py-3">Amount Paid</th>
            <th class="px-5 py-3">Date Paid</th>
        </tr>
    </thead>
    <tbody class="divide-y divide-gray-100 text-gray-700">
        @foreach($payments as $payment)
        @foreach($payment->fees as $fee)
        <tr class="hover:bg-gray-50 transition">
            <td class="px-4 py-3 text-gray-500">{{ $loop->parent->iteration }}</td>

            <td class="px-5 py-3 flex items-center gap-2">
                @if($payment->organization?->logo)
                <img src="{{ asset('storage/' . $payment->organization->logo) }}" alt="{{ $payment->organization->name }}" class="w-6 h-6 rounded-full object-cover">
                @endif
                <span>{{ $payment->organization?->name ?? '—' }}</span>
            </td>

            <td class="px-5 py-3">
                {{ strtoupper($payment->student->last_name) }},
                {{ strtoupper($payment->student->first_name) }}
            </td>
            <td class="px-5 py-3">{{ $fee->fee_name }}</td>
            <td class="px-5 py-3">{{ number_format($fee->pivot->amount_paid, 2) }}</td>
            <td class="px-5 py-3">{{ $payment->created_at->format('F d, Y H:i') }}</td>
        </tr>
        @endforeach
        @endforeach
    </tbody>
</table>
@endif
