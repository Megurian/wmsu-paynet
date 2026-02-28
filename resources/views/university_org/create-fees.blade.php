@extends('layouts.dashboard')

@section('title', 'Fees')
@section('page-title', ($organization?->org_code ?? 'Organization') . ' Setup of Fees')

@section('content')
<div class="mb-8">
    <h2 class="text-3xl font-bold text-gray-800"> {{ ($organization?->org_code ?? 'Organization') . " Setup of Fees" }} </h2>
    <p class="text-sm text-gray-500 mt-1">
        Welcome, {{ Auth::user()->name }}. Here you can manage the fees associated with different colleges within the university.
    </p>
</div>

<div class="flex justify-center">
    <form id="fee-form" method="POST" action="{{ route('university_org.fees.store') }}" enctype="multipart/form-data" class="w-full max-w-2xl bg-white p-8 rounded-lg shadow-lg">
        @csrf
        @if ($errors->any())
            <div class="mb-4 bg-red-50 border border-red-200 text-red-700 p-3 rounded">
                <ul class="list-disc pl-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        <input type="hidden" name="current_step" id="current_step" value="1">

        <!-- Progress Bar -->
        <div class="flex items-center justify-center mb-8">
            <!-- Step 1 -->
            <div class="flex items-center">
                <div id="step-1-indicator" class="step-indicator flex items-center justify-center w-10 h-10 rounded-full bg-red-700 text-white">
                    1
                </div>
                <div id="step-1-line" class="w-20 h-1 bg-gray-200 mx-4"></div>
            </div>
            
            <!-- Step 2 -->
            <div class="flex items-center">
                <div id="step-2-indicator" class="step-indicator flex items-center justify-center w-10 h-10 rounded-full bg-gray-200">
                    2
                </div>
                <div id="step-2-line" class="w-20 h-1 bg-gray-200 mx-4"></div>
            </div>

            <!-- Step 3 -->
            <div class="flex items-center">
                <div id="step-3-indicator" class="step-indicator flex items-center justify-center w-10 h-10 rounded-full bg-gray-200">
                    3
                </div>
            </div>
        </div>

        <!-- Step 1: Basic Information -->
        <div id="step-1" class="form-step">
            <div class="space-y-6">
                <div>
                    <label for="fee_name" class="block text-sm font-medium text-gray-700 mb-1">Fee Name</label>
                    <input type="text" id="fee_name" name="fee_name" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-red-500 focus:border-red-500"
                           placeholder="e.g., Student Activity Fee">
                </div>

                <div>
                    <label for="purpose" class="block text-sm font-medium text-gray-700 mb-1">Purpose of Collection</label>
                    <input type="text" id="purpose" name="purpose" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-red-500 focus:border-red-500"
                           placeholder="e.g., Library Fund">
                </div>

                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                    <textarea id="description" name="description" rows="4" required
                              class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-red-500 focus:border-red-500"
                              placeholder="Detailed explanation of what the fee covers..."></textarea>
                </div>

                <div class="flex justify-end mt-8">
                    <button type="button" id="to-step-2"
                            class="ml-3 inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-red-700 hover:bg-red-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                        Next
                    </button>
                </div>
            </div>
        </div>

        <!-- Step 2: Financial Configuration -->
        <div id="step-2" class="form-step hidden">
            <div class="space-y-6">
                <div>
                    <label for="amount" class="block text-sm font-medium text-gray-700 mb-1">Amount</label>
                    <div class="mt-1 relative rounded-md shadow-sm">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <span class="text-gray-500 sm:text-sm">₱</span>
                        </div>
                        <input type="number" id="amount" name="amount" min="0" step="0.01" required
                               class="focus:ring-red-500 focus:border-red-500 block w-full pl-7 pr-12 sm:text-sm border-gray-300 rounded-md"
                               placeholder="0.00">
                    </div>
                </div>

                <div>
                    <label for="remittance_percent" class="block text-sm font-medium text-gray-700 mb-1">Remittance (%) <span class="text-sm text-gray-500">(optional)</span></label>
                    <input type="number" id="remittance_percent" name="remittance_percent" step="0.01" min="0" max="100"
                           class="focus:ring-red-500 focus:border-red-500 block w-full pl-3 pr-3 sm:text-sm border-gray-300 rounded-md"
                           placeholder="e.g., 10 for 10%">
                </div>

                <div>
                    <label for="recurrence" class="block text-sm font-medium text-gray-700 mb-1">Fee Recurrence</label>
                    <select id="recurrence" name="recurrence" required class="w-full border rounded px-3 py-2">
                        <option value="one_time">One Time</option>
                        <option value="semestrial">Semestrial</option>
                        <option value="annual">Annual</option>
                    </select>
                </div>

                <div>
                    <div class="flex items-center gap-2">
                        <span class="block text-sm font-medium text-gray-700 mb-1">Requirement Level</span>
                        <!-- Tooltip icon -->
                        <button type="button" id="requirement-help" class="ml-2 text-gray-400 hover:text-gray-600 focus:outline-none" title="Why choose Mandatory vs Optional? Click for details." onclick="openRequirementModal()" aria-label="Requirement level help">
                            <svg  xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" viewBox="0 0 24 24" >
                                <!--Boxicons v3.0.8 https://boxicons.com | License  https://docs.boxicons.com/free-->
                                <path d="M11 11h2v6h-2zm0-4h2v2h-2z"></path>
                                <path d="M12 22c5.51 0 10-4.49 10-10S17.51 2 12 2 2 6.49 2 12s4.49 10 10 10m0-18c4.41 0 8 3.59 8 8s-3.59 8-8 8-8-3.59-8-8 3.59-8 8-8"></path>
                            </svg>
                        </button>
                    </div>
                    <div class="mt-2 flex items-center gap-6">
                        <label class="inline-flex items-center">
                            <input type="radio" name="requirement_level" value="mandatory" class="requirement-radio" />
                            <span class="ml-2">Mandatory</span>
                        </label>
                        <label class="inline-flex items-center">
                            <input type="radio" name="requirement_level" value="optional" class="requirement-radio" />
                            <span class="ml-2">Optional</span>
                        </label>
                    </div>
                </div>

                <div class="flex justify-between mt-8">
                    <button type="button" onclick="prevStep()" 
                            class="inline-flex justify-center py-2 px-4 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                        Back
                    </button>
                    <button type="button" id="to-step-3" 
                            class="ml-3 inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-red-700 hover:bg-red-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                        Next
                    </button>
                </div>
            </div>
        </div>

        <!-- Step 3: Supporting Documentation -->
        <div id="step-3" class="form-step hidden">
            <div class="space-y-6">
                <div>
                    <h3 class="text-lg font-medium text-gray-800">Supporting Documentation</h3>
                    <p class="text-sm text-gray-500">Upload legal proof required based on the selected requirement level.</p>
                </div>

                <!-- Optional: accreditation only -->
                <div id="supporting-optional" class="hidden">
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Certificate of Accreditation / Accreditation Permit</label>
                        <div class="space-y-3">
                            @if($accreditationDocuments->count() > 0)
                                <div>
                                    <label class="block text-xs font-semibold text-gray-600 mb-2">Select from existing documents:</label>
                                    <select name="accreditation_document_id" id="accreditation_document_id_optional" class="w-full border rounded px-3 py-2 focus:ring-red-500 focus:border-red-500">
                                        <option value="">-- Use new upload below --</option>
                                        @foreach($accreditationDocuments as $doc)
                                            <option value="{{ $doc->id }}">{{ $doc->original_file_name }} ({{ $doc->created_at->format('M d, Y') }})</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="relative">
                                    <div class="absolute inset-0 flex items-center"><span class="w-full border-t border-gray-300"></span></div>
                                    <div class="relative flex justify-center text-xs uppercase"><span class="bg-white px-2 text-gray-500">Or upload new</span></div>
                                </div>
                            @endif
                            <input type="file" id="accreditation_file_optional" name="accreditation_file" accept=".pdf,image/*,.doc,.docx" class="w-full" />
                        </div>
                    </div>
                </div>

                <!-- Mandatory: accreditation + resolution -->
                <div id="supporting-mandatory" class="hidden space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Certificate of Accreditation / Accreditation Permit</label>
                        <div class="space-y-3">
                            @if($accreditationDocuments->count() > 0)
                                <div>
                                    <label class="block text-xs font-semibold text-gray-600 mb-2">Select from existing documents:</label>
                                    <select name="accreditation_document_id" id="accreditation_document_id_mandatory" class="w-full border rounded px-3 py-2 focus:ring-red-500 focus:border-red-500">
                                        <option value="">-- Use new upload below --</option>
                                        @foreach($accreditationDocuments as $doc)
                                            <option value="{{ $doc->id }}">{{ $doc->original_file_name }} ({{ $doc->created_at->format('M d, Y') }})</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="relative">
                                    <div class="absolute inset-0 flex items-center"><span class="w-full border-t border-gray-300"></span></div>
                                    <div class="relative flex justify-center text-xs uppercase"><span class="bg-white px-2 text-gray-500">Or upload new</span></div>
                                </div>
                            @endif
                            <input type="file" id="accreditation_file_mandatory" name="accreditation_file" accept=".pdf,image/*,.doc,.docx" class="w-full" />
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Resolution of Collection</label>
                        <div class="space-y-3">
                            @if($resolutionDocuments->count() > 0)
                                <div>
                                    <label class="block text-xs font-semibold text-gray-600 mb-2">Select from existing documents:</label>
                                    <select name="resolution_document_id" id="resolution_document_id_mandatory" class="w-full border rounded px-3 py-2 focus:ring-red-500 focus:border-red-500">
                                        <option value="">-- Use new upload below --</option>
                                        @foreach($resolutionDocuments as $doc)
                                            <option value="{{ $doc->id }}">{{ $doc->original_file_name }} ({{ $doc->created_at->format('M d, Y') }})</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="relative">
                                    <div class="absolute inset-0 flex items-center"><span class="w-full border-t border-gray-300"></span></div>
                                    <div class="relative flex justify-center text-xs uppercase"><span class="bg-white px-2 text-gray-500">Or upload new</span></div>
                                </div>
                            @endif
                            <input type="file" id="resolution_file" name="resolution_file" accept=".pdf,image/*,.doc,.docx" class="w-full" />
                        </div>
                    </div>
                </div>

                <!-- Supporting Document (Optional, applies to all fees) -->
                <div class="border-t border-gray-200 pt-6 mt-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Supporting Document <span class="text-xs text-gray-500">(optional)</span></label>
                    <p class="text-xs text-gray-500 mb-3">Upload additional documents specific to this fee, such as supplementary evidence or supporting materials.</p>
                    <input type="file" id="supporting_file" name="supporting_file" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent" />
                    <p class="text-xs text-gray-500 mt-2">Allowed formats: PDF, DOC, DOCX, JPG, JPEG, PNG (Max 5MB)</p>
                </div>

                <div class="flex justify-between mt-8">
                    <button type="button" onclick="prevStep()" 
                            class="inline-flex justify-center py-2 px-4 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                        Back
                    </button>
                    <button type="submit" id="submit-fee" 
                            class="ml-3 inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-red-700 hover:bg-red-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                        Submit Fee
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

