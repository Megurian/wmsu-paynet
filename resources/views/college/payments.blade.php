<div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4 mb-6">
    <div class="bg-blue-50 text-blue-700 rounded-lg p-4 shadow-md flex flex-col items-center justify-center"> <span class="text-xs font-semibold uppercase">Total Payments</span> <span class="text-2xl font-bold">{{ $totalPayments }}</span> </div>
    <div class="bg-green-50 text-green-700 rounded-lg p-4 shadow-md flex flex-col items-center justify-center"> <span class="text-xs font-semibold uppercase">Total Amount Collected</span> <span class="text-2xl font-bold">₱ {{ number_format($totalAmount, 2) }}</span> </div>
    <div class="bg-red-50 text-red-700 rounded-lg p-4 shadow-md flex flex-col items-center justify-center"> <span class="text-xs font-semibold uppercase">Mandatory Fees</span> <span class="text-2xl font-bold">₱ {{ number_format($requirementBreakdown['mandatory'] ?? 0, 2) }}</span> </div>
    <div class="bg-yellow-50 text-yellow-700 rounded-lg p-4 shadow-md flex flex-col items-center justify-center"> <span class="text-xs font-semibold uppercase">Optional Fees</span> <span class="text-2xl font-bold">₱ {{ number_format($requirementBreakdown['optional'] ?? 0, 2) }}</span> </div>
</div>
<div x-data="{ search: '', rowsVisible: {{ $payments->count() }}, clear() { this.search = ''; $refs.table.querySelectorAll('tbody tr').forEach(tr => tr.style.display = ''); this.rowsVisible = $refs.table.querySelectorAll('tbody tr').length; } }" class="mb-4">
    <div class="flex flex-col md:flex-row justify-end items-center mb-4 gap-4">
        <div class="relative w-full md:w-1/3"> <input type="text" placeholder="Search payments..." x-model="search" @input=" let count = 0; $refs.table.querySelectorAll('tbody tr').forEach(tr => { let text = tr.innerText.toLowerCase(); if(text.includes(search.toLowerCase())) { tr.style.display = ''; count++; } else { tr.style.display = 'none'; } }); rowsVisible = count; " class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition"> <button type="button" x-show="search.length > 0" @click="clear()" class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 text-sm font-bold">&times;</button> </div> <button @click="openFilter = true" class="bg-gray-100 hover:bg-gray-200 text-gray-700 inline-flex items-center gap-2 px-4 py-2 text-sm font-medium rounded-lg transition"> <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2l-6 7v5l-4 2v-7L3 6V4z" /> </svg> Filters </button>
    </div>
    <div x-ref="table" class="overflow-x-auto rounded-lg shadow-sm">
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
                    <td class="px-5 py-3 flex items-center gap-2"> @if($fee->organization) @if($fee->organization->logo) <img src="{{ asset('storage/' . $fee->organization->logo) }}" alt="{{ $fee->organization->name }}" class="w-6 h-6 rounded-full object-cover"> @endif <span>{{ $fee->organization->name }}</span> @else @if($college->logo) <img src="{{ asset('storage/' . $college->logo) }}" alt="{{ $college->name }}" class="w-6 h-6 rounded-full object-cover"> @endif <span>{{ $college->name }}</span> @endif </td>

                    <td class="px-5 py-3">{{ strtoupper($payment->student->last_name) }}, {{ strtoupper($payment->student->first_name) }}</td>
                    <td class="px-5 py-3">{{ $fee->fee_name }}</td>
                    <td class="px-5 py-3">
                        <span class="inline-flex px-2 py-1 text-xs font-medium rounded-full {{ $fee->requirement_level === 'mandatory' ? 'bg-red-100 text-red-700' : 'bg-gray-100 text-gray-600' }}">
                            {{ ucfirst($fee->requirement_level) }}
                        </span>
                    </td>
                    <td class="px-5 py-3">
                        @if($fee->pivot->amount_paid > 0)
                        <span class="inline-flex px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-700">Paid</span>
                        @else
                        <span class="inline-flex px-2 py-1 text-xs font-medium rounded-full bg-red-100 text-red-700">Unpaid</span>
                        @endif
                    </td>
                    <td class="px-5 py-3">{{ $fee->pivot->amount_paid > 0 ? '₱ ' . number_format($fee->pivot->amount_paid, 2) : '—' }}</td>
                    <td class="px-5 py-3">{{ $payment->created_at ? $payment->created_at->format('F d, Y H:i') : '—' }}</td>
                </tr>
                @endforeach
                @endforeach
            </tbody>
        </table>
    </div>
    <div x-show="rowsVisible === 0" class="p-8 text-center text-gray-500 text-sm"> No payment records found for the search. </div>
</div>
