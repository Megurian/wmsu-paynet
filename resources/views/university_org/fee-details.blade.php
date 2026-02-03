@extends('layouts.dashboard')

@section('title', 'Fee Details')
@section('page-title', 'Fee Details')

@section('content')
<div class="mb-6">
    <a href="{{ route('university_org.fees') }}" class="text-sm text-gray-600 hover:underline">&larr; Back to Fees</a>
</div>

<div class="bg-white rounded shadow p-6 max-w-3xl">
    <h2 class="text-2xl font-bold mb-4">{{ $fee->fee_name }}</h2>

    @if($fee->status === 'approved')
        <div class="mb-4 flex items-start gap-3 bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 flex-shrink-0" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.707a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
            </svg>
            <div>
                <div class="font-semibold">Approved by OSA</div>
                <div class="text-sm text-green-700">This fee has been reviewed and approved by the Office of Student Affairs.</div>
            </div>
        </div>
    @endif

    @if($fee->status === 'disabled')
        <div class="mb-4 flex items-start gap-3 bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 flex-shrink-0" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.293 7.293a1 1 0 011.414 0L10 7.586l.293-.293a1 1 0 111.414 1.414L11.414 9l.293.293a1 1 0 01-1.414 1.414L10 10.414l-.293.293a1 1 0 01-1.414-1.414L8.586 9l-.293-.293a1 1 0 010-1.414z" clip-rule="evenodd" />
            </svg>
            <div>
                <div class="font-semibold">Disabled by OSA</div>
                <div class="text-sm text-red-700">This fee has been disabled by the Office of Student Affairs and cannot be collected.</div>
            </div>
        </div>
    @endif

    <div class="grid grid-cols-1 gap-4">
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

        <!-- Appeals history (read-only) -->
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

        @if($fee->status === 'disabled')
        <div class="mt-6 flex justify-end">
            <button id="openAppealBtn" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Appeal Disable</button>
        </div>

        <!-- Appeal Modal -->
        <div id="appealModal" class="hidden fixed inset-0 z-50 flex items-center justify-center">
            <div class="fixed inset-0 bg-black bg-opacity-50"></div>
            <div class="relative bg-white rounded-lg shadow-xl max-w-lg w-full mx-4 z-10 p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Submit Appeal</h3>
                <p class="text-sm text-gray-500 mb-4">Provide a reason for appeal and upload supporting documents (optional, up to 10 files).</p>
                <form id="appealForm" method="POST" action="{{ route('university_org.fees.appeal', $fee->id) }}" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Reason for Appeal</label>
                        <textarea name="reason" required rows="4" class="w-full border rounded px-3 py-2" placeholder="Explain why the fee should be reinstated..."></textarea>
                        @error('reason') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Supporting Documents (optional)</label>
                        <input id="supporting_files" name="supporting_files[]" type="file" multiple accept=".pdf,image/*" class="w-full" />
                        <p class="text-xs text-gray-500 mt-1">You may upload up to 10 files, each up to 5MB.</p>
                        @error('supporting_files.*') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="flex justify-end gap-2">
                        <button type="button" onclick="closeAppealModal()" class="px-4 py-2 border rounded">Cancel</button>
                        <button id="submitAppealBtn" type="submit" class="px-4 py-2 bg-blue-600 text-white rounded">Submit Appeal</button>
                    </div>
                </form>
            </div>
        </div>
        @endif
    </div>
</div>

<script>
// Appeal modal logic
const openAppealBtn = document.getElementById('openAppealBtn');
if (openAppealBtn) {
    openAppealBtn.addEventListener('click', function(e) {
        e.preventDefault();
        document.getElementById('appealModal').classList.remove('hidden');
    });
}

function closeAppealModal() {
    document.getElementById('appealModal').classList.add('hidden');
}

// Prevent uploading more than 10 files
const supportingFilesInput = document.getElementById('supporting_files');
if (supportingFilesInput) {
    supportingFilesInput.addEventListener('change', function() {
        if (this.files.length > 10) {
            alert('You can upload a maximum of 10 files.');
            this.value = null;
        }
    });
}
</script>
@endsection