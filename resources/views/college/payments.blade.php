
<div class="flex justify-end gap-2 px-2 mb-2">
    <button @click="openFilter = true" class="bg-gray-300 inline-flex items-center gap-2 px-4 py-2 text-sm font-medium rounded-lg hover:bg-gray-200 transition">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2l-6 7v5l-4 2v-7L3 6V4z" />
        </svg>
        Filters
    </button>
</div>

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

    {{-- If payment belongs to an organization --}}
    @if($payment->organization)

        @if($payment->organization->logo)
            <img 
                src="{{ asset('storage/' . $payment->organization->logo) }}"
                alt="{{ $payment->organization->name }}"
                class="w-6 h-6 rounded-full object-cover">
        @endif

        <span>{{ $payment->organization->name }}</span>

    {{-- If College Only --}}
    @else

        @if($college->logo)
            <img 
                src="{{ asset('storage/' . $college->logo) }}"
                alt="{{ $college->name }}"
                class="w-6 h-6 rounded-full object-cover">
        @endif

        <span>{{ $college->name }}</span>

    @endif

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
