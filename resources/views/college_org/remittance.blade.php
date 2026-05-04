@extends('layouts.dashboard')

@section('title', 'Remittance')
@section('page-title', 'Remittance')

@section('content')
<div class="bg-white p-6 rounded-xl shadow">

    <h2 class="text-lg font-semibold mb-4">Remittance History</h2>

    <form method="GET" class="mb-4 flex gap-3 items-center">

   <select name="to_filter"
            class="border-gray-300 rounded-md text-sm">

        <option value="">All</option>

        @if($osaOrg)
            <option value="osa" {{ request('to_filter') == 'osa' ? 'selected' : '' }}>
                {{ $osaOrg->name }} 
            </option>
        @endif

        @if($motherOrg)
            <option value="mother" {{ request('to_filter') == 'mother' ? 'selected' : '' }}>
                {{ $motherOrg->name }} 
            </option>
        @endif

    </select>

    <button class="bg-indigo-600 text-white px-4 py-1 rounded-md text-sm">
        Filter
    </button>

    @if(request()->filled('to_filter'))
        <a href="{{ url()->current() }}"
           class="text-sm text-gray-500 underline">
            Reset
        </a>
    @endif

</form>

    <div class="overflow-x-auto">
        <table class="w-full text-sm">

            <thead>
                <tr class="border-b">
                    <th class="text-left py-2">Date</th>
                    <th class="text-left py-2">To</th>
                    <th class="text-left py-2">Amount</th>
                    <th class="text-left py-2">Attachment</th>
                </tr>
            </thead>

            <tbody>
                @forelse($remittances as $remit)
                    <tr class="border-b">

                        <td class="py-2">
                            {{ $remit->created_at->format('M d, Y') }}
                        </td>

                        <td class="py-2">
                            {{ $remit->toOrganization->name ?? 'OSA' }}
                        </td>

                        <td class="py-2">
                            ₱{{ number_format($remit->amount, 2) }}
                        </td>

                        <td class="py-2">
                        @if($remit->proof_image)
                            <button
                                onclick="openProofModal('{{ asset('storage/' . $remit->proof_image) }}')"
                                class="text-blue-600 hover:underline text-sm font-medium">
                                View
                            </button>
                        @else
                            <span class="text-gray-400 text-xs">No proof</span>
                        @endif
                    </td>

                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center py-4 text-gray-500">
                            No remittance records found.
                        </td>
                    </tr>
                @endforelse
            </tbody>

        </table>
    </div>
</div>

<div id="proofModal"
     class="fixed inset-0 hidden items-center justify-center bg-black bg-opacity-60 z-50">

    <div class="bg-white rounded-lg shadow-lg max-w-2xl w-full p-4 relative">

        <button onclick="closeProofModal()"
                class="absolute top-2 right-3 text-gray-600 text-xl font-bold">
            &times;
        </button>

        <img id="proofImage"
             src=""
             class="w-full max-h-[80vh] object-contain rounded" />
    </div>
</div>

<script>
function openProofModal(imageUrl) {
    const modal = document.getElementById('proofModal');
    const img = document.getElementById('proofImage');

    img.src = imageUrl;
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

function closeProofModal() {
    const modal = document.getElementById('proofModal');
    const img = document.getElementById('proofImage');

    modal.classList.add('hidden');
    modal.classList.remove('flex');
    img.src = '';
}
</script>
@endsection