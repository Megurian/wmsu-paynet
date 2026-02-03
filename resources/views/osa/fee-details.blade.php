@extends('layouts.dashboard')

@section('title', 'Fee Details')
@section('page-title', 'Fee Details (OSA)')

@section('content')
<div class="mb-6">
    <a href="{{ route('osa.fees') }}" class="text-sm text-gray-600 hover:underline">&larr; Back to Fees</a>
</div>

<div class="bg-white rounded shadow p-6 max-w-3xl">
    <h2 class="text-2xl font-bold mb-4">{{ $fee->fee_name }}</h2>

    <div class="grid grid-cols-1 gap-4">
        <div>
            <h3 class="font-medium">Organization</h3>
            <p class="text-gray-700">{{ $fee->organization->name }} ({{ $fee->organization->org_code }})</p>
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
                <h3 class="font-medium">Status</h3>
                <p id="fee-status" class="text-gray-700 capitalize">{{ $fee->status }}</p>
            </div>
        </div>

        <div>
            <h3 class="font-medium">Supporting Documents</h3>
            <div class="mt-2">
                @if($fee->accreditation_file)
                    <a href="{{ asset('storage/' . $fee->accreditation_file) }}" target="_blank" class="text-blue-600 hover:underline">Download Certificate of Accreditation</a>
                @else
                    <p class="text-gray-500">No Certificate of Accreditation uploaded.</p>
                @endif

                @if($fee->resolution_file)
                    <div class="mt-2">
                        <a href="{{ asset('storage/' . $fee->resolution_file) }}" target="_blank" class="text-blue-600 hover:underline">Download Resolution of Collection</a>
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
                                                    <li><a href="{{ asset('storage/' . $file) }}" target="_blank" class="text-blue-600 hover:underline">{{ basename($file) }}</a></li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    @endif
                                </div>

                                <div class="text-right">
                                    <div class="mb-2 text-sm font-semibold capitalize">Status: {{ $appeal->status }}</div>
                                    @if($appeal->status === 'pending')
                                        <button onclick="openAcceptModal({{ $appeal->id }})" class="mb-2 px-3 py-1 bg-green-600 text-white rounded">Accept</button>
                                        <button onclick="openRejectModal({{ $appeal->id }})" class="px-3 py-1 bg-red-600 text-white rounded">Reject</button>

                                        <!-- Accept Modal -->
                                        <div id="acceptModal-{{ $appeal->id }}" class="hidden fixed inset-0 z-50 flex items-center justify-center">
                                            <div class="fixed inset-0 bg-black bg-opacity-50"></div>
                                            <div class="relative bg-white rounded-lg shadow-xl max-w-md w-full mx-4 z-10 p-6">
                                                <h3 class="text-lg font-medium text-gray-900 mb-4">Accept Appeal</h3>
                                                <p class="text-sm text-gray-500 mb-4">Provide a remark and your password to accept this appeal.</p>
                                                <form method="POST" action="{{ route('osa.appeals.accept', $appeal->id) }}">
                                                    @csrf
                                                    <div class="mb-4">
                                                        <label class="block text-sm font-medium text-gray-700 mb-1">Remark</label>
                                                        <textarea name="remark" required rows="3" class="w-full border rounded px-3 py-2" placeholder="Reason for accepting the appeal..."></textarea>
                                                    </div>
                                                    <div class="mb-4">
                                                        <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                                                        <input type="password" name="password" required class="w-full border rounded px-3 py-2">
                                                    </div>
                                                    <div class="flex justify-end gap-2">
                                                        <button type="button" onclick="closeAcceptModal({{ $appeal->id }})" class="px-4 py-2 border rounded">Cancel</button>
                                                        <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded">Confirm Accept</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>

                                        <!-- Reject Modal -->
                                        <div id="rejectModal-{{ $appeal->id }}" class="hidden fixed inset-0 z-50 flex items-center justify-center">
                                            <div class="fixed inset-0 bg-black bg-opacity-50"></div>
                                            <div class="relative bg-white rounded-lg shadow-xl max-w-md w-full mx-4 z-10 p-6">
                                                <h3 class="text-lg font-medium text-gray-900 mb-4">Reject Appeal</h3>
                                                <p class="text-sm text-gray-500 mb-4">Provide a remark and your password to reject this appeal.</p>
                                                <form method="POST" action="{{ route('osa.appeals.reject', $appeal->id) }}">
                                                    @csrf
                                                    <div class="mb-4">
                                                        <label class="block text-sm font-medium text-gray-700 mb-1">Remark</label>
                                                        <textarea name="remark" required rows="3" class="w-full border rounded px-3 py-2" placeholder="Reason for rejecting the appeal..."></textarea>
                                                    </div>
                                                    <div class="mb-4">
                                                        <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                                                        <input type="password" name="password" required class="w-full border rounded px-3 py-2">
                                                    </div>
                                                    <div class="flex justify-end gap-2">
                                                        <button type="button" onclick="closeRejectModal({{ $appeal->id }})" class="px-4 py-2 border rounded">Cancel</button>
                                                        <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded">Confirm Reject</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>

                                        <script>
                                            function openAcceptModal(id) { document.getElementById('acceptModal-' + id).classList.remove('hidden'); }
                                            function closeAcceptModal(id) { document.getElementById('acceptModal-' + id).classList.add('hidden'); }
                                            function openRejectModal(id) { document.getElementById('rejectModal-' + id).classList.remove('hidden'); }
                                            function closeRejectModal(id) { document.getElementById('rejectModal-' + id).classList.add('hidden'); }
                                        </script>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
        <div class="flex justify-end mt-6 gap-3">
            @if($fee->status === 'pending')
            <button id="approveBtn" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">Approve</button>
            @endif
            @if($fee->status !== 'disabled')
            <button id="disableBtn" class="px-4 py-2 bg-gray-200 text-gray-700 rounded hover:bg-gray-300">Disable</button>
            @endif
            <a href="{{ route('osa.fees') }}" class="px-4 py-2 border rounded">Back</a>
        </div>

        <!-- Approve Modal -->
        <div id="approveModal" class="hidden fixed inset-0 z-50 flex items-center justify-center">
            <div class="fixed inset-0 bg-black bg-opacity-50"></div>
            <div class="relative bg-white rounded-lg shadow-xl max-w-md w-full mx-4 z-10 p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Confirm Approval</h3>
                <p class="text-sm text-gray-500 mb-4">Enter your password to confirm approving this fee. This action is final.</p>
                <form id="approveForm" method="POST" action="{{ route('osa.fees.approve', $fee->id) }}">
                    @csrf
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                        <input type="password" name="password" required class="w-full border rounded px-3 py-2">
                        @error('password') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div class="flex justify-end gap-2">
                        <button type="button" onclick="closeApproveModal()" class="px-4 py-2 border rounded">Cancel</button>
                        <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded">Confirm Approve</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Disable Modal -->
        <div id="disableModal" class="hidden fixed inset-0 z-50 flex items-center justify-center">
            <div class="fixed inset-0 bg-black bg-opacity-50"></div>
            <div class="relative bg-white rounded-lg shadow-xl max-w-md w-full mx-4 z-10 p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Confirm Disable</h3>
                <p class="text-sm text-gray-500 mb-4">Enter your password to confirm disabling this fee. Disabled fees cannot be collected.</p>
                <form id="disableForm" method="POST" action="{{ route('osa.fees.disable', $fee->id) }}">
                    @csrf
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                        <input type="password" name="password" required class="w-full border rounded px-3 py-2">
                        @error('password') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div class="flex justify-end gap-2">
                        <button type="button" onclick="closeDisableModal()" class="px-4 py-2 border rounded">Cancel</button>
                        <button type="submit" class="px-4 py-2 bg-gray-700 text-white rounded">Confirm Disable</button>
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

// Hook up approve button
const approveBtn = document.getElementById('approveBtn');
if (approveBtn) {
    approveBtn.addEventListener('click', function(e) {
        e.preventDefault();
        openApproveModal();
    });
}

// Hook up disable button
const disableBtn = document.getElementById('disableBtn');
if (disableBtn) {
    disableBtn.addEventListener('click', function(e) {
        e.preventDefault();
        openDisableModal();
    });
}

function openDisableModal() {
    document.getElementById('disableModal').classList.remove('hidden');
}

function closeDisableModal() {
    document.getElementById('disableModal').classList.add('hidden');
}
</script>
@endsection