@extends('layouts.dashboard')

@section('title', 'OSA Fees')
@section('page-title', 'OSA Fees')

@section('content')
<div class="mb-8">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-3xl font-bold text-gray-800">OSA Fee Approval</h2>
            <p class="text-sm text-gray-500 mt-1">Welcome, {{ Auth::user()->name }}. Here you can manage the fees associated with different colleges within the university.</p>
        </div>
        <div>
            <a href="{{ route('osa.fees.create') }}" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">Create Fee</a>
        </div>
    </div>
    @if(session('success'))
        <div class="mb-4 p-4 bg-green-100 text-green-800 rounded">
            {{ session('success') }}
        </div>
    @endif
</div>

<!-- Fees Section -->
<div class="bg-gray rounded shadow p-6">
        <div class="mb-6 flex space-x-2">
            <a href="{{ route('osa.fees', array_merge(request()->except('page'), ['status' => 'pending'])) }}"
            class="px-4 py-2 rounded-full font-medium text-sm transition
            {{ $status === 'pending' ? 'bg-red-800 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }}">
                Pending Fees
                <span class="ml-2 inline-block bg-white text-red-800 font-semibold text-xs px-2 py-0.5 rounded-full">
                    {{ $pendingFees->count() }}
                </span>
            </a>
            <a href="{{ route('osa.fees', array_merge(request()->except('page'), ['status' => 'disabled'])) }}"
                class="px-4 py-2 rounded-full font-medium text-sm transition
                {{ $status === 'disabled' ? 'bg-red-800 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }}">
                Disabled Fees
            </a>
            <a href="{{ route('osa.fees', array_merge(request()->except('page'), ['status' => 'approved'])) }}"
            class="px-4 py-2 rounded-full font-medium text-sm transition
            {{ $status === 'approved' ? 'bg-red-800 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }}">
                Approved Fees
            </a>
        </div>
        <div class="mb-6 p-4 bg-white rounded-xl border border-gray-200">
            <form id="osaFeesFilterForm" method="GET" action="{{ route('osa.fees') }}" class="grid gap-4 md:grid-cols-[2fr_1fr_1fr_auto] items-end">
                <input type="hidden" name="status" value="{{ $status }}">
                <div>
                    <label for="feesSearchInput" class="block text-sm font-medium text-gray-700">Search</label>
                    <input id="feesSearchInput" name="search" type="search" value="{{ request('search') }}" placeholder="Search fees, orgs, colleges..." class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">College</label>
                    <select name="college_id" class="mt-1 block w-full rounded-lg border-gray-300 bg-white py-2 px-3 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        <option value="">All Colleges</option>
                        @foreach($colleges as $college)
                            <option value="{{ $college->id }}" {{ request('college_id') == $college->id ? 'selected' : '' }}>{{ $college->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Organization</label>
                    <select name="organization_id" class="mt-1 block w-full rounded-lg border-gray-300 bg-white py-2 px-3 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        <option value="">All Organizations</option>
                        @foreach($organizations as $organization)
                            <option value="{{ $organization->id }}" {{ request('organization_id') == $organization->id ? 'selected' : '' }}>{{ $organization->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex items-center justify-end space-x-2">
                    <button type="submit" class="rounded-lg bg-red-800 px-4 py-2 text-sm text-white hover:bg-red-700">Filter</button>
                    <a href="{{ route('osa.fees', ['status' => $status]) }}" class="inline-flex h-10 w-10 items-center justify-center rounded-lg border border-gray-300 bg-white text-gray-700 hover:bg-gray-100" aria-label="Clear filters">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-5 w-5">
                            <path d="M23 4v6h-6" />
                            <path d="M1 20v-6h6" />
                            <path d="M3.51 9a9 9 0 0 1 14.13-3.36L23 10" />
                            <path d="M20.49 15a9 9 0 0 1-14.13 3.36L1 14" />
                        </svg>
                    </a>
                </div>
            </form>
        </div>
   

        @if($status === 'pending')
         <h3 class="text-xl font-semibold mb-4">Pending Fees</h3>
         <p class="text-gray-500 italic">Pending fee approval requests for every organization will appear here.</p> <br>

            <!-- DISABLE REQUESTS -->
        @foreach($pendingDisableRequests as $request)
            <div class="bg-white shadow rounded-lg p-4 mb-4 border-l-4 border-red-500">

                <h3 class="text-lg font-semibold text-gray-800">
                    Disable Request: {{ $request->fee->fee_name }}
                </h3>

                <p class="text-sm text-gray-500 mt-1">
                    Requested by: {{ $request->requestedBy->name }}
                </p>

                <p class="text-sm text-gray-600 mt-2">
                    Reason: {{ $request->reason }}
                </p>

                <button
                    onclick="openDisableReviewModal({{ $request->id }}, `{{ $request->reason }}`)"
                    class="mt-3 px-3 py-1 bg-blue-600 text-white rounded"
                >
                    Review
                </button>

            </div>
        @endforeach


        <!-- ENABLE REQUESTS -->
        @foreach($pendingEnableRequests as $request)
            <div class="bg-white shadow rounded-lg p-4 mb-4 border-l-4 border-green-500">

                <h3 class="text-lg font-semibold text-gray-800">
                    Enable Request: {{ $request->fee->fee_name }}
                </h3>

                <p class="text-sm text-gray-500 mt-1">
                    Requested by: {{ $request->requestedBy->name }}
                </p>

                <p class="text-sm text-gray-600 mt-2">
                    Reason: {{ $request->reason }}
                </p>

                <button
                    onclick="openDisableReviewModal({{ $request->id }}, `{{ $request->reason }}`)"
                    class="mt-3 px-3 py-1 bg-green-600 text-white rounded"
                >
                    Review
                </button>

            </div>
        @endforeach

@forelse($filteredFees as $fee)

        <div class="bg-white shadow rounded-lg p-4 flex flex-col md:flex-row md:items-center md:justify-between gap-4">

            <!-- Fee Info -->
            <div class="flex-1">
                <h3 class="text-lg font-semibold text-gray-800">
                    {{ $fee->fee_name }}

                    @if($fee->appeals->where('status','pending')->count() > 0)
                        <span class="ml-2 inline-block px-2 py-0.5 text-xs font-semibold bg-yellow-100 text-yellow-800 rounded">
                            Appeal Pending
                        </span>
                    @endif
                </h3>

                <p class="text-sm text-gray-500">
                    <span class="capitalize font-medium">{{ $fee->requirement_level }}</span>
                </p>

                <p class="text-sm text-gray-600 mt-1">
                    From:
                    <span class="font-semibold">
                        {{ optional($fee->organization)->name ?? optional($fee->college)->name }}
                        @if(optional($fee->organization)->org_code)
                            ({{ $fee->organization->org_code }})
                        @endif
                    </span>
                </p>

                <p class="text-sm text-gray-400 mt-1">
                    Submitted on: {{ $fee->created_at->format('M d, Y') }}
                </p>

                

            </div>

            <form method="GET" action="{{ route('osa.fees.show', $fee->id) }}">
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-green-700 transition">
                    View Details
                </button>
            </form>
        </div>

        <br>
        @empty
            <div class="text-center text-gray-500 py-6">
                No pending fees to review.
            </div>
        @endforelse

        <div class="mt-6">
            {{ $filteredFees->withQueryString()->links() }}
        </div>

    @elseif ($status === 'disabled')
    <div class="mt-8">
        <h3 class="text-xl font-semibold mb-4">Disabled Fees</h3>

        @forelse($filteredFees as $fee)
            <div class="bg-white shadow rounded-lg p-4 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div class="flex-1">
                    <h3 class="text-lg font-semibold text-gray-800">
                        {{ $fee->fee_name }}
                        <span class="ml-2 px-2 py-1 text-xs font-bold bg-red-100 text-red-800 rounded">
                            DISABLED
                        </span>
                    </h3>

                    <p class="text-sm text-gray-500">
                        <span class="capitalize font-medium">{{ $fee->requirement_level }}</span>
                    </p>

                    <p class="text-sm text-gray-600 mt-1">
                        From:
                        <span class="font-semibold">
                            {{ optional($fee->organization)->name ?? optional($fee->college)->name }}
                            @if(optional($fee->organization)->org_code)
                                ({{ $fee->organization->org_code }})
                            @endif
                        </span>
                    </p>
                    @php $disableRequest = $fee->feeRequests->first(); @endphp
                    <p class="text-sm text-gray-400 mt-1">
                      {{ optional($disableRequest)->disable_approved_at
                        ? \Carbon\Carbon::parse($disableRequest->disable_approved_at)->format('M d, Y')
                        : '-' }}
                    </p>
                </div>

                <div class="flex gap-2 md:gap-3">
                    <form method="GET" action="{{ route('osa.fees.show', $fee->id) }}">
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                            View
                        </button>
                    </form>
                </div>
            </div>
            <br>
        @empty
            <div class="text-center text-gray-500 py-6">
                No disabled fees.
            </div>
        @endforelse

        <div class="mt-6">
            {{ $filteredFees->withQueryString()->links() }}
        </div>
    </div>

    @elseif ($status === 'approved')
    <!-- Approved Fees Section -->
    <div class="mt-8">
        <h3 class="text-xl font-semibold mb-4">Approved Fees</h3>
            @forelse($filteredFees as $fee)
            <div class="bg-white shadow rounded-lg p-4 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div class="flex-1">
                    <h3 class="text-lg font-semibold text-gray-800">{{ $fee->fee_name }}
                        @if($fee->status === 'disabled')
                            <span class="ml-2 px-2 py-1 text-xs font-bold bg-red-100 text-red-800 rounded">
                                DISABLED
                            </span>
                        @endif
                    </h3>
                    <p class="text-sm text-gray-500"> 
                        <span class="capitalize font-medium">{{ $fee->requirement_level }}</span>
                    </p>
                    <p class="text-sm text-gray-600 mt-1">
                        From:
                        <span class="font-semibold">
                            {{ optional($fee->organization)->name ?? optional($fee->college)->name }}
                            @if(optional($fee->organization)->org_code)
                                ({{ $fee->organization->org_code }})
                            @endif
                        </span>
                    </p>
                    <p class="text-sm text-gray-400 mt-1">
                        Approved on: {{ optional(\Illuminate\Support\Carbon::parse($fee->approved_at))->format('M d, Y') }}
                    </p>
                </div>
                <div class="flex gap-2 md:gap-3">
                    <form method="GET" action="{{ route('osa.fees.show', $fee->id) }}">
                        @csrf
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-green-700 transition">
                            View
                        </button>
                    </form>
                </div>
            </div>
            <br>
            @empty
                <div class="text-center text-gray-500 py-6">
                    No approved fees yet.
                </div>
            @endforelse

                <div class="mt-6">
                    {{ $filteredFees->withQueryString()->links() }}
                </div>
    @endif
</div>

<script>
    (function () {
        const searchInput = document.getElementById('feesSearchInput');
        const filterForm = document.getElementById('osaFeesFilterForm');

        if (!searchInput || !filterForm) {
            return;
        }

        let debounceTimer;
        searchInput.addEventListener('input', function () {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(function () {
                filterForm.submit();
            }, 1200);
        });

        filterForm.querySelectorAll('select[name="college_id"], select[name="organization_id"]').forEach(function (select) {
            select.addEventListener('change', function () {
                filterForm.submit();
            });
        });
    })();
</script>

<div id="disableReviewModal" class="hidden fixed inset-0 z-50 flex items-center justify-center px-4">
    <div class="fixed inset-0 bg-black/50"></div>

    <div class="bg-white w-full max-w-lg rounded-lg shadow-lg p-6 relative z-10">
        <h3 class="text-lg font-semibold mb-3">Disable Request Review</h3>

        <div class="mb-4">
            <p class="text-sm font-medium text-gray-700">Dean’s Reason:</p>
            <p id="disableReasonText" class="text-gray-600 bg-gray-100 p-3 rounded mt-1"></p>
        </div>

        <form id="disableReviewForm" method="POST">
            @csrf

            <!-- OSA Notes -->
            <label class="block text-sm font-medium text-gray-700 mb-1">
                OSA Notes / Response
            </label>
            <textarea name="note" class="w-full border rounded p-2 mb-4" required></textarea>

            <!-- Password Confirmation -->
            <label class="block text-sm font-medium text-gray-700 mb-1">
                Confirm Password
            </label>
            <input 
                type="password" 
                name="password" 
                class="w-full border rounded p-2 mb-4" 
                placeholder="Enter your password"
                required
            >

            <div class="flex justify-end gap-2">
                <button type="button" onclick="closeDisableReviewModal()" class="px-4 py-2 border rounded">
                    Cancel
                </button>

                <button type="button" onclick="setApproveAction()" class="px-4 py-2 bg-green-600 text-white rounded">
                    Approve
                </button>

                <button type="button" onclick="setRejectAction()" class="px-4 py-2 bg-red-600 text-white rounded">
                    Reject
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function toggleMenu(menuId) {
    document.querySelectorAll('[id^="menu-"]').forEach(menu => {
        if (menu.id !== menuId) {
            menu.classList.add('hidden');
        }
    });
    
    const menu = document.getElementById(menuId);
    if (menu) {
        menu.classList.toggle('hidden');
    }
}

document.addEventListener('click', function(event) {
    if (!event.target.matches('button') && !event.target.closest('.relative')) {
        document.querySelectorAll('[id^="menu-"]').forEach(menu => {
            menu.classList.add('hidden');
        });
    }
});

function openDisableReviewModal(feeId, reason) {
    document.getElementById('disableReviewModal').classList.remove('hidden');
    document.getElementById('disableReasonText').innerText = reason;

    const form = document.getElementById('disableReviewForm');
    form.dataset.feeRequestId = feeId; 
}

function closeDisableReviewModal() {
    document.getElementById('disableReviewModal').classList.add('hidden');
}

function setApproveAction() {
    const form = document.getElementById('disableReviewForm');
    const requestId = form.dataset.feeRequestId;

    form.action = `/osa/fee-requests/${requestId}/approve`;
    form.submit();
}

function setRejectAction() {
    const form = document.getElementById('disableReviewForm');
    const requestId = form.dataset.feeRequestId;

    form.action = `/osa/fee-requests/${requestId}/reject`;
    form.submit();
}
</script>

<style>
[id^="menu-"] {
    transition: opacity 0.2s ease-in-out;
}
</style>
@endsection