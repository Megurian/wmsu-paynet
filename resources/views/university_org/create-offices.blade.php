@extends('layouts.dashboard')

@section('title', 'Offices')
@section('page-title', ($organization?->org_code ?? 'Organization') . ' Offices')

@section('content')
<div class="mb-8">
    <h2 class="text-3xl font-bold text-gray-800"> {{ ($organization?->org_code ?? 'Organization') . " Offices" }} </h2>
    <p class="text-sm text-gray-500 mt-1">
        Welcome, {{ Auth::user()->name }}. Here you can manage the Offices associated with different colleges within the university.
    </p>
    @if(Auth::user()?->organization && Auth::user()->organization->role === 'university_org')
        <p class="text-sm text-blue-600 mt-2">⚠️ <strong>Note:</strong> Your organization <em>{{ Auth::user()->organization->name }}</em> will be recorded as the <em>mother organization</em> for any office you create.</p>
    @endif
</div>
<div class="flex justify-center">
    <form id="org-form" method="POST" action="{{ route('university_org.offices.store') }}" enctype="multipart/form-data" class="w-full max-w-2xl bg-white p-8 rounded-lg shadow-lg" @if(Auth::user()?->organization && Auth::user()->organization->role === 'university_org') data-mother-name="{{ Auth::user()->organization->name }}" @endif>
        @csrf
        <input type="hidden" name="current_step" id="current_step" value="{{ old('current_step', 1) }}">

        <div class="flex items-center justify-center mb-8">
            <div class="flex items-center">
                <!-- Step 1 -->
                <div class="step-indicator flex items-center justify-center w-10 h-10 rounded-full bg-red-700 text-white">
                    1
                </div>
                <div class="w-16 h-1 bg-gray-200 mx-2"></div>
                
                <!-- Step 2 -->
                <div class="step-indicator flex items-center justify-center w-10 h-10 rounded-full bg-gray-200">
                    2
                </div>
                <div class="w-16 h-1 bg-gray-200 mx-2"></div>
                
                <!-- Step 3 -->
                <div class="step-indicator flex items-center justify-center w-10 h-10 rounded-full bg-gray-200">
                    3
                </div>
            </div>
        </div>

        <!-- Organization Type -->
        <div class="form-step" id="step-1">
            <h3 class="text-xl font-bold mb-6 text-center">Organization Type</h3>

            <!-- Offices are always under a college by default -->
            <input type="hidden" name="role" value="college_org">

            <div id="college-select" class="mb-4">
                <label class="block font-medium mb-1">Select College</label>
                <select name="college_code" class="shadow border-gray-300 rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="college">
                    <option value="" disabled {{ empty(old('college_code')) ? 'selected' : '' }}> Select College</option>
                    @forelse($colleges as $college)
                        <option value="{{ $college->college_code }}" data-name="{{ $college->name }}" {{ old('college_code') == $college->college_code ? 'selected' : '' }}>
                            {{ $college->name }}
                        </option>
                    @empty
                        <option value="" disabled>No colleges available</option>
                    @endforelse
                </select>
                <p id="collegeFeedback" class="text-xs mt-1 text-red-600"></p>
            </div>

            <div class="flex justify-end">
                <button type="button" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700 transition" onclick="nextStep()">Next</button>
            </div>
        </div>

        <!--  Organization Details -->
        <div class="form-step hidden" id="step-2">
            <h3 class="text-xl font-bold mb-6 text-center">Organization Details</h3>

            <div class="mb-4">
                <label class="block font-medium mb-1">Organization Name</label>
                <div class="flex space-x-2">
                    <input type="text" id="org-prefix" class="w-1/3 bg-gray-100 border border-gray-300 px-4 py-2 rounded focus:outline-none" readonly placeholder="College Name - " value="">
                    <input type="text" id="org-suffix" name="org_suffix" value="{{ old('org_suffix') }}" placeholder="Enter Organization Name" class="flex-1 border border-gray-300 px-4 py-2 rounded focus:outline-none focus:ring-2 focus:ring-blue-400" required>
                </div>
                <input type="hidden" name="name" id="org-full" value="{{ old('name') }}">
                <p class="text-xs text-gray-500 mt-1">The prefix is automatically set from the selected college and cannot be changed.</p>
            </div>

            <div class="mb-4">
                <label class="block font-medium mb-1">Organization Code</label>
                <input id="org_code_input" type="text" name="org_code" value="" placeholder="Enter Organization Code" class="w-full border border-gray-300 px-4 py-2 rounded focus:outline-none focus:ring-2 focus:ring-blue-400" required>
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
                <button type="button" class="bg-gray-400 text-white px-6 py-2 rounded hover:bg-gray-500 transition" onclick="prevStep()">Back</button>
                <button type="button" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700 transition" onclick="nextStep()">Next</button>
            </div>
        </div>

        <!--  Admin Account -->
        <div class="form-step hidden" id="step-3">
            <h3 class="text-xl font-bold mb-6 text-center">Create Admin Account</h3>

            <div class="mb-4">
                <label class="block font-medium mb-1">Admin Name</label>
                <input type="text" name="admin_name" value="" placeholder="Enter Admin Name" class="w-full border border-gray-300 px-4 py-2 rounded focus:outline-none focus:ring-2 focus:ring-blue-400" required>
            </div>

            <div class="mb-4">
                <label class="block font-medium mb-1">Admin Email</label>
                <input id="admin_email_input" type="email" name="admin_email" value="" placeholder="Enter Admin Email" class="w-full border border-gray-300 px-4 py-2 rounded focus:outline-none focus:ring-2 focus:ring-blue-400" required>
                <p id="emailFeedback" class="text-xs mt-1"></p>
            </div>

            <div class="mb-4">
                <label class="block font-medium mb-1">Password</label>
                <div class="relative">
                    <input id="admin_password" type="password" name="admin_password" placeholder="Enter Password" class="w-full border border-gray-300 px-4 py-2 pr-10 rounded focus:outline-none focus:ring-2 focus:ring-blue-400" required>
                    <button type="button" class="toggle-password-btn absolute right-2 top-1/2 transform -translate-y-1/2 text-gray-500" aria-label="Toggle password visibility" onclick="togglePassword('admin_password', this)">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8S1 12 1 12z"/><circle cx="12" cy="12" r="3"/></svg>
                    </button>
                </div>
            </div>

            <div class="mb-6">
                <label class="block font-medium mb-1">Confirm Password</label>
                <div class="relative">
                    <input id="admin_password_confirmation" type="password" name="admin_password_confirmation" placeholder="Confirm Password" class="w-full border border-gray-300 px-4 py-2 pr-10 rounded focus:outline-none focus:ring-2 focus:ring-blue-400" required>
                    <button type="button" class="toggle-password-btn absolute right-2 top-1/2 transform -translate-y-1/2 text-gray-500" aria-label="Toggle confirm password visibility" onclick="togglePassword('admin_password_confirmation', this)">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8S1 12 1 12z"/><circle cx="12" cy="12" r="3"/></svg>
                    </button>
                </div>
                <p id="passwordFeedback" class="text-xs mt-1 text-red-600"></p>
            </div>

            <div class="flex justify-between">
                <button type="button" class="bg-gray-400 text-white px-6 py-2 rounded hover:bg-gray-500 transition" onclick="prevStep()">Back</button>
                <button type="button" id="openPreviewBtn" class="bg-red-700 text-white px-6 py-2 rounded hover:bg-red-800" onclick="openPreview()">Create Organization</button>
            </div>
        </div>
    </form>