<!-- Requirement Level Modal -->
<div id="requirement-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center">
  <div id="requirement-modal-overlay" class="fixed inset-0 bg-black bg-opacity-50"></div>
  <div class="relative bg-white rounded-lg shadow-xl max-w-xl w-full mx-4 z-10 p-6">
    <div class="flex items-start justify-between">
      <h3 id="requirement-modal-title" class="text-lg font-medium text-gray-900">Requirement Level Details</h3>
      <button type="button" onclick="closeRequirementModal()" class="text-gray-400 hover:text-gray-600 focus:outline-none" aria-label="Close modal">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
      </button>
    </div>
    <div id="requirement-modal-body" class="mt-4 text-sm text-gray-700">
      <p>Please select a requirement level to view details.</p>
    </div>
    <div class="mt-6 flex justify-end">
      <button type="button" onclick="closeRequirementModal()" class="inline-flex justify-center py-2 px-4 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none">Close</button>
    </div>
  </div>
</div>

<script>
let currentStep = 1;

function updateProgress() {
    for (let i = 1; i <= 3; i++) {
        const indicator = document.getElementById(`step-${i}-indicator`);
        const line = document.getElementById(`step-${i}-line`);

        if (i < currentStep) {
            indicator.classList.remove('bg-gray-200');
            indicator.classList.add('bg-green-500', 'text-white');
            if (line) line.classList.add('bg-green-500');
        } else if (i === currentStep) {
            indicator.classList.remove('bg-gray-200', 'bg-green-500');
            indicator.classList.add('bg-red-700', 'text-white');
            if (line) line.classList.remove('bg-green-500');
        } else {
            indicator.classList.remove('bg-red-700', 'bg-green-500', 'text-white');
            indicator.classList.add('bg-gray-200');
            if (line) line.classList.remove('bg-green-500');
        }
    }
}

