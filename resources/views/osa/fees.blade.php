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
<div class="bg-white rounded shadow p-6">
    <h3 class="text-xl font-semibold mb-4">Fee Approval Request</h3>
    <p class="text-gray-500 italic">Pending fee approval requests for every organization will appear here.</p>

    <table class="w-full text-left border-collapse mt-4">
        <thead>
            <tr class="bg-gray-100">
                <th class="border px-4 py-2">Organization</th>
                <th class="border px-4 py-2">Fee</th>
                <th class="border px-4 py-2">Amount</th>
                <th class="border px-4 py-2">Requirement</th>
                <th class="border px-4 py-2">Submitted At</th>
                <th class="border px-4 py-2 text-center">Action</th>
            </tr>
        </thead>
        <tbody>
            @forelse($pendingFees as $fee)
                <tr>
                    <td class="border px-4 py-2">{{ $fee->organization->name }} ({{ $fee->organization->org_code }})</td>
                    <td class="border px-4 py-2">
                        {{ $fee->fee_name }}
                        @if($fee->appeals->where('status','pending')->count() > 0)
                            <span class="ml-2 inline-block px-2 py-0.5 text-xs font-semibold bg-yellow-100 text-yellow-800 rounded">Appeal Pending</span>
                        @endif
                    </td>
                    <td class="border px-4 py-2">₱{{ number_format($fee->amount, 2) }}</td>
                    <td class="border px-4 py-2 capitalize">{{ $fee->requirement_level }}</td>
                    <td class="border px-4 py-2">{{ $fee->created_at->format('Y-m-d') }}</td>
                    <td class="border px-4 py-2 text-center">
                        <a href="{{ route('osa.fees.show', $fee->id) }}" class="inline-flex items-center px-3 py-1 bg-blue-600 text-white rounded hover:bg-blue-700">View</a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td class="border px-4 py-2" colspan="6">No pending fee approval requests.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Approved Fees Section -->
    <div class="mt-8">
        <h3 class="text-xl font-semibold mb-4">Approved Fees</h3>

        <form method="GET" action="{{ route('osa.fees') }}" class="mb-4 flex gap-3 items-end">
            <div>
                <label class="text-sm text-gray-600">Organization</label>
                <select name="organization_id" class="border rounded px-2 py-1">
                    <option value="">All</option>
                    @foreach($organizations as $org)
                        <option value="{{ $org->id }}" {{ request('organization_id') == $org->id ? 'selected' : '' }}>{{ $org->name }} ({{ $org->org_code }})</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="text-sm text-gray-600">Organization Type</label>
                <select name="organization_role" class="border rounded px-2 py-1">
                    <option value="">All</option>
                    <option value="university_org" {{ request('organization_role') == 'university_org' ? 'selected' : '' }}>University</option>
                    <option value="college_org" {{ request('organization_role') == 'college_org' ? 'selected' : '' }}>College</option>
                </select>
            </div>

            <div>
                <label class="text-sm text-gray-600">Requirement</label>
                <select name="requirement_level" class="border rounded px-2 py-1">
                    <option value="">All</option>
                    <option value="mandatory" {{ request('requirement_level') == 'mandatory' ? 'selected' : '' }}>Mandatory</option>
                    <option value="optional" {{ request('requirement_level') == 'optional' ? 'selected' : '' }}>Optional</option>
                </select>
            </div>

            <div>
                <label class="text-sm text-gray-600">Status</label>
                <select name="status" class="border rounded px-2 py-1">
                    <option value="approved" {{ (isset($status) ? $status : request('status', 'approved')) == 'approved' ? 'selected' : '' }}>Approved</option>
                    <option value="pending" {{ (isset($status) ? $status : request('status', 'approved')) == 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="disabled" {{ (isset($status) ? $status : request('status', 'approved')) == 'disabled' ? 'selected' : '' }}>Disabled</option>
                    <option value="all" {{ (isset($status) ? $status : request('status', 'approved')) == 'all' ? 'selected' : '' }}>All</option>
                </select>
            </div>

            <div>
                <button class="px-3 py-1 bg-gray-200 rounded">Filter</button>
            </div>
        </form>

        <table class="w-full text-left border-collapse mt-2">
            <thead>
                <tr class="bg-gray-100">
                    <th class="border px-4 py-2">Organization</th>
                    <th class="border px-4 py-2">Fee</th>
                    <th class="border px-4 py-2">Amount</th>
                    <th class="border px-4 py-2">Requirement</th>
                    <th class="border px-4 py-2">Status / Updated</th>
                    <th class="border px-4 py-2 text-center">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($filteredFees as $fee)
                    <tr>
                        <td class="border px-4 py-2">{{ $fee->organization->name }} ({{ $fee->organization->org_code }})</td>
                        <td class="border px-4 py-2">{{ $fee->fee_name }}</td>
                        <td class="border px-4 py-2">₱{{ number_format($fee->amount, 2) }}</td>
                        <td class="border px-4 py-2 capitalize">{{ $fee->requirement_level }}</td>
                        <td class="border px-4 py-2">{{ ucfirst($fee->status) }} @ {{ $fee->updated_at->format('Y-m-d') }}</td>
                        <td class="border px-4 py-2 text-center">
                            <a href="{{ route('osa.fees.show', $fee->id) }}" class="inline-flex items-center px-3 py-1 bg-blue-600 text-white rounded hover:bg-blue-700">View</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td class="border px-4 py-2" colspan="6">No fees found for selected filters.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
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
</script>

<style>
[id^="menu-"] {
    transition: opacity 0.2s ease-in-out;
}
</style>
@endsection