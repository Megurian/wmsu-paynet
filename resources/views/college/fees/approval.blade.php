@extends('layouts.dashboard')

@section('title', 'College Fees')
@section('page-title', 'College Fees')

@section('content')
@php
    $tab = request('tab', 'pending');
@endphp

<div class="mb-6 flex space-x-2">
    <a href="{{ route('college.fees.approval', ['tab' => 'pending']) }}"
       class="px-4 py-2 rounded-full font-medium text-sm transition
       {{ $tab === 'pending' ? 'bg-red-800 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }}">
        Pending Fees
        <span class="ml-2 inline-block bg-white text-red-800 font-semibold text-xs px-2 py-0.5 rounded-full">
            {{ $pendingFees->count() }}
        </span>
    </a>
    <a href="{{ route('college.fees.approval', ['tab' => 'disabled']) }}"
    class="px-4 py-2 rounded-full font-medium text-sm transition
    {{ $tab === 'disabled' ? 'bg-red-800 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }}">
        Disabled Fees
    </a>
    <a href="{{ route('college.fees.approval', ['tab' => 'approved']) }}"
       class="px-4 py-2 rounded-full font-medium text-sm transition
       {{ $tab === 'approved' ? 'bg-red-800 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }}">
        Approved Fees
    </a>
</div>

<div class="space-y-6">
    @if($tab === 'pending')
        @forelse($pendingFees as $fee)
            <div class="bg-white shadow rounded-lg p-4 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <!-- Fee Info -->
                <div class="flex-1">
                    <h3 class="text-lg font-semibold text-gray-800">{{ $fee->fee_name }}</h3>
                    <p class="text-sm text-gray-500 mt-1">Amount: <span class="font-medium">₱{{ number_format($fee->amount, 2) }}</span></p>
                    <p class="text-sm text-gray-500">
                        <span class="capitalize font-medium">{{ $fee->requirement_level }}</span>
                    </p>

                    {{-- SOURCE DISPLAY --}}
                    <p class="text-sm text-gray-600 mt-1">
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
                                </span>a
                            @endif

                        @else
                            <span class="font-semibold text-blue-700">
                                College Fee (Student Coordinator)
                            </span>
                        @endif
                    </p>
                    <p class="text-sm text-gray-400 mt-1">Submitted on: {{ $fee->created_at->format('M d, Y') }}</p>
                </div>

                <div class="flex gap-2 md:gap-3">
                    <a href="{{ route('college.fees.show', $fee->id) }}" class="inline-flex items-center px-3 py-1 bg-blue-600 text-white rounded hover:bg-blue-700">View</a>
                    <button type="button" data-approve-url="{{ route('college.fees.approve', $fee) }}" class="approve-fee-btn inline-flex items-center px-3 py-1 bg-green-600 text-white rounded hover:bg-green-700">Approve</button>
                </div>

            </div>
        @empty
            <div class="text-center text-gray-500 py-6">
                No pending fees to review.
            </div>
        @endforelse

    @elseif($tab === 'disabled')
    @forelse($disabledFees as $fee)
        <div class="bg-white shadow rounded-lg p-4 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            
            <div class="flex-1">
                <h3 class="text-lg font-semibold text-gray-800">
                    {{ $fee->fee_name }}
                    <span class="ml-2 px-2 py-1 text-xs font-bold bg-red-100 text-red-700 rounded">
                        DISABLED
                    </span>
                </h3>

                <p class="text-sm text-gray-500 mt-1">
                    Amount: <span class="font-medium">₱{{ number_format($fee->amount, 2) }}</span>
                </p>

                <p class="text-sm text-gray-400 mt-1">
                    Disabled on:
                    {{ $fee->disable_approved_at 
                        ? \Carbon\Carbon::parse($fee->disable_approved_at)->format('M d, Y')
                        : '-' 
                    }}
                </p>
            </div>

            <a href="{{ route('college.fees.show', $fee->id) }}"
               class="px-3 py-1 bg-blue-600 text-white rounded hover:bg-blue-700">
                View
            </a>
        </div>
        @empty
            <div class="text-center text-gray-500 py-6">
                No disabled fees.
            </div>
        @endforelse

    @elseif($tab === 'approved')
        @forelse($allFees as $fee)
            <div class="bg-white shadow rounded-lg p-4 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div class="flex-1">
                    <h3 class="text-lg font-semibold text-gray-800">{{ $fee->fee_name }}</h3>
                    <p class="text-sm text-gray-500 mt-1">Amount: <span class="font-medium">₱{{ number_format($fee->amount, 2) }}</span></p>
                    <p class="text-sm text-gray-500"> <span class="capitalize font-medium">{{ $fee->requirement_level }}</span></p>
                    <p class="text-sm text-gray-600 mt-1">
                        @if($fee->organization)
                            @if($fee->organization && $fee->organization->motherOrganization)
                                <span class="font-semibold">{{ $fee->organization->name }}
                                    @if($fee->status === 'disabled')
                                        <span class="inline-block mt-1 px-2 py-1 text-xs font-bold bg-red-100 text-red-700 rounded">
                                            DISABLED
                                        </span>
                                    @endif
                                </span>
                                <span class="text-gray-400">(Office under {{ $fee->organization->motherOrganization->name }})</span>
                            @elseif($fee->organization)
                                <span class="font-semibold">{{ $fee->organization->name }}</span>
                            @else
                                <span class="font-semibold text-blue-700">College Fee (Student Coordinator)</span>
                            @endif

                        @else
                            <span class="font-semibold text-blue-700">
                                College Fee (Student Coordinator)
                            </span>
                        @endif
                    </p>
                    <p class="text-sm text-gray-400 mt-1">
                        Approved on: {{ optional(\Illuminate\Support\Carbon::parse($fee->approved_at))->format('M d, Y') }}
                    </p>

                    @if($fee->disable_status === 'pending')
                        <p class="text-xs text-yellow-600 mt-1">
                            Disable request pending OSA approval
                        </p>
                    @elseif($fee->disable_status === 'approved')
                        <p class="text-xs text-red-600 mt-1">
                            Fee disabled
                        </p>
                    @endif
                    @if($fee->disable_status === 'rejected')
                        <p class="text-xs text-red-600 mt-1">
                            Disable request was rejected. You may submit again.
                        </p>
                    @endif

                    @if($fee->status === 'approved' && ($fee->disable_status === null || $fee->disable_status === 'rejected'))
                    <button
                        type="button"
                        onclick="openDisableModal({{ $fee->id }})"
                        class="px-3 py-1 bg-red-600 text-white rounded hover:bg-red-700"
                    >
                        Disable
                    </button>
                @endif
                </div>
            </div>
        @empty
            <div class="text-center text-gray-500 py-6">
                No approved fees yet.
            </div>
        @endforelse
    @endif
</div>

<!-- Approval Password Confirmation Modal -->
<div id="feeApproveModal" class="hidden fixed inset-0 z-50 flex items-center justify-center px-4">
    <div class="fixed inset-0 bg-black bg-opacity-50"></div>
    <div class="relative bg-white rounded-lg shadow-xl max-w-md w-full z-10 p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-3">Confirm Approval</h3>
        <p class="text-sm text-gray-500 mb-5">Enter your password to confirm this fee approval.</p>

        <form id="feeApproveForm" method="POST" action="">
            @csrf
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1" for="approval_password">Password</label>
                <input id="approval_password" type="password" name="password" required class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-red-600" autocomplete="current-password" />
                @error('password') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="flex justify-end gap-2">
                <button type="button" onclick="closeFeeApproveModal()" class="px-4 py-2 border rounded text-gray-700 hover:bg-gray-100">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">Confirm Approve</button>
            </div>
        </form>
    </div>
</div>

<div id="disableFeeModal" class="hidden fixed inset-0 z-50 flex items-center justify-center px-4">
    <div class="fixed inset-0 bg-black/50"></div>

    <div class="bg-white w-full max-w-md rounded-lg shadow-lg p-6 relative z-10">
        <h3 class="text-lg font-semibold mb-3">Request Fee Disable</h3>

        <form id="disableFeeForm" method="POST" action="">
            @csrf

            <textarea
                name="reason"
                required
                placeholder="Enter reason for disabling this fee..."
                class="w-full border rounded p-2 mb-4"
            ></textarea>

            <div class="flex justify-end gap-2">
                <button type="button" onclick="closeDisableModal()" class="px-4 py-2 border rounded">
                    Cancel
                </button>

                <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded">
                    Send Request
                </button>
            </div>
        </form>
    </div>
</div>

<div id="enableModal" class="hidden fixed inset-0 flex items-center justify-center">
    <div class="bg-white p-6 rounded w-96">
        <form id="enableForm" method="POST">
            @csrf
            <textarea name="reason" class="w-full border p-2" placeholder="Reason"></textarea>

            <button class="mt-3 bg-green-600 text-white px-4 py-2 rounded">
                Send Request
            </button>
        </form>
    </div>
</div>

<script>

function openFeeApproveModal(actionUrl) {
    const modal = document.getElementById('feeApproveModal');
    const form = document.getElementById('feeApproveForm');
    form.action = actionUrl;
    modal.classList.remove('hidden');
}

function closeFeeApproveModal() {
    document.getElementById('feeApproveModal').classList.add('hidden');
}

document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.approve-fee-btn').forEach(function(button) {
        button.addEventListener('click', function() {
            openFeeApproveModal(this.dataset.approveUrl);
        });
    });
});

function openDisableModal(feeId) {
    const modal = document.getElementById('disableFeeModal');
    const form = document.getElementById('disableFeeForm');

    form.action = `/college/fees/${feeId}/request-disable`;
    modal.classList.remove('hidden');
}

function closeDisableModal() {
    document.getElementById('disableFeeModal').classList.add('hidden');
}
function openEnableModal(id) {
    document.getElementById('enableForm').action = `/college/fees/${id}/request-enable`;
    document.getElementById('enableModal').classList.remove('hidden');
}
</script>
@endsection