<div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4 mb-4">
    <div class="bg-blue-50 text-blue-700 rounded-lg p-4 shadow-sm flex flex-col items-center">
        <span class="text-xs font-semibold uppercase">Total Payments</span>
        <span class="text-xl font-bold">{{ $totalPayments }}</span>
    </div>

    <div class="bg-green-50 text-green-700 rounded-lg p-4 shadow-sm flex flex-col items-center">
        <span class="text-xs font-semibold uppercase">Total Amount Collected</span>
        <span class="text-xl font-bold">₱ {{ number_format($totalAmount, 2) }}</span>
    </div>

    <div class="bg-yellow-50 text-yellow-700 rounded-lg p-4 shadow-sm flex flex-col items-center">
        <span class="text-xs font-semibold uppercase">Mandatory Fees</span>
        <span class="text-xl font-bold">₱ {{ number_format($requirementBreakdown['mandatory'] ?? 0, 2) }}</span>
    </div>

    <div class="bg-gray-100 text-gray-700 rounded-lg p-4 shadow-sm flex flex-col items-center">
        <span class="text-xs font-semibold uppercase">Optional Fees</span>
        <span class="text-xl font-bold">₱ {{ number_format($requirementBreakdown['optional'] ?? 0, 2) }}</span>
    </div>
</div>

<div class="mb-4">
    <h3 class="text-sm font-semibold mb-2">Breakdown by Organization</h3>
    <ul class="list-disc pl-5 text-gray-700 text-sm">
        @foreach($organizationBreakdown as $org => $amount)
            <li>{{ $org }}: ₱ {{ number_format($amount, 2) }}</li>
        @endforeach
    </ul>
</div>

<div class="mb-4">
    <h3 class="text-sm font-semibold mb-2">Breakdown by Fee</h3>
    <ul class="list-disc pl-5 text-gray-700 text-sm">
        @foreach($feeBreakdown as $feeName => $amount)
            <li>{{ $feeName }}: ₱ {{ number_format($amount, 2) }}</li>
        @endforeach
    </ul>
</div>

<div x-data="{ search: '', rowsVisible: {{ $payments->count() }}, clear() { 
        this.search = ''; 
        $refs.table.querySelectorAll('tbody tr').forEach(tr => tr.style.display = ''); 
        this.rowsVisible = $refs.table.querySelectorAll('tbody tr').length; 
    } 
}" class="mb-4">

    <div class="flex justify-end gap-4 items-center mb-2">
        <div class="relative w-1/3">
            <input type="text" placeholder="Search payments..." x-model="search" @input="
                let count = 0;
                $refs.table.querySelectorAll('tbody tr').forEach(tr => {
                    let text = tr.innerText.toLowerCase();
                    if(text.includes(search.toLowerCase())) {
                        tr.style.display = '';
                        count++;
                    } else {
                        tr.style.display = 'none';
                    }
                });
                rowsVisible = count;
            " class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition">
            <button type="button" x-show="search.length > 0" @click="clear()" class="absolute right-1 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 text-sm font-bold px-1">
                &times;
            </button>
        </div>

        <button @click="openFilter = true" class="bg-gray-300 inline-flex items-center gap-2 px-4 py-2 text-sm font-medium rounded-lg hover:bg-gray-200 transition">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2l-6 7v5l-4 2v-7L3 6V4z" />
            </svg>
            Filters
        </button>
    </div>

    <div x-ref="table" class="overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr class="text-left text-[11px] font-semibold uppercase tracking-wide text-gray-600">
                    <th class="px-4 py-3">#</th>
                    <th class="px-5 py-3">Organization</th>
                    <th class="px-5 py-3">Student</th>
                    <th class="px-5 py-3">Fee Name</th>
                    <th class="px-5 py-3">Requirement</th>
                    <th class="px-5 py-3">Status</th>
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
                        @if($payment->organization)

                        @if($payment->organization->logo)
                        <img src="{{ asset('storage/' . $payment->organization->logo) }}" alt="{{ $payment->organization->name }}" class="w-6 h-6 rounded-full object-cover">
                        @endif

                        <span>{{ $payment->organization->name }}</span>
                        @else
                        @if($college->logo)
                        <img src="{{ asset('storage/' . $college->logo) }}" alt="{{ $college->name }}" class="w-6 h-6 rounded-full object-cover">
                        @endif
                        <span>{{ $college->name }}</span>
                        @endif

                    </td>

                    <td class="px-5 py-3">
                        {{ strtoupper($payment->student->last_name) }},
                        {{ strtoupper($payment->student->first_name) }}
                    </td>
                    <td class="px-5 py-3">{{ $fee->fee_name }}</td>

                    <td class="px-5 py-3">
                        @if($fee->requirement_level === 'mandatory')
                        <span class="inline-flex px-2 py-1 text-xs font-medium rounded-full bg-red-100 text-red-700">
                            Mandatory
                        </span>
                        @else
                        <span class="inline-flex px-2 py-1 text-xs font-medium rounded-full bg-gray-100 text-gray-600">
                            Optional
                        </span>
                        @endif
                    </td>

                    <td class="px-5 py-3">
                        <span class="inline-flex px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-700">
                            Paid
                        </span>
                    </td>
                    <td class="px-5 py-3">{{ number_format($fee->pivot->amount_paid, 2) }}</td>
                    <td class="px-5 py-3">{{ $payment->created_at->format('F d, Y H:i') }}</td>
                </tr>
                @endforeach
                @endforeach
            </tbody>
        </table>
    </div>

    <div x-show="rowsVisible === 0" class="p-8 text-center text-gray-500 text-sm">
        No payment records found for the search.
    </div>
</div>
