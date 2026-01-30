@extends('layouts.dashboard')

@section('title', 'Add Organization')
@section('page-title', 'Add Organization')

@section('content')
<div class="flex justify-center">
    <form id="organizationForm" method="POST" action="{{ route('osa.organizations.store') }}" enctype="multipart/form-data" class="w-full max-w-2xl bg-white p-8 rounded-lg shadow-lg">
        @csrf
        <input type="hidden" name="current_step" id="current_step" value="{{ old('current_step', 1) }}">

        <div class="flex items-center justify-center mb-8">
            <div class="flex items-center">
                <div id="dot-1" class="w-6 h-6 rounded-full flex items-center justify-center text-white font-bold">1</div>
                <div id="line-1" class="w-24 h-1 "></div>
            </div>
            <div class="flex items-center">
                <div id="dot-2" class="w-6 h-6 rounded-full flex items-center justify-center text-white font-bold">2</div>
                <div id="line-2" class="w-24 h-1 bg-gray-300"></div>
            </div>
            <div class="flex items-center">
                <div id="dot-3" class="w-6 h-6 rounded-full flex items-center justify-center text-white font-bold">3</div>
            </div>
        </div>

        <!-- Organization Type -->
        <div class="form-step" id="step-1">
            <h3 class="text-xl font-bold mb-6 text-center">Organization Type</h3>

            <div class="mb-4">
                <label class="block font-medium mb-1">Select Type</label>
                <select name="role" id="org-role" class="w-full border border-gray-300 px-4 py-2 rounded focus:outline-none focus:ring-2 focus:ring-blue-400">
                    <option value="">-- Select Type --</option>
                    <option value="university_org" {{ old('role')=='university_org'?'selected':'' }}>University-wide</option>
                    <option value="college_org" {{ old('role')=='college_org'?'selected':'' }}>Under a College</option>
                </select>
                <p id="roleFeedback" class="text-xs mt-1 text-red-600"></p>
            </div>

            <div id="college-select" class="mb-4 {{ old('role')==='college_org' ? '' : 'hidden' }}">
                <label class="block font-medium mb-1">Select College</label>
                <select name="college_id" class="w-full border border-gray-300 px-4 py-2 rounded focus:outline-none focus:ring-2 focus:ring-blue-400">
                    <option value="">-- Choose College --</option>
                    @foreach($colleges as $college)
                        <option value="{{ $college->id }}" {{ old('college_id')==$college->id?'selected':'' }}>{{ $college->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex justify-end">
                <button type="button" id="nextStepBtn1" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700 transition" onclick="nextStep(1)">Next</button>
            </div>
        </div>

        <!--  Organization Details -->
        <div class="form-step hidden" id="step-2">
            <h3 class="text-xl font-bold mb-6 text-center">Organization Details</h3>

            <div class="mb-4">
                <label class="block font-medium mb-1">Organization Name</label>
                <input type="text" name="name" value="{{ old('name') }}" placeholder="Enter Organization Name" class="w-full border border-gray-300 px-4 py-2 rounded focus:outline-none focus:ring-2 focus:ring-blue-400" required>
            </div>

            <div class="mb-4">
                <label class="block font-medium mb-1">Organization Code</label>
                <input id="org_code_input" type="text" name="org_code" value="{{ old('org_code') }}" placeholder="Enter Organization Code" class="w-full border border-gray-300 px-4 py-2 rounded focus:outline-none focus:ring-2 focus:ring-blue-400" required>
                <p class="text-xs text-gray-500 mt-1">Unique code for the organization</p>
                <p id="codeFeedback" class="text-xs mt-1"></p>
            </div>

            <div class="mb-6 relative">
                <label class="block font-medium mb-2">Organization Logo (Optional)</label>
                <div id="logoUpload" class="w-40 h-40 border-2 border-dashed border-gray-300 rounded-lg flex items-center justify-center cursor-pointer relative mx-auto">
                    <button type="button" id="removeLogo" class="hidden absolute -top-7 -right-3 right-0 bg-red-600 text-white rounded-full w-6 h-6 flex items-center justify-center text-xs hover:bg-red-700">×</button>
                    <span id="logoPlus" class="text-gray-400 text-4xl font-bold">+</span>
                    <img id="logoPreview" class="hidden w-full h-full object-cover rounded-lg absolute top-0 left-0" alt="Logo Preview">
                    <input type="file" name="logo" id="logoInput" accept="image/*" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer">
                </div>
                <p class="text-xs text-gray-500 mt-1 text-center">Click to upload organization logo</p>
            </div>

            <div class="flex justify-between">
                <button type="button" class="bg-gray-400 text-white px-6 py-2 rounded hover:bg-gray-500 transition" onclick="prevStep(2)">Back</button>
                <button type="button" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700 transition" onclick="nextStep(2)">Next</button>
            </div>
        </div>

        <!--  Admin Account -->
        <div class="form-step hidden" id="step-3">
            <h3 class="text-xl font-bold mb-6 text-center">Organization President Account</h3>

            <div class="mb-4">
                <label class="block font-medium mb-1">Admin Name</label>
                <input type="text" name="admin_name" value="{{ old('admin_name') }}" placeholder="Enter Admin Name" class="w-full border border-gray-300 px-4 py-2 rounded focus:outline-none focus:ring-2 focus:ring-blue-400" required>
            </div>

            <div class="mb-4">
                <label class="block font-medium mb-1">Admin Email</label>
                <input id="admin_email_input" type="email" name="admin_email" value="{{ old('admin_email') }}" placeholder="Enter Admin Email" class="w-full border border-gray-300 px-4 py-2 rounded focus:outline-none focus:ring-2 focus:ring-blue-400" required>
                <p id="emailFeedback" class="text-xs mt-1"></p>
            </div>

            <div class="mb-4 relative">
                <label class="block font-medium mb-1">Password</label>
                <input id="admin_password" type="password" name="admin_password" placeholder="Enter Password" class="w-full border border-gray-300 px-4 py-2 pr-10 rounded focus:outline-none focus:ring-2 focus:ring-blue-400" required>
                <button type="button" class="toggle-password-btn absolute right-2 top-1/2 transform -translate-y-1/2 text-gray-500" aria-label="Toggle password visibility" onclick="togglePassword('admin_password', this)">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8S1 12 1 12z"/><circle cx="12" cy="12" r="3"/></svg>
                </button>
            </div>

            <div class="mb-6 relative">
                <label class="block font-medium mb-1">Confirm Password</label>
                <input id="admin_password_confirmation" type="password" name="admin_password_confirmation" placeholder="Confirm Password" class="w-full border border-gray-300 px-4 py-2 pr-10 rounded focus:outline-none focus:ring-2 focus:ring-blue-400" required>
                <button type="button" class="toggle-password-btn absolute right-2 top-1/2 transform -translate-y-1/2 text-gray-500" aria-label="Toggle confirm password visibility" onclick="togglePassword('admin_password_confirmation', this)">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8S1 12 1 12z"/><circle cx="12" cy="12" r="3"/></svg>
                </button>
                <p id="passwordFeedback" class="text-xs mt-1 text-red-600"></p>
            </div>

            <div class="flex justify-between">
                <button type="button" class="bg-gray-400 text-white px-6 py-2 rounded hover:bg-gray-500 transition" onclick="prevStep(3)">Back</button>
                <button type="button" id="openPreviewBtn" class="bg-red-700 text-white px-6 py-2 rounded hover:bg-red-800" onclick="openPreview()">Create Organization</button>
            </div>
        </div>
    </form>

    {{-- Preview Modal Component --}}
    <x-preview-modal id="previewModal" title="Preview Organization" />
</div>

<script>
let currentStep = parseInt(document.getElementById('current_step').value);

function updateProgress() {
    ['dot-1','dot-2','dot-3','line-1','line-2'].forEach(id => document.getElementById(id).classList.remove('bg-blue-600','bg-gray-300'));
    document.getElementById('dot-1').classList.add(currentStep>=1?'bg-red-800':'bg-gray-300');
    document.getElementById('dot-2').classList.add(currentStep>=2?'bg-red-800':'bg-gray-300');
    document.getElementById('dot-3').classList.add(currentStep>=3?'bg-red-800':'bg-gray-300');
    document.getElementById('line-1').classList.add(currentStep>=2?'bg-red-800':'bg-gray-300');
    document.getElementById('line-2').classList.add(currentStep>=3?'bg-red-800':'bg-gray-300');
}

function showStep(step){
    document.querySelectorAll('.form-step').forEach(s=>s.classList.add('hidden'));
    document.getElementById(`step-${step}`).classList.remove('hidden');
    document.getElementById('current_step').value = step;
    currentStep = step;
    updateProgress();

    if(step===1){
        const role = document.getElementById('org-role').value;
        document.getElementById('college-select').classList.toggle('hidden', role!=='college_org');
    }
}

// Validation helpers and live uniqueness checks
let codeTimeout = null, emailTimeout = null;
let codeAvailable = null, emailAvailable = null;
let codePending = false, emailPending = false;
let passwordMismatch = false;

// expose for preview modal
window.codeAvailable = codeAvailable;
window.emailAvailable = emailAvailable;
window.codePending = codePending;
window.emailPending = emailPending;
window.passwordMismatch = passwordMismatch;

const csrfMeta = document.querySelector('meta[name="csrf-token"]');
const csrfToken = csrfMeta ? csrfMeta.getAttribute('content') : null;
if (!csrfToken) console.warn('CSRF token meta tag not found; AJAX requests may fail.');

const codeInput = document.getElementById('org_code_input');
const codeFeedback = document.getElementById('codeFeedback');
const emailInput = document.getElementById('admin_email_input');
const emailFeedback = document.getElementById('emailFeedback');
const openPreviewBtn = document.getElementById('openPreviewBtn');
const nextBtn1 = document.getElementById('nextStepBtn1');
const nextBtn2 = document.querySelector('#step-2 button[onclick^="nextStep"]');

const spinnerHTML = '<span class="inline-block w-4 h-4 border-2 border-gray-300 border-t-red-700 rounded-full animate-spin mr-2 align-middle"></span>';

function setFeedback(el, msg, colorClass) {
    if (!el) return;
    el.innerHTML = msg;
    el.className = `text-xs mt-1 ${colorClass}`;
}

function toggleCreateDisabled() {
    const shouldDisable = (codeAvailable === false || emailAvailable === false || codePending || emailPending || passwordMismatch);

    // Open Preview / Create button
    if (openPreviewBtn) {
        if (shouldDisable) {
            openPreviewBtn.disabled = true;
            openPreviewBtn.classList.add('opacity-60', 'cursor-not-allowed');
        } else {
            openPreviewBtn.disabled = false;
            openPreviewBtn.classList.remove('opacity-60', 'cursor-not-allowed');
        }
    }

    // Next button on step 1
    if (nextBtn1) {
        if (shouldDisable) {
            nextBtn1.disabled = true;
            nextBtn1.classList.add('opacity-60', 'cursor-not-allowed');
        } else {
            nextBtn1.disabled = false;
            nextBtn1.classList.remove('opacity-60', 'cursor-not-allowed');
        }
    }

    // Next button on step 2
    if (nextBtn2) {
        if (shouldDisable) {
            nextBtn2.disabled = true;
            nextBtn2.classList.add('opacity-60', 'cursor-not-allowed');
        } else {
            nextBtn2.disabled = false;
            nextBtn2.classList.remove('opacity-60', 'cursor-not-allowed');
        }
    }

    // Update window vars for preview component
    window.codeAvailable = codeAvailable;
    window.emailAvailable = emailAvailable;
    window.codePending = codePending;
    window.emailPending = emailPending;
    window.passwordMismatch = passwordMismatch;
}

// Password match
const pwInput = document.getElementById('admin_password');
const pwcInput = document.getElementById('admin_password_confirmation');
const pwFeedback = document.getElementById('passwordFeedback');

function checkPasswordMatch() {
    const pv = pwInput ? pwInput.value : '';
    const pvc = pwcInput ? pwcInput.value : '';
    if (!pv && !pvc) {
        passwordMismatch = false;
        if (pwFeedback) pwFeedback.textContent = '';
        toggleCreateDisabled();
        return;
    }
    if (pv !== pvc) {
        passwordMismatch = true;
        if (pwFeedback) pwFeedback.textContent = 'Passwords do not match';
    } else {
        passwordMismatch = false;
        if (pwFeedback) pwFeedback.textContent = '';
    }
    toggleCreateDisabled();
}

if (pwInput && pwcInput) {
    pwInput.addEventListener('input', checkPasswordMatch);
    pwcInput.addEventListener('input', checkPasswordMatch);
}

// Live org code check
if (codeInput) {
    codeInput.addEventListener('input', (e) => {
        clearTimeout(codeTimeout);
        const val = e.target.value.trim();
        codeAvailable = null;
        codePending = true;
        toggleCreateDisabled();
        if (!val) {
            setFeedback(codeFeedback, 'Required', 'text-red-600');
            codePending = false;
            toggleCreateDisabled();
            return;
        }
        setFeedback(codeFeedback, spinnerHTML + 'Checking…', 'text-gray-500');
        codeTimeout = setTimeout(() => checkOrgCode(val), 500);
    });
}

function checkOrgCode(code) {
    const normalized = (code || '').trim().toUpperCase();
    if (!normalized) {
        setFeedback(codeFeedback, 'Required', 'text-red-600');
        codeAvailable = false;
        codePending = false;
        toggleCreateDisabled();
        return Promise.resolve({ available: false });
    }

    setFeedback(codeFeedback, spinnerHTML + 'Checking…', 'text-gray-500');
    codePending = true;
    toggleCreateDisabled();

    return fetch("{{ route('osa.organizations.checkCode') }}", {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
        body: JSON.stringify({ org_code: normalized })
    })
    .then(res => res.json())
    .then(data => {
        if (data.available) {
            setFeedback(codeFeedback, 'Available', 'text-green-600');
            codeAvailable = true;
        } else {
            setFeedback(codeFeedback, 'Already taken', 'text-red-600');
            codeAvailable = false;
        }
        codePending = false;
        toggleCreateDisabled();
        return data;
    })
    .catch(() => {
        setFeedback(codeFeedback, 'Error checking', 'text-red-600');
        codeAvailable = false;
        codePending = false;
        toggleCreateDisabled();
        return { available: false };
    });
}

// Live email check
if (emailInput) {
    emailInput.addEventListener('input', (e) => {
        clearTimeout(emailTimeout);
        const val = e.target.value.trim();
        emailAvailable = null;
        if (!val) {
            setFeedback(emailFeedback, 'Required', 'text-red-600');
            toggleCreateDisabled();
            return;
        }
        emailPending = true;
        setFeedback(emailFeedback, spinnerHTML + 'Checking…', 'text-gray-500');
        emailTimeout = setTimeout(() => checkAdminEmail(val), 500);
    });
}

function checkAdminEmail(email) {
    return fetch("{{ route('osa.organizations.checkEmail') }}", {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
        body: JSON.stringify({ admin_email: email })
    })
    .then(res => res.json())
    .then(data => {
        if (data.available) {
            setFeedback(emailFeedback, 'Available', 'text-green-600');
            emailAvailable = true;
        } else {
            setFeedback(emailFeedback, 'Already taken', 'text-red-600');
            emailAvailable = false;
        }
        emailPending = false;
        toggleCreateDisabled();
        return data;
    })
    .catch(() => {
        setFeedback(emailFeedback, 'Error checking', 'text-red-600');
        emailPending = false;
        toggleCreateDisabled();
        return { available: false };
    });
}

// Validate steps (returns a Promise<boolean>)
function validateStep(step) {
    const stepEl = document.getElementById(`step-${step}`);
    if (!stepEl) return Promise.resolve(true);

    if (step === 1) {
        const role = document.getElementById('org-role').value;
        const collegeSelect = document.querySelector('[name="college_id"]');
        const roleFb = document.getElementById('roleFeedback');
        if (!role) { if (roleFb) roleFb.textContent = 'Please select organization type.'; document.getElementById('org-role').focus(); return Promise.resolve(false); }
        if (role === 'college_org' && (!collegeSelect || !collegeSelect.value)) { if (roleFb) roleFb.textContent = 'Please select a college.'; document.querySelector('[name="college_id"]')?.focus(); return Promise.resolve(false); }
        if (roleFb) roleFb.textContent = '';
        return Promise.resolve(true);
    }

    if (step === 2) {
        const requiredEls = stepEl.querySelectorAll('input[required], textarea[required], select[required]');
        for (const el of requiredEls) {
            // ignore file input (logo)
            if (el.type === 'file') continue;
            if (!el.checkValidity()) { el.reportValidity(); el.focus(); return Promise.resolve(false); }
        }

        const codeVal = codeInput ? codeInput.value.trim() : '';
        if (codeAvailable === false) { setFeedback(codeFeedback, 'Already taken', 'text-red-600'); codeInput && codeInput.focus(); return Promise.resolve(false); }
        if (codeAvailable === null) {
            return checkOrgCode(codeVal).then(res => {
                if (!res || !res.available) { codeInput && codeInput.focus(); return false; }
                return true;
            });
        }

        return Promise.resolve(true);
    }

    if (step === 3) {
        const requiredEls = stepEl.querySelectorAll('input[required], textarea[required], select[required]');
        for (const el of requiredEls) { if (!el.checkValidity()) { el.reportValidity(); el.focus(); return Promise.resolve(false); } }

        // password match
        if (passwordMismatch) { document.getElementById('admin_password_confirmation').focus(); return Promise.resolve(false); }

        const emailVal = emailInput ? emailInput.value.trim() : '';
        if (emailAvailable === false) { setFeedback(emailFeedback, 'Already taken', 'text-red-600'); emailInput && emailInput.focus(); return Promise.resolve(false); }
        if (emailAvailable === null) {
            return checkAdminEmail(emailVal).then(res => {
                if (!res || !res.available) { emailInput && emailInput.focus(); return false; }
                return true;
            });
        }

        return Promise.resolve(true);
    }

    return Promise.resolve(true);
}

function nextStep(step) {
    validateStep(step).then(ok => { if (ok) showStep(step + 1); });
}

function prevStep(step){ showStep(step-1); }

document.addEventListener('DOMContentLoaded', function(){
    showStep(currentStep);

    @if(old('college_id'))
        document.querySelector('[name="college_id"]').value = "{{ old('college_id') }}";
    @endif

    // initial toggle state
    const role = document.getElementById('org-role').value;
    document.getElementById('college-select').classList.toggle('hidden', role!=='college_org');

    toggleCreateDisabled();
});

// logo handlers (kept unchanged)
logoInput.addEventListener('change', function(e){
    const file = e.target.files[0];
    if(file){
        const reader = new FileReader();
        reader.onload = function(e){
            logoPreview.src = e.target.result;
            logoPreview.classList.remove('hidden');
            logoPlus.classList.add('hidden');
            removeLogo.classList.remove('hidden');
        }
        reader.readAsDataURL(file);
    }
});

removeLogo.addEventListener('click', function(){
    logoInput.value = '';
    logoPreview.src = '';
    logoPreview.classList.add('hidden');
    logoPlus.classList.remove('hidden');
    removeLogo.classList.add('hidden');
});

const roleSelect = document.getElementById('org-role');
const collegeSelectWrapper = document.getElementById('college-select');

roleSelect.addEventListener('change', function () {
    if (this.value === 'college_org') {
        collegeSelectWrapper.classList.remove('hidden');
    } else {
        collegeSelectWrapper.classList.add('hidden');
    }
    const roleFb = document.getElementById('roleFeedback');
    if (roleFb) roleFb.textContent = '';
    toggleCreateDisabled();
});

// Preview
function escapeHtml(unsafe) {
    if (!unsafe) return '';
    return unsafe.replace(/[&<>"'`]/g, function (m) { return ({
        '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;', '`': '&#96;'
    })[m]; });
}

