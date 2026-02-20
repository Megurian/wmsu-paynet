@extends('layouts.dashboard')

@section('title', 'Fee Details')
@section('page-title', 'Fee Details')

@section('content')
<div class="mb-6">
    <a href="{{ route('college.fees.approval') }}" class="text-sm text-gray-600 hover:underline">&larr; Back to Approvals</a>
</div>

<div class="bg-white rounded shadow p-6 max-w-3xl">
    <h2 class="text-2xl font-bold mb-4">{{ $fee->fee_name }}</h2>

    <div class="grid grid-cols-1 gap-4">
        <div>
            <h3 class="font-medium">Organization</h3>
            <p class="text-gray-700">{{ optional($fee->organization)->name ?? 'College (student coordinator)' }} @if($fee->organization) ({{ $fee->organization->org_code }}) @endif</p>
        </div>

        <div>
            <h3 class="font-medium">Purpose</h3>
            <p class="text-gray-700">{{ $fee->purpose }}</p>
        </div>

        <div>
            <h3 class="font-medium">Description</h3>
            <p class="text-gray-700">{{ $fee->description }}</p>
        </div>

        <div class="flex gap-6">
            <div>
                <h3 class="font-medium">Amount</h3>
                <p class="text-gray-700">₱{{ number_format($fee->amount, 2) }}</p>
            </div>
            <div>
                <h3 class="font-medium">Remittance</h3>
                <p class="text-gray-700">{{ $fee->remittance_percent !== null ? number_format($fee->remittance_percent, 2) . '%' : '—' }}</p>
            </div>
            <div>
                <h3 class="font-medium">Requirement Level</h3>
                <p class="text-gray-700 capitalize">{{ $fee->requirement_level }}</p>
            </div>
            <div>
                <h3 class="font-medium">Recurrence</h3>
                <p class="text-gray-700">{{ ucwords(str_replace('_', ' ', $fee->recurrence ?? 'one_time')) }}</p>
            </div>
            <div>
                <h3 class="font-medium">Status</h3>
                <p id="fee-status" class="text-gray-700 capitalize">{{ $fee->status }}</p>
            </div>
        </div>

        <div>
            <h3 class="font-medium">Supporting Documents</h3>
            <div class="mt-2 space-y-2">
                @if($fee->accreditationDocument)
                    <div>
                        <span class="text-sm text-gray-600">Accreditation Certification:</span>
                        <a href="{{ \Illuminate\Support\Facades\Storage::url($fee->accreditationDocument->file_path) }}" target="_blank" class="text-blue-600 hover:underline">{{ $fee->accreditationDocument->original_file_name }}</a>
                    </div>
                @else
                    <p class="text-gray-500 text-sm">No Certificate of Accreditation uploaded.</p>
                @endif

                @if($fee->resolutionDocument)
                    <div>
                        <span class="text-sm text-gray-600">Resolution of Collection:</span>
                        <a href="{{ \Illuminate\Support\Facades\Storage::url($fee->resolutionDocument->file_path) }}" target="_blank" class="text-blue-600 hover:underline">{{ $fee->resolutionDocument->original_file_name }}</a>
                    </div>
                @else
                    <p class="text-gray-500 text-sm">No Resolution of Collection uploaded.</p>
                @endif

                @if($fee->supportingDocument)
                    <div>
                        <span class="text-sm text-gray-600">Supporting Document:</span>
                        <a href="{{ \Illuminate\Support\Facades\Storage::url($fee->supportingDocument->file_path) }}" target="_blank" class="text-blue-600 hover:underline">{{ $fee->supportingDocument->original_file_name }}</a>
                    </div>
                @endif
            </div>
        </div>

        <div>
            <h3 class="font-medium">Submitted By</h3>
            <p class="text-gray-700">{{ $fee->user?->name ?? '—' }} — {{ $fee->created_at->format('Y-m-d') }}</p>
        </div>

        <div id="appeals" class="mt-6">
            <h3 class="font-medium">Appeals</h3>
            @if($fee->appeals->count() === 0)
                <p class="text-gray-500">No appeals have been submitted for this fee.</p>
            @else
                <div class="space-y-4 mt-4">
                    @foreach($fee->appeals as $appeal)
                        <div class="p-4 border rounded">
                            <div class="flex justify-between items-start">
                                <div>
                                    <div class="text-sm text-gray-600">Submitted by {{ $appeal->user?->name ?? '—' }} on {{ $appeal->created_at->format('Y-m-d') }}</div>
                                    <div class="mt-2">{{ $appeal->reason }}</div>

                                    @if($appeal->supporting_files && count($appeal->supporting_files) > 0)
                                        <div class="mt-2">
                                            <div class="text-sm text-gray-600">Files:</div>
                                            <ul class="list-disc pl-5 mt-1">
                                                @foreach($appeal->supporting_files as $file)
                                                    <li><a href="{{ \Illuminate\Support\Facades\Storage::url($file) }}" target="_blank" class="text-blue-600 hover:underline">{{ basename($file) }}</a></li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    @endif
                                </div>

                                <div class="text-right">
                                    <div class="mb-2 text-sm font-semibold capitalize">Status: {{ $appeal->status }}</div>
                                    @if($appeal->status !== 'pending')
                                        <div class="text-sm text-gray-600">Reviewed by {{ $appeal->reviewer?->name ?? '—' }} on {{ $appeal->reviewed_at?->format('Y-m-d') ?? '—' }}</div>
                                        @if($appeal->review_remark)
                                            <div class="mt-2 text-sm text-gray-700"><strong>Review remark:</strong> {{ $appeal->review_remark }}</div>
                                        @endif
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <div class="flex justify-end mt-6 gap-3">
            @if($fee->status === 'pending' && ($fee->approval_level === 'dean' || $fee->approval_level === 'osa'))
            <button id="approveBtn" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">Approve</button>
            @endif
            @if($fee->status !== 'disabled')
            <button id="rejectBtn" class="px-4 py-2 bg-gray-200 text-gray-700 rounded hover:bg-gray-300">Reject</button>
            @endif
            <a href="{{ route('college.fees.approval') }}" class="px-4 py-2 border rounded">Back</a>
        </div>

        <!-- Approve Modal (confirmation only) -->
        <div id="approveModal" class="hidden fixed inset-0 z-50 flex items-center justify-center">
            <div class="fixed inset-0 bg-black bg-opacity-50"></div>
            <div class="relative bg-white rounded-lg shadow-xl max-w-md w-full mx-4 z-10 p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Confirm Approval</h3>
                <p class="text-sm text-gray-500 mb-4">Confirm approving this fee. Dean approval will be forwarded to OSA when applicable.</p>
                <form id="approveForm" method="POST" action="{{ route('college.fees.approve', $fee->id) }}">
                    @csrf
                    <input type="hidden" name="confirm_action" value="approve">

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                        <input type="password" name="password" required class="w-full border rounded px-3 py-2" />
                        @error('password') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="flex justify-end gap-2">
                        <button type="button" onclick="closeApproveModal()" class="px-4 py-2 border rounded">Cancel</button>
                        <button type="submit" onclick="document.getElementById('approveForm').submit();" class="px-4 py-2 bg-green-600 text-white rounded">Confirm Approve</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Reject Modal (confirmation) -->
        <div id="rejectModal" class="hidden fixed inset-0 z-50 flex items-center justify-center">
            <div class="fixed inset-0 bg-black bg-opacity-50"></div>
            <div class="relative bg-white rounded-lg shadow-xl max-w-md w-full mx-4 z-10 p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Confirm Reject</h3>
                <p class="text-sm text-gray-500 mb-4">Confirm rejecting this fee. This will mark the fee as rejected.</p>
                <form id="rejectForm" method="POST" action="{{ route('college.fees.reject', $fee->id) }}">
                    @csrf
                    <input type="hidden" name="confirm_action" value="reject">

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                        <input type="password" name="password" required class="w-full border rounded px-3 py-2" />
                        @error('password') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="flex justify-end gap-2">
                        <button type="button" onclick="closeRejectModal()" class="px-4 py-2 border rounded">Cancel</button>
                        <button type="submit" onclick="document.getElementById('rejectForm').submit();" class="px-4 py-2 bg-red-600 text-white rounded">Confirm Reject</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function openApproveModal() {
    document.getElementById('approveModal').classList.remove('hidden');
}

function closeApproveModal() {
    document.getElementById('approveModal').classList.add('hidden');
}

function openRejectModal() {
    document.getElementById('rejectModal').classList.remove('hidden');
}

function closeRejectModal() {
    document.getElementById('rejectModal').classList.add('hidden');
}

// Attach event listeners to buttons
document.addEventListener('DOMContentLoaded', function() {
    const approveBtn = document.getElementById('approveBtn');
    if (approveBtn) {
        approveBtn.addEventListener('click', function(e) {
            e.preventDefault();
            openApproveModal();
        });
    }

    const rejectBtn = document.getElementById('rejectBtn');
    if (rejectBtn) {
        rejectBtn.addEventListener('click', function(e) {
            e.preventDefault();
            openRejectModal();
        });
    }
});
</script>
@endsection