</div>

<x-preview-modal id="previewModal" title="Preview Organization" />

<script>
let currentStep = 1;

function updateProgress() {
    document.querySelectorAll('.step-indicator').forEach((step, index) => {
        const stepNumber = index + 1;
        if (stepNumber < currentStep) {
            step.classList.remove('bg-gray-200', 'bg-red-700');
            step.classList.add('bg-green-500', 'text-white');
        } else if (stepNumber === currentStep) {
            step.classList.remove('bg-gray-200', 'bg-green-500');
            step.classList.add('bg-red-700', 'text-white');
        } else {
            step.classList.remove('bg-red-700', 'bg-green-500', 'text-white');
            step.classList.add('bg-gray-200');
        }
    });
}

function setupOrgPrefix() {
    const collegeSelect = document.getElementById('college');
    const prefixInput = document.getElementById('org-prefix');
    const suffixInput = document.getElementById('org-suffix');
    const fullInput = document.getElementById('org-full');

    if (!prefixInput || !suffixInput || !fullInput) return;

    const selectedOption = collegeSelect && collegeSelect.selectedOptions[0];
    const collegeName = selectedOption ? (selectedOption.dataset.name || selectedOption.text) : '';

    const prefix = collegeName ? `${collegeName} - ` : '';
    prefixInput.value = prefix;

    // If full input already has a value and suffix is empty, split it
    if (fullInput.value && !suffixInput.value) {
        if (prefix && fullInput.value.startsWith(prefix)) {
            suffixInput.value = fullInput.value.slice(prefix.length);
        } else {
            suffixInput.value = fullInput.value;
            fullInput.value = prefix + suffixInput.value;
        }
    }

    // Ensure full value is synced now
    fullInput.value = prefix + suffixInput.value;
}

