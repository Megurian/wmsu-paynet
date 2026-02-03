@extends('layouts.dashboard')

@section('title', 'Fees')
@section('page-title', ($organization?->org_code ?? 'Organization') . ' Setup of Fees')

@section('content')
<div class="mb-8">
    <h2 class="text-3xl font-bold text-gray-800"> {{ ($organization?->org_code ?? 'Organization') . " Setup of Fees" }} </h2>
    <p class="text-sm text-gray-500 mt-1">
        Welcome, {{ Auth::user()->name }}. Here you can manage the fees associated with this college organization.
    </p>
</div>

<div class="flex justify-center">
    <form id="fee-form" method="POST" action="{{ route('college_org.fees.store') }}" enctype="multipart/form-data" class="w-full max-w-2xl bg-white p-8 rounded-lg shadow-lg">
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
            <!-- Step indicators copied from university_org view -->
            <div class="flex items-center">
                <div id="step-1-indicator" class="step-indicator flex items-center justify-center w-10 h-10 rounded-full bg-red-700 text-white">1</div>
                <div id="step-1-line" class="w-20 h-1 bg-gray-200 mx-4"></div>
            </div>
            <div class="flex items-center">
                <div id="step-2-indicator" class="step-indicator flex items-center justify-center w-10 h-10 rounded-full bg-gray-200">2</div>
                <div id="step-2-line" class="w-20 h-1 bg-gray-200 mx-4"></div>
            </div>
            <div class="flex items-center">
                <div id="step-3-indicator" class="step-indicator flex items-center justify-center w-10 h-10 rounded-full bg-gray-200">3</div>
            </div>
        </div>

        <!-- Step 1 -->
        <div id="step-1" class="form-step">
            <div class="space-y-6">
                <div>
                    <label for="fee_name" class="block text-sm font-medium text-gray-700 mb-1">Fee Name</label>
                    <input type="text" id="fee_name" name="fee_name" required class="w-full px-3 py-2 border border-gray-300 rounded-md" placeholder="e.g., Library Fee" />
                </div>

                <div>
                    <label for="purpose" class="block text-sm font-medium text-gray-700 mb-1">Purpose of Collection</label>
                    <input type="text" id="purpose" name="purpose" required class="w-full px-3 py-2 border border-gray-300 rounded-md" placeholder="e.g., Library Fund" />
                </div>

                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                    <textarea id="description" name="description" rows="4" required class="w-full px-3 py-2 border border-gray-300 rounded-md" placeholder="Detailed explanation..."></textarea>
                </div>

                <div class="flex justify-end mt-8">
                    <button type="button" id="to-step-2" class="ml-3 inline-flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-red-700">Next</button>
                </div>
            </div>
        </div>

        <!-- Step 2 -->
        <div id="step-2" class="form-step hidden">
            <div class="space-y-6">
                <div>
                    <label for="amount" class="block text-sm font-medium text-gray-700 mb-1">Amount</label>
                    <div class="mt-1 relative rounded-md shadow-sm">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <span class="text-gray-500 sm:text-sm">₱</span>
                        </div>
                        <input type="number" id="amount" name="amount" min="0" step="0.01" required class="block w-full pl-7 pr-12 sm:text-sm border-gray-300 rounded-md" placeholder="0.00" />
                    </div>
                </div>

                <div>
                    <label for="remittance_percent" class="block text-sm font-medium text-gray-700 mb-1">Remittance (%) <span class="text-sm text-gray-500">(optional)</span></label>
                    <input type="number" id="remittance_percent" name="remittance_percent" step="0.01" min="0" max="100" class="w-full border rounded px-3 py-2" placeholder="e.g., 10" />
                </div>

                <div>
                    <div class="flex items-center gap-2">
                        <span class="block text-sm font-medium text-gray-700 mb-1">Requirement Level</span>
                        <button type="button" id="requirement-help" class="ml-2 text-gray-400 hover:text-gray-600" onclick="openRequirementModal()" title="Why choose Mandatory vs Optional?">?</button>
                    </div>
                    <div class="mt-2 flex items-center gap-6">
                        <label class="inline-flex items-center"><input type="radio" name="requirement_level" value="mandatory" class="requirement-radio" /> <span class="ml-2">Mandatory</span></label>
                        <label class="inline-flex items-center"><input type="radio" name="requirement_level" value="optional" class="requirement-radio" /> <span class="ml-2">Optional</span></label>
                    </div>
                </div>

                <div class="flex justify-between mt-8">
                    <button type="button" onclick="prevStep()" class="inline-flex py-2 px-4 border rounded text-gray-700">Back</button>
                    <button type="button" id="to-step-3" class="ml-3 inline-flex py-2 px-4 bg-red-700 text-white rounded">Next</button>
                </div>
            </div>
        </div>

        <!-- Step 3 -->
        <div id="step-3" class="form-step hidden">
            <div class="space-y-6">
                <div>
                    <h3 class="text-lg font-medium text-gray-800">Supporting Documentation</h3>
                    <p class="text-sm text-gray-500">Upload legal proof required based on the selected requirement level.</p>
                </div>

                <div id="supporting-optional" class="hidden">
                    <label for="accreditation_file_optional" class="block text-sm font-medium text-gray-700 mb-1">Certificate of Accreditation / Accreditation Permit</label>
                    <input type="file" id="accreditation_file_optional" name="accreditation_file" accept=".pdf,image/*" class="w-full" />
                </div>

                <div id="supporting-mandatory" class="hidden space-y-4">
                    <div>
                        <label for="accreditation_file_mandatory" class="block text-sm font-medium text-gray-700 mb-1">Certificate of Accreditation / Accreditation Permit</label>
                        <input type="file" id="accreditation_file_mandatory" name="accreditation_file" accept=".pdf,image/*" class="w-full" />
                    </div>
                    <div>
                        <label for="resolution_file" class="block text-sm font-medium text-gray-700 mb-1">Resolution of Collection</label>
                        <input type="file" id="resolution_file" name="resolution_file" accept=".pdf,image/*" class="w-full" />
                    </div>
                </div>

                <div class="flex justify-between mt-8">
                    <button type="button" onclick="prevStep()" class="inline-flex py-2 px-4 border rounded text-gray-700">Back</button>
                    <button type="submit" id="submit-fee" class="ml-3 inline-flex py-2 px-4 bg-red-700 text-white rounded">Submit Fee</button>
                </div>
            </div>
        </div>
    </form>
