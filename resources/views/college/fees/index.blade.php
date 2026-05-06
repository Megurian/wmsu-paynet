@extends('layouts.dashboard')

@section('title', 'College Fees')
@section('page-title', 'College Fees')

@section('content')
<div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-4 mb-6">
    <div class="space-y-2">
        <h2 class="text-2xl font-bold text-gray-800">College Fees</h2>
        <p class="text-sm text-gray-500">Search and filter fees for your college.</p>
    </div>

    <a href="{{ route('college.fees.create') }}"
       class="bg-red-700 hover:bg-red-800 text-white px-4 py-2 rounded-lg shadow">
        + Add Fee
    </a>
</div>

<div class="mb-6 p-4 bg-white rounded-xl border border-gray-200">
    <form method="GET" action="{{ route('college.fees') }}" class="grid gap-4 md:grid-cols-[2fr_1fr_auto] items-end">
        <div>
            <label for="feeSearchInput" class="block text-sm font-medium text-gray-700">Search</label>
            <input id="feeSearchInput" name="search" type="search" value="{{ request('search') }}" placeholder="Search fees or organizations..."
                class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-red-600 focus:ring-red-600 sm:text-sm">
        </div>

        <div>
            <label for="organizationFilter" class="block text-sm font-medium text-gray-700">Organization</label>
            <select id="organizationFilter" name="organization_id"
                class="mt-1 block w-full rounded-lg border-gray-300 bg-white py-2 px-3 shadow-sm focus:border-red-600 focus:ring-red-600 sm:text-sm">
                <option value="">All Organizations</option>
                @foreach($organizations as $organization)
                    <option value="{{ $organization->id }}" {{ request('organization_id') == $organization->id ? 'selected' : '' }}>
                        {{ $organization->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="flex items-center justify-end space-x-2">
            <button type="submit" class="rounded-lg bg-red-800 px-4 py-2 text-sm text-white hover:bg-red-700">Filter</button>
            <a href="{{ route('college.fees') }}" class="inline-flex h-10 w-10 items-center justify-center rounded-lg border border-gray-300 bg-white text-gray-700 hover:bg-gray-100" aria-label="Clear filters">
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

<div class="bg-white shadow rounded-lg">
    @if($fees->isEmpty())
        <div class="text-center text-gray-500 py-8">
            No fees found. Click the "Add Fee" button to create one.
        </div>
    @else
        <div class="divide-y">
            @foreach($fees as $fee)
                <div class="p-4 hover:bg-gray-50 transition flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                    <!-- Fee Info -->
                    <div class="flex-1">
                        <h3 class="text-lg font-semibold text-gray-800">{{ $fee->fee_name }}</h3>
                        <p class="text-sm text-gray-600 mt-1">Amount: <span class="font-medium">₱{{ number_format($fee->amount, 2) }}</span></p>
                        <p class="text-sm text-gray-600">
                            <span class="capitalize font-medium">{{ $fee->requirement_level }}</span>
                        </p>

                        {{-- SOURCE DISPLAY (inherited fees) --}}
                        <p class="text-sm text-gray-600 mt-2">
                            From:
                            @if($fee->organization)
                                {{-- If it has a mother org (meaning this is an office) --}}
                                @if($fee->organization->motherOrganization)
                                    <span class="font-semibold">
                                        {{ $fee->organization->name }}
                                    </span>
                                    <span class="text-gray-400">
                                        (Office under {{ $fee->organization->motherOrganization->name }})
                                    </span>
                                @else
                                    <span class="font-semibold">
                                        {{ $fee->organization->name }}
                                    </span>
                                @endif
                            @else
                                <span class="font-semibold text-blue-700">
                                    College Fee (Student Coordinator)
                                </span>
                            @endif
                        </p>

                        <p class="text-sm text-gray-400 mt-1">Created: {{ $fee->created_at->format('M d, Y') }}</p>
                    </div>

                    <!-- Status Badge -->
                    <div class="flex items-center gap-2">
                        @if($fee->status === 'approved')
                            <span class="inline-block bg-green-100 text-green-800 text-xs font-semibold px-3 py-1 rounded-full">
                                Approved
                            </span>
                        @elseif($fee->status === 'pending')
                            <span class="inline-block bg-yellow-100 text-yellow-800 text-xs font-semibold px-3 py-1 rounded-full">
                                Pending
                            </span>
                        @elseif($fee->status === 'rejected')
                            <span class="inline-block bg-red-100 text-red-800 text-xs font-semibold px-3 py-1 rounded-full">
                                Rejected
                            </span>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>

<script>
    (function () {
        const filterForm = document.getElementById('organizationFilter')?.closest('form');
        const searchInput = document.getElementById('feeSearchInput');
        const orgSelect = document.getElementById('organizationFilter');

        if (!filterForm) {
            return;
        }

        let debounceTimer;

        if (searchInput) {
            searchInput.addEventListener('input', function () {
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(function () {
                    filterForm.submit();
                }, 1200);
            });
        }

        if (orgSelect) {
            orgSelect.addEventListener('change', function () {
                filterForm.submit();
            });
        }
    })();
</script>
@endsection