function showStep(step) {
    document.querySelectorAll('[id^="step-"]').forEach(step => {
        step.classList.add('hidden');
    });
    
    document.getElementById(`step-${step}`)?.classList.remove('hidden');
    currentStep = step;
    updateProgress();

    if (step === 2) {
        setupOrgPrefix();
    }
}

function nextStep() {
    if (currentStep < 3) {
        Promise.resolve(validateStep(currentStep)).then(ok => {
            if (!ok) return;
            currentStep++;
            showStep(currentStep);
        });
    } else {
        // Final step: validate then open preview modal
        Promise.resolve(validateStep(3)).then(ok => {
            if (!ok) return;
            openPreview();
        });
    }
}

function prevStep() { 
    if (currentStep > 1) {
        showStep(currentStep - 1);
    }
}

// --- Validation and uniqueness checks (state) ---
let codeTimeout = null, emailTimeout = null;
let codeAvailable = null, emailAvailable = null;
let codePending = false, emailPending = false;
let passwordMismatch = false;

const csrfMeta = document.querySelector('meta[name="csrf-token"]');
const csrfToken = csrfMeta ? csrfMeta.getAttribute('content') : null;
const spinnerHTML = '<span class="inline-block w-4 h-4 border-2 border-gray-300 border-t-red-700 rounded-full animate-spin mr-2 align-middle"></span>';

function setFeedback(el, msg, colorClass) {
    if (!el) return;
    el.innerHTML = msg;
    el.className = `text-xs mt-1 ${colorClass}`;
}

function toggleCreateDisabled() {
    const shouldDisable = (codeAvailable === false || emailAvailable === false || codePending || emailPending || passwordMismatch);
    const openPreviewBtn = document.getElementById('openPreviewBtn');
    const nextBtnStep1 = document.querySelector('#step-1 button[type="button"]');

    if (openPreviewBtn) {
        if (shouldDisable) {
            openPreviewBtn.disabled = true;
            openPreviewBtn.classList.add('opacity-60', 'cursor-not-allowed');
        } else {
            openPreviewBtn.disabled = false;
            openPreviewBtn.classList.remove('opacity-60', 'cursor-not-allowed');
        }
    }

    if (nextBtnStep1) {
        if (codePending || emailPending || passwordMismatch) {
            nextBtnStep1.disabled = true;
            nextBtnStep1.classList.add('opacity-60', 'cursor-not-allowed');
        } else {
            nextBtnStep1.disabled = false;
            nextBtnStep1.classList.remove('opacity-60', 'cursor-not-allowed');
        }
    }
}