function showStep(step) {
    document.querySelectorAll('.form-step').forEach(step => {
        step.classList.add('hidden');
    });

    const currentStepElement = document.getElementById(`step-${step}`);
    if (currentStepElement) {
        currentStepElement.classList.remove('hidden');
    }
    currentStep = step;
    document.getElementById('current_step').value = currentStep;
    updateProgress();
}

function prevStep() {
    if (currentStep > 1) {
        showStep(currentStep - 1);
    }
}

function validateStep1() {
    const feeName = document.getElementById('fee_name').value.trim();
    const purpose = document.getElementById('purpose').value.trim();
    const description = document.getElementById('description').value.trim();
    if (!feeName) { alert('Please enter a Fee Name.'); return false; }
    if (!purpose) { alert('Please enter a Purpose of Collection.'); return false; }
    if (!description) { alert('Please enter a Description.'); return false; }
    return true;
}

function validateStep2() {
    const amount = parseFloat(document.getElementById('amount').value);
    const req = document.querySelector('input[name="requirement_level"]:checked');
    const rec = document.getElementById('recurrence') ? document.getElementById('recurrence').value : null;
    if (isNaN(amount) || amount < 0) { alert('Please enter a valid Amount (0 or more).'); return false; }
    if (!req) { alert('Please select Requirement Level (Mandatory or Optional).'); return false; }
    if (!rec) { alert('Please select Fee Recurrence.'); return false; }
    return true;
}

