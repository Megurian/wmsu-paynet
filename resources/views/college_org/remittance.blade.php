@extends('layouts.dashboard')

@section('title', 'Remittance')
@section('page-title', 'Remittance History')

@section('content')

<div class="space-y-6">

    {{-- HEADER --}}
    <div class="bg-white rounded-xl shadow p-6">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">

            <div>
                <h2 class="text-xl font-semibold text-gray-800">Remittance History</h2>
                <p class="text-sm text-gray-500">
                    View all fund transfers made to OSA or Mother Organization.
                </p>
            </div>

            {{-- FILTER --}}
            <form method="GET" class="flex flex-col sm:flex-row gap-3 sm:items-end">

                <div>
                    <label class="text-xs text-gray-500">Filter by Destination</label>
                    <select name="to_filter"
                        class="w-full sm:w-56 border-gray-300 rounded-lg text-sm focus:ring-indigo-500 focus:border-indigo-500">

                        <option value="">All Destinations</option>

                        @if($osaOrg)
                            <option value="osa" {{ request('to_filter') == 'osa' ? 'selected' : '' }}>
                                {{ $osaOrg->name }} (OSA)
                            </option>
                        @endif

                        @if($motherOrg)
                            <option value="mother" {{ request('to_filter') == 'mother' ? 'selected' : '' }}>
                                {{ $motherOrg->name }} (Mother Org)
                            </option>
                        @endif

                    </select>
                </div>

                <div class="flex gap-2">
                    <button class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg text-sm transition">
                        Apply
                    </button>

                    @if(request()->filled('to_filter'))
                        <a href="{{ url()->current() }}"
                           class="px-4 py-2 text-sm text-gray-500 hover:text-gray-700 underline">
                            Reset
                        </a>
                    @endif
                </div>

            </form>
        </div>
    </div>

    {{-- TABLE CARD --}}
    <div class="bg-white rounded-xl shadow overflow-hidden">

        <div class="p-4 border-b">
            <h3 class="text-sm font-semibold text-gray-700">Transactions</h3>
        </div>

        <div class="overflow-x-auto">

            <table class="w-full text-sm">

                <thead class="bg-gray-50 text-gray-600 uppercase text-xs">
                    <tr>
                        <th class="text-left px-6 py-3">Date</th>
                        <th class="text-left px-6 py-3">To</th>
                        <th class="text-left px-6 py-3">Amount</th>
                        <th class="text-left px-6 py-3">Attachment</th>
                    </tr>
                </thead>

                <tbody class="divide-y">

                    @forelse($remittances as $remit)

                        <tr class="hover:bg-gray-50 transition">

                            <td class="px-6 py-4 text-gray-600">
                                {{ $remit->created_at->format('M d, Y') }}
                            </td>

                            <td class="px-6 py-4">
                                <div class="flex flex-col">
                                    <span class="font-medium text-gray-800">
                                        {{ $remit->toOrganization->name ?? 'OSA' }}
                                    </span>

                                    <span class="text-xs text-gray-400">
                                        {{ $remit->type ?? 'Destination' }}
                                    </span>
                                </div>
                            </td>

                            <td class="px-6 py-4 font-semibold text-gray-800">
                                ₱ {{ number_format($remit->amount, 2) }}
                            </td>

                            <td class="px-6 py-4">
                                @if($remit->proof_image)
                                    <button
                                        onclick="openProofModal('{{ asset('storage/' . $remit->proof_image) }}')"
                                        class="text-indigo-600 hover:text-indigo-800 font-medium text-sm">
                                        View 
                                    </button>
                                @else
                                    <span class="text-gray-400 text-xs">No attachment</span>
                                @endif
                            </td>

                        </tr>

                    @empty

                        <tr>
                            <td colspan="4" class="text-center py-10 text-gray-500">
                                No remittance records found.
                            </td>
                        </tr>

                    @endforelse

                </tbody>

            </table>
        </div>
    </div>

</div>

{{-- MODAL --}}
<div id="proofModal"
     class="fixed inset-0 hidden items-center justify-center bg-black/60 z-50 p-4">

    <div class="bg-white rounded-xl shadow-lg max-w-3xl w-full p-4 relative">

        <button onclick="closeProofModal()"
                class="absolute top-3 right-3 text-gray-500 hover:text-gray-700 text-2xl">
            &times;
        </button>

        <img id="proofImage"
             src=""
             class="w-full max-h-[80vh] object-contain rounded-lg" />

    </div>
</div>

<script>
function openProofModal(imageUrl) {
    document.getElementById('proofImage').src = imageUrl;
    document.getElementById('proofModal').classList.remove('hidden');
    document.getElementById('proofModal').classList.add('flex');
}

function closeProofModal() {
    document.getElementById('proofModal').classList.add('hidden');
    document.getElementById('proofModal').classList.remove('flex');
    document.getElementById('proofImage').src = '';
}
</script>

@endsection