</div>

<!-- Requirement Modal (same as university_org) -->
<div id="requirement-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center">
  <div id="requirement-modal-overlay" class="fixed inset-0 bg-black bg-opacity-50"></div>
  <div class="relative bg-white rounded-lg shadow-xl max-w-xl w-full mx-4 z-10 p-6">
    <div class="flex items-start justify-between">
      <h3 id="requirement-modal-title" class="text-lg font-medium text-gray-900">Requirement Level Details</h3>
      <button type="button" onclick="closeRequirementModal()" class="text-gray-400 hover:text-gray-600 focus:outline-none" aria-label="Close modal">×</button>
    </div>
    <div id="requirement-modal-body" class="mt-4 text-sm text-gray-700">
      <p>Please select a requirement level to view details.</p>
    </div>
    <div class="mt-6 flex justify-end">
      <button type="button" onclick="closeRequirementModal()" class="inline-flex justify-center py-2 px-4 border rounded text-gray-700 bg-white">Close</button>
    </div>
  </div>
</div>

<script>
// Copy the JS from university_org/create-fees and change the submit route is already set in the form action
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
    if (isNaN(amount) || amount < 0) { alert('Please enter a valid Amount (0 or more).'); return false; }
    if (!req) { alert('Please select Requirement Level (Mandatory or Optional).'); return false; }
    return true;
}

function updateSupportingDocs() {
    const selectedEl = document.querySelector('input[name="requirement_level"]:checked');
    if (!selectedEl) return;
    const selected = selectedEl.value;
    const opt = document.getElementById('supporting-optional');
    const mand = document.getElementById('supporting-mandatory');

    // Reset visibility, required and disabled attributes
    opt.classList.add('hidden');
    mand.classList.add('hidden');

    const accOpt = document.getElementById('accreditation_file_optional');
    const accMand = document.getElementById('accreditation_file_mandatory');
    const res = document.getElementById('resolution_file');

    // Disable all by default (so only the visible one is submitted)
    if (accOpt) { accOpt.required = false; accOpt.disabled = true; }
    if (accMand) { accMand.required = false; accMand.disabled = true; }
    if (res) { res.required = false; res.disabled = true; }

    if (selected === 'optional') {
        if (opt) opt.classList.remove('hidden');
        if (accOpt) { accOpt.disabled = false; }
    } else if (selected === 'mandatory') {
        if (mand) mand.classList.remove('hidden');
        if (accMand) { accMand.required = true; accMand.disabled = false; }
        if (res) { res.required = true; res.disabled = false; }
    }
}

function goToStep3() {
    if (!validateStep2()) return;
    updateSupportingDocs();
    showStep(3);
}

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

function onModalKeyDown(e) { if (e.key === 'Escape') closeRequirementModal(); }

document.addEventListener('DOMContentLoaded', function() {
    showStep(1);

    document.getElementById('to-step-2').addEventListener('click', function() { if (validateStep1()) showStep(2); });
    document.getElementById('to-step-3').addEventListener('click', goToStep3);
    document.querySelectorAll('.requirement-radio').forEach(r => { r.addEventListener('change', updateSupportingDocs); });

    // initialize supporting docs
    updateSupportingDocs();

    const form = document.getElementById('fee-form');
    if (form) {
        form.onsubmit = function(e) {
            const req = document.querySelector('input[name="requirement_level"]:checked');
            if (!req) { e.preventDefault(); alert('Requirement Level is missing.'); showStep(2); return false; }
            if (req.value === 'mandatory') {
                const a = document.getElementById('accreditation_file_mandatory');
                const r = document.getElementById('resolution_file');
                if (!a || a.files.length === 0) { e.preventDefault(); alert('Please upload the Certificate of Accreditation/Accreditation Permit.'); showStep(3); return false; }
                if (!r || r.files.length === 0) { e.preventDefault(); alert('Please upload the Resolution of Collection.'); showStep(3); return false; }
            }
            return true;
        };
    }
});
</script>

<style>
.form-step { transition: all 0.3s ease; }
.step-indicator { transition: all 0.3s ease; }
#requirement-modal .relative { transform: translateY(0); transition: transform 0.15s ease-out, opacity 0.15s ease-out; }
#requirement-modal:not(.hidden) .relative { transform: translateY(0); opacity: 1; }
</style>
@endsection