function updateSupportingDocs() {
    const selectedEl = document.querySelector('input[name="requirement_level"]:checked');
    if (!selectedEl) return;
    const selected = selectedEl.value;
    const opt = document.getElementById('supporting-optional');
    const mand = document.getElementById('supporting-mandatory');

    // Reset visibility and disabled attributes
    opt.classList.add('hidden');
    mand.classList.add('hidden');

    const accOpt = document.getElementById('accreditation_file_optional');
    const accMand = document.getElementById('accreditation_file_mandatory');
    const res = document.getElementById('resolution_file');

    // Disable all by default (so only the visible one is submitted)
    if (accOpt) { accOpt.disabled = true; }
    if (accMand) { accMand.disabled = true; }
    if (res) { res.disabled = true; }

    if (selected === 'optional') {
        if (opt) opt.classList.remove('hidden');
        if (accOpt) { accOpt.disabled = false; }
    } else if (selected === 'mandatory') {
        if (mand) mand.classList.remove('hidden');
        if (accMand) { accMand.disabled = false; }
        if (res) { res.disabled = false; }
    }
}

function goToStep3() {
    if (!validateStep2()) return;
    // ensure supporting docs reflect the choice
    updateSupportingDocs();
    showStep(3);
}

// Modal controls for requirement level details
function openRequirementModal() {
    const req = document.querySelector('input[name="requirement_level"]:checked');
    const title = document.getElementById('requirement-modal-title');
    const body = document.getElementById('requirement-modal-body');

    if (!req) {
        title.textContent = 'Requirement Level Details';
        body.innerHTML = '<p class="mb-2">No selection detected. Please select <strong>Mandatory</strong> or <strong>Optional</strong> to see specific details.</p>' +
                         '<p><strong>Mandatory:</strong> Payment becomes mandatory before enrollment and requires the specified documents to be submitted; the submission will be subject to approval by the OSA.</p>' +
                         '<p class="mt-2"><strong>Optional:</strong> The fee will not be required before enrollment; however, submit the Certificate of Accreditation (CoA). It will be subject to approval by the OSA before it can be collected.</p>';
    } else if (req.value === 'mandatory') {
        title.textContent = 'Mandatory: Details';
        body.innerHTML = '<p>The payment will become <strong>mandatory before enrollment</strong>. The specified documents (Certificate of Accreditation/Accreditation Permit and Resolution of Collection) must be submitted. Submissions will be <strong>subject to approval by the OSA</strong> before collection can proceed.</p>';
    } else {
        title.textContent = 'Optional: Details';
        body.innerHTML = '<p>The fee will <strong>not be required before enrollment</strong>. However, the <strong>Certificate of Accreditation (CoA)</strong> must be submitted. Submissions will be <strong>subject to approval by the OSA</strong> before the fee can be collected.</p>';
    }

    const modal = document.getElementById('requirement-modal');
    modal.classList.remove('hidden');
    document.getElementById('requirement-modal-overlay').addEventListener('click', closeRequirementModal);
    document.addEventListener('keydown', onModalKeyDown);
}