function checkOrgCode(code) {
    const normalized = (code || '').trim().toUpperCase();
    const codeFeedback = document.getElementById('codeFeedback');
    if (!normalized) {
        setFeedback(codeFeedback, 'Required', 'text-red-600');
        codeAvailable = false;
        toggleCreateDisabled();
        return Promise.resolve({ available: false });
    }

    setFeedback(codeFeedback, spinnerHTML + 'Checking…', 'text-gray-500');
    codePending = true;
    toggleCreateDisabled();

    return fetch("{{ route('university_org.organizations.checkCode') }}", {
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

function checkAdminEmail(email) {
    const emailFeedback = document.getElementById('emailFeedback');
    return fetch("{{ route('university_org.organizations.checkEmail') }}", {
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

function validateStep(step) {
    const codeFeedback = document.getElementById('codeFeedback');
    const emailFeedback = document.getElementById('emailFeedback');
    const pwFeedback = document.getElementById('passwordFeedback');

    if (step === 1) {
        const college = document.getElementById('college')?.value || '';
        const collegeFb = document.getElementById('collegeFeedback');
        if (!college) { setFeedback(collegeFb, 'Please select a college', 'text-red-600'); document.getElementById('college')?.focus(); return false; }
        setFeedback(collegeFb, '', '');
        return true;
    }

    if (step === 2) {
        const suffix = document.getElementById('org-suffix')?.value.trim() || '';
        const codeVal = document.getElementById('org_code_input')?.value.trim() || '';
        if (!suffix) {
            document.getElementById('org-suffix')?.focus();
            return false;
        }
        if (!codeVal) {
            document.getElementById('org_code_input')?.focus();
            setFeedback(codeFeedback, 'Required', 'text-red-600');
            return false;
        }

        if (codeAvailable === false) {
            setFeedback(codeFeedback, 'Already taken', 'text-red-600');
            document.getElementById('org_code_input')?.focus();
            return false;
        }

        if (codeAvailable === null || codeAvailable === undefined) {
            return checkOrgCode(codeVal).then(res => {
                return res && res.available;
            });
        }

        return true;
    }

    if (step === 3) {
        const adminName = document.querySelector('input[name="admin_name"]')?.value.trim() || '';
        const adminEmail = document.getElementById('admin_email_input')?.value.trim() || '';
        const pw = document.getElementById('admin_password')?.value || '';
        const pwc = document.getElementById('admin_password_confirmation')?.value || '';

        if (!adminName) { document.querySelector('input[name="admin_name"]')?.focus(); return false; }
        if (!adminEmail) { document.getElementById('admin_email_input')?.focus(); setFeedback(emailFeedback, 'Required', 'text-red-600'); return false; }
        if (!pw || !pwc) { document.getElementById('admin_password')?.focus(); return false; }
        if (pw !== pwc) { setFeedback(pwFeedback, 'Passwords do not match', 'text-red-600'); return false; }

        if (emailAvailable === false) { setFeedback(emailFeedback, 'Already taken', 'text-red-600'); document.getElementById('admin_email_input')?.focus(); return false; }

        if (emailAvailable === null || emailAvailable === undefined) {
            return checkAdminEmail(adminEmail).then(res => {
                return res && res.available;
            });
        }

        return true;
    }

    return true;
}

function openPreview() {
    const form = document.getElementById('org-form');
    if (!form) return;
    if (!validateStep(3)) return;

    const prefix = document.getElementById('org-prefix')?.value || '';
    const suffix = document.getElementById('org-suffix')?.value || '';
    const fullName = prefix + suffix;
    const orgCode = document.getElementById('org_code_input')?.value || '';
    const adminName = document.querySelector('input[name="admin_name"]')?.value || '';
    const adminEmail = document.getElementById('admin_email_input')?.value || '';
    const adminPassword = document.getElementById('admin_password')?.value || '';

    // If the current user is a university org, the form carries the mother org name as a data attribute
    const motherName = (form && form.dataset && form.dataset.motherName) ? form.dataset.motherName : '';

    const logoPreviewEl = document.getElementById('logoPreview');
    let logoHtml = '<p class="text-gray-500">No logo uploaded</p>';
    if (logoPreviewEl && !logoPreviewEl.classList.contains('hidden') && logoPreviewEl.src) {
        logoHtml = `<img src="${logoPreviewEl.src}" alt="Logo" class="w-32 h-32 object-cover rounded border" />`;
    }

    const maskedPassword = adminPassword ? '•'.repeat(Math.max(4, adminPassword.length)) + `  (length: ${adminPassword.length})` : '<span class="text-gray-500">Not set</span>';

    const html = `
        <div class="space-y-3">
            <div class="flex items-center gap-4">
                <div class="flex-shrink-0">${logoHtml}</div>
                <div>
                    <div><strong>Organization Name:</strong> ${escapeHtml(fullName)}</div>
                    <div><strong>Organization Code:</strong> ${escapeHtml(orgCode)}</div>
                    <div><strong>Mother Organization:</strong> ${motherName ? escapeHtml(motherName) : '<span class="text-gray-500">None</span>'}</div>
                </div>
            </div>

            <div>
                <h4 class="font-semibold">Admin Account</h4>
                <div class="mt-1 text-sm">
                    <div><strong>Name:</strong> ${escapeHtml(adminName)}</div>
                    <div><strong>Email:</strong> ${escapeHtml(adminEmail)}</div>
                    <div><strong>Password:</strong> ${maskedPassword}</div>
                </div>
            </div>
        </div>
    `;

    showPreviewModal('previewModal', html, 'org-form');
}

document.addEventListener('DOMContentLoaded', function() {
    showStep(1);
    
    document.querySelectorAll('[onclick^="nextStep"]').forEach(btn => {
        btn.onclick = nextStep;
        btn.type = 'button';
    });
    
    document.querySelectorAll('[onclick^="prevStep"]').forEach(btn => {
        btn.onclick = prevStep;
        btn.type = 'button';
    });

    // Sync suffix input into the hidden full name input
    const suffixInput = document.getElementById('org-suffix');
    const fullInput = document.getElementById('org-full');
    if (suffixInput && fullInput) {
        suffixInput.addEventListener('input', function() {
            const prefix = document.getElementById('org-prefix')?.value || '';
            fullInput.value = prefix + suffixInput.value;
        });
    }

    // Update prefix if college selection changes
    const collegeSelect = document.getElementById('college');
    if (collegeSelect) {
        collegeSelect.addEventListener('change', function() {
            setupOrgPrefix();
            // Clear validation message when user selects a college
            const collegeFb = document.getElementById('collegeFeedback');
            if (collegeFb) setFeedback(collegeFb, '', '');
        });
    }

    // Logo preview and remove button
    const logoInput = document.getElementById('logoInput');
    const logoPreview = document.getElementById('logoPreview');
    const logoPlus = document.getElementById('logoPlus');
    const removeLogo = document.getElementById('removeLogo');
    let logoObjectUrl = null;

    if (logoInput) {
        logoInput.addEventListener('change', function() {
            const file = this.files && this.files[0];
            if (!file) return;
            if (!file.type.startsWith('image/')) return;

            // Revoke previous URL if present
            if (logoObjectUrl) { URL.revokeObjectURL(logoObjectUrl); logoObjectUrl = null; }
            logoObjectUrl = URL.createObjectURL(file);

            if (logoPreview) {
                logoPreview.src = logoObjectUrl;
                logoPreview.classList.remove('hidden');
            }
            if (logoPlus) {
                logoPlus.classList.add('hidden');
            }
            if (removeLogo) {
                removeLogo.classList.remove('hidden');
            }
        });
    }

    if (removeLogo) {
        removeLogo.addEventListener('click', function(e) {
            e.preventDefault();
            // Clear preview and input
            if (logoObjectUrl) { URL.revokeObjectURL(logoObjectUrl); logoObjectUrl = null; }
            if (logoPreview) {
                logoPreview.src = '';
                logoPreview.classList.add('hidden');
            }
            if (logoPlus) {
                logoPlus.classList.remove('hidden');
            }
            if (logoInput) {
                logoInput.value = '';
            }
            removeLogo.classList.add('hidden');
        });
    }

    // --- Validation and uniqueness checks (event listeners) ---
    const codeInput = document.getElementById('org_code_input');
    const emailInput = document.getElementById('admin_email_input');
    if (!csrfToken) console.warn('CSRF token meta tag not found; AJAX may fail.');

    if (codeInput) {
        codeInput.addEventListener('input', (e) => {
            clearTimeout(codeTimeout);
            const val = (e.target.value || '').trim();
            codeAvailable = null;
            codePending = true;
            toggleCreateDisabled();
            if (!val) {
                const codeFeedback = document.getElementById('codeFeedback');
                setFeedback(codeFeedback, 'Required', 'text-red-600');
                codePending = false;
                toggleCreateDisabled();
                return;
            }
            const codeFeedback = document.getElementById('codeFeedback');
            setFeedback(codeFeedback, spinnerHTML + 'Checking…', 'text-gray-500');
            codeTimeout = setTimeout(() => checkOrgCode(val), 500);
        });
    }

    if (emailInput) {
        emailInput.addEventListener('input', (e) => {
            clearTimeout(emailTimeout);
            const val = (e.target.value || '').trim();
            emailAvailable = null;
            emailPending = true;
            toggleCreateDisabled();
            if (!val) {
                const emailFeedback = document.getElementById('emailFeedback');
                setFeedback(emailFeedback, 'Required', 'text-red-600');
                emailPending = false;
                toggleCreateDisabled();
                return;
            }
            const emailFeedback = document.getElementById('emailFeedback');
            setFeedback(emailFeedback, spinnerHTML + 'Checking…', 'text-gray-500');
            emailTimeout = setTimeout(() => checkAdminEmail(val), 500);
        });
    }

    // Password match
    const pwInput = document.getElementById('admin_password');
    const pwcInput = document.getElementById('admin_password_confirmation');

    function checkPasswordMatch() {
        const pv = pwInput ? pwInput.value : '';
        const pvc = pwcInput ? pwcInput.value : '';
        const pwFeedback = document.getElementById('passwordFeedback');
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
});

function togglePassword(inputId, btn) {
    const input = document.getElementById(inputId);
    if (!input) return;
    const isPassword = input.type === 'password';
    input.type = isPassword ? 'text' : 'password';
    // swap icon
    btn.innerHTML = isPassword ? getEyeOffIcon() : getEyeIcon();
    btn.setAttribute('aria-pressed', isPassword ? 'true' : 'false');
}

function getEyeIcon() {
    return `<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8S1 12 1 12z"/><circle cx="12" cy="12" r="3"/></svg>`;
}

function getEyeOffIcon() {
    return `<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 2l20 20"/><path d="M17.94 17.94A10.94 10.94 0 0 1 12 20c-7 0-11-8-11-8a21.45 21.45 0 0 1 5.06-7.06"/></svg>`;
}

function escapeHtml(unsafe) {
    if (!unsafe) return '';
    return unsafe.replace(/[&<>"'`]/g, function (m) {
        return ({
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#39;',
            '`': '&#96;'
        })[m];
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
.toggle-password-btn svg { display: block; }
</style>
@endsection