function openPreview() {
    const form = document.getElementById('organizationForm');
    if (!form.checkValidity()) { form.reportValidity(); return; }

    // Ensure last-step validations
    validateStep(3).then(ok => {
        if (!ok) return;

        const name = form.querySelector('input[name="name"]').value.trim();
        const code = form.querySelector('input[name="org_code"]').value.trim();
        const role = form.querySelector('select[name="role"]').value;
        const collegeText = form.querySelector('select[name="college_id"]')? form.querySelector('select[name="college_id"] option:checked')?.textContent : '';
        const adminName = form.querySelector('input[name="admin_name"]').value.trim();
        const adminEmail = form.querySelector('input[name="admin_email"]').value.trim();
        const adminPassword = form.querySelector('input[name="admin_password"]').value || '';

        let logoHtml = '<p class="text-gray-500">No logo uploaded</p>';
        if (logoPreview && !logoPreview.classList.contains('hidden') && logoPreview.src) {
            logoHtml = `<img src="${logoPreview.src}" alt="Logo" class="w-32 h-32 object-cover rounded border" />`;
        }

        const maskedPassword = adminPassword ? '•'.repeat(Math.max(4, adminPassword.length)) + `  (length: ${adminPassword.length})` : '<span class="text-gray-500">Not set</span>';

        const html = `
            <div class="space-y-3">
                <div class="flex items-center gap-4">
                    <div class="flex-shrink-0">${logoHtml}</div>
                    <div>
                        <div><strong>Organization Name:</strong> ${escapeHtml(name)}</div>
                        <div>
                            <strong>Organization Code:</strong> ${escapeHtml(code)}
                            <div id="preview-code-error" class="text-xs mt-1 text-red-600"></div>
                        </div>
                        <div><strong>Type:</strong> ${escapeHtml(role === 'college_org' ? 'College-based' : 'University-wide')}</div>
                        ${role === 'college_org' ? `<div><strong>College:</strong> ${escapeHtml(collegeText || '')}</div>` : ''}
                    </div>
                </div>

                <div>
                    <h4 class="font-semibold">Admin Account</h4>
                    <div class="mt-1 text-sm">
                        <div><strong>Name:</strong> ${escapeHtml(adminName)}</div>
                        <div><strong>Email:</strong> ${escapeHtml(adminEmail)}</div>
                        <div id="preview-email-error" class="text-xs mt-1 text-red-600"></div>
                        <div><strong>Password:</strong> ${maskedPassword}</div>
                    </div>
                </div>
            </div>
        `;

        // ensure preview modal picks up latest validation state
        window.codeAvailable = codeAvailable;
        window.emailAvailable = emailAvailable;
        window.codePending = codePending;
        window.emailPending = emailPending;
        window.passwordMismatch = passwordMismatch;

        showPreviewModal('previewModal', html, 'organizationForm');
    });
}
</script>

<style>
.form-step { transition: all 0.3s ease; }
.toggle-password-btn {
    background: transparent;
    border: none;
    padding: 0;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
}
.toggle-password-btn svg {
    display: block;
}
</style>
@endsection