function closeRequirementModal() {
    const modal = document.getElementById('requirement-modal');
    modal.classList.add('hidden');
    document.removeEventListener('keydown', onModalKeyDown);
}

function onModalKeyDown(e) {
    if (e.key === 'Escape') closeRequirementModal();
}


document.addEventListener('DOMContentLoaded', function() {
    showStep(1);

    document.getElementById('to-step-2').addEventListener('click', function() {
        if (validateStep1()) showStep(2);
    });

    document.getElementById('to-step-3').addEventListener('click', goToStep3);

    document.querySelectorAll('.requirement-radio').forEach(r => {
        r.addEventListener('change', updateSupportingDocs);
    });

    // initialize supporting docs state in case of validation errors / pre-selected values
    updateSupportingDocs();

    const form = document.getElementById('fee-form');
    if (form) {
        form.onsubmit = function(e) {
            // client-side validation for required files based on requirement level
            const req = document.querySelector('input[name="requirement_level"]:checked');
            if (!req) { e.preventDefault(); alert('Requirement Level is missing.'); showStep(2); return false; }
            if (req.value === 'mandatory') {
                const accFile = document.getElementById('accreditation_file_mandatory');
                const accDocId = document.getElementById('accreditation_document_id_mandatory');
                const resFile = document.getElementById('resolution_file');
                const resDocId = document.getElementById('resolution_document_id_mandatory');
                
                // Check if either a file is uploaded OR a document is selected
                const hasAccreditation = (accFile && accFile.files.length > 0) || (accDocId && accDocId.value);
                const hasResolution = (resFile && resFile.files.length > 0) || (resDocId && resDocId.value);
                
                if (!hasAccreditation) { e.preventDefault(); alert('Please upload or select the Certificate of Accreditation/Accreditation Permit.'); showStep(3); return false; }
                if (!hasResolution) { e.preventDefault(); alert('Please upload or select the Resolution of Collection.'); showStep(3); return false; }
            }
            // if optional, accreditation is optional
            // allow the form to submit normally
            return true;
        };
    }
});
</script>

<style>
.form-step {
    transition: all 0.3s ease;
}
.step-indicator {
    transition: all 0.3s ease;
}
/* Modal small styling */
#requirement-modal .relative {
    transform: translateY(0);
    transition: transform 0.15s ease-out, opacity 0.15s ease-out;
}
#requirement-modal:not(.hidden) .relative {
    transform: translateY(0);
    opacity: 1;
}
</style>
@endsection