@extends('layouts.dashboard')

@section('title', 'New College')
@section('page-title', 'Create New College')

@section('content')
<div class="flex justify-center">
    <form id="collegeForm" action="{{ route('osa.college.store') }}" method="POST" enctype="multipart/form-data" class="w-full max-w-2xl bg-white p-8 rounded-lg shadow-lg">
        @csrf

        <div class="flex items-center justify-center mb-8">
            <div class="flex items-center">
                <div id="dot-1" class="w-6 h-6 rounded-full bg-red-800 flex items-center justify-center text-white font-bold">1</div>
                <div id="line-1" class="w-24 h-1 bg-gray-300"></div>
            </div>

            <div class="flex items-center">
                <div id="dot-2" class="w-6 h-6 rounded-full bg-gray-300 flex items-center justify-center text-white font-bold">2</div>
                <div id="line-2" class="w-24 h-1 bg-gray-300"></div>
            </div>

            <div class="flex items-center">
                <div id="dot-3" class="w-6 h-6 rounded-full bg-gray-300 flex items-center justify-center text-white font-bold">3</div>
            </div>
        </div>

        <div class="form-step" id="step-1">
            <h3 class="text-xl font-bold mb-6 text-center">College Information</h3>

            <div class="mb-4">
                <label class="block font-medium mb-1">College Name</label>
                <input type="text" name="name" class="w-full border border-gray-300 px-4 py-2 rounded focus:outline-none focus:ring-2 focus:ring-blue-400" placeholder="Enter College Name" required>
            </div>

            <div class="mb-4">
                <label class="block font-medium mb-1">College Code</label>
                <input id="college_code_input" type="text" name="college_code" class="w-full border border-gray-300 px-4 py-2 rounded focus:outline-none focus:ring-2 focus:ring-blue-400" placeholder="Enter College Code (e.g., CCS001)" required>
                <p class="text-xs text-gray-500 mt-1">Unique code for the college</p>
                <p id="codeFeedback" class="text-xs mt-1"></p>
            </div>

            <div class="mb-6 relative">
                <label class="block font-medium mb-2">College Logo (Optional)</label>

                <div id="logoUpload" class="w-40 h-40 border-2 border-dashed border-gray-300 rounded-lg flex items-center justify-center cursor-pointer relative mx-auto">
                    <button type="button" id="removeLogo" class="hidden absolute -top-7 -right-3 right-0 bg-red-600 text-white rounded-full w-6 h-6 flex items-center justify-center text-xs hover:bg-red-700">×</button>
                    <span id="logoPlus" class="text-gray-400 text-4xl font-bold">+</span>
                    <img id="logoPreview" class="hidden w-full h-full object-cover rounded-lg absolute top-0 left-0" alt="Logo Preview">
                    <input type="file" name="logo" id="logoInput" accept="image/*" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer">
                </div>
                <p class="text-xs text-gray-500 mt-1 text-center">Click to upload college logo</p>
            </div>

            <div class="flex justify-end">
                <button id="nextStepBtn1" type="button" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700 transition" onclick="nextStep()">Next</button>
            </div>
        </div>

        <div class="form-step hidden" id="step-2">
            <h3 class="text-xl font-bold mb-6 text-center">
                Pre-Configure College Data
            </h3>

            {{-- Courses --}}
           <div class="mb-6">
                <label class="font-medium block mb-2">Courses</label>

                <div class="flex gap-2 mb-3">
                    <input id="courseInput" type="text"
                        class="flex-1 border rounded px-3 py-2 text-sm"
                        placeholder="e.g. BS Computer Science">
                    <button type="button"
                            onclick="addItem('course')"
                            class="bg-blue-600 text-white px-4 py-2 rounded">
                        Add
                    </button>
                </div>

                <ul id="courseList" class="space-y-2 text-sm"></ul>
                <p id="courseFeedback" class="text-xs mt-1 text-red-600"></p>
            </div>


            {{-- Year Levels --}}
            <div class="mb-6">
                <div class="flex justify-between items-center mb-2">
                    <label class="font-medium">Year Levels</label>
                    <button type="button"
                            onclick="addDefaultYears()"
                            class="text-xs text-blue-600 hover:underline">
                        Use Default (1st–4th Year)
                    </button>
                </div>

                <div class="flex gap-2 mb-3">
                    <input id="yearInput" type="text"
                        class="flex-1 border rounded px-3 py-2 text-sm"
                        placeholder="e.g. 1st Year">
                    <button type="button"
                            onclick="addItem('year')"
                            class="bg-blue-600 text-white px-4 py-2 rounded">
                        Add
                    </button>
                </div>

                <ul id="yearList" class="space-y-2 text-sm"></ul>
                <p id="yearFeedback" class="text-xs mt-1 text-red-600"></p>
            </div>

            {{-- Sections --}}
           <div class="mb-6">
                <div class="flex justify-between items-center mb-2">
                    <label class="font-medium">Sections</label>
                    <button type="button"
                            onclick="addDefaultSections()"
                            class="text-xs text-blue-600 hover:underline">
                        Use Default (A, B, C)
                    </button>
                </div>

                <div class="flex gap-2 mb-3">
                    <input id="sectionInput" type="text"
                        class="flex-1 border rounded px-3 py-2 text-sm"
                        placeholder="e.g. A">
                    <button type="button"
                            onclick="addItem('section')"
                            class="bg-blue-600 text-white px-4 py-2 rounded">
                        Add
                    </button>
                </div>

                <ul id="sectionList" class="space-y-2 text-sm"></ul>
                <p id="sectionFeedback" class="text-xs mt-1 text-red-600"></p>
            </div>

            <div class="flex justify-between">
                <button type="button"
                    class="bg-gray-400 text-white px-6 py-2 rounded"
                    onclick="prevStep()">
                    Back
                </button>

                <button type="button"
                    class="bg-blue-600 text-white px-6 py-2 rounded"
                    onclick="nextStep()">
                    Next
                </button>
            </div>
        </div>

        <div class="form-step hidden" id="step-3">
            <h3 class="text-xl font-bold mb-6 text-center">College Dean Account</h3>

            <div class="mb-4">
                <label class="block font-medium mb-1">Admin Name</label>
                <input type="text" name="admin_name" class="w-full border border-gray-300 px-4 py-2 rounded focus:outline-none focus:ring-2 focus:ring-blue-400" placeholder="Enter Admin Name" required>
            </div>

            <div class="mb-4">
                <label class="block font-medium mb-1">Admin Email</label>
                <input id="admin_email_input" type="email" name="admin_email" class="w-full border border-gray-300 px-4 py-2 rounded focus:outline-none focus:ring-2 focus:ring-blue-400" placeholder="Enter Admin Email" required>
                <p id="emailFeedback" class="text-xs mt-1"></p>
            </div>

            <div class="mb-4">
                <label class="block font-medium mb-1">Admin Password</label>
                <div class="relative">
                    <input id="admin_password" type="password" name="admin_password" class="w-full border border-gray-300 px-4 py-2 pr-10 rounded focus:outline-none focus:ring-2 focus:ring-blue-400" placeholder="Enter Password" required>
                    <button type="button" class="toggle-password-btn absolute right-2 top-1/2 transform -translate-y-1/2 text-gray-500" aria-label="Toggle password visibility" onclick="togglePassword('admin_password', this)">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8S1 12 1 12z"/><circle cx="12" cy="12" r="3"/></svg>
                    </button>
                </div>
            </div>

            <div class="mb-6">
                <label class="block font-medium mb-1">Confirm Password</label>
                <div class="relative">
                    <input id="admin_password_confirmation" type="password" name="admin_password_confirmation" class="w-full border border-gray-300 px-4 py-2 pr-10 rounded focus:outline-none focus:ring-2 focus:ring-blue-400" placeholder="Confirm Password" required>
                    <button type="button" class="toggle-password-btn absolute right-2 top-1/2 transform -translate-y-1/2 text-gray-500" aria-label="Toggle confirm password visibility" onclick="togglePassword('admin_password_confirmation', this)">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8S1 12 1 12z"/><circle cx="12" cy="12" r="3"/></svg>
                    </button>
                    <p id="passwordFeedback" class="text-xs mt-1 text-red-600"></p>
                </div>
            </div>

            <div class="flex justify-between">
                <button type="button" class="bg-gray-400 text-white px-6 py-2 rounded hover:bg-gray-500 transition" onclick="prevStep()">Back</button>
                <button type="button" id="openPreviewBtn" class="bg-red-700 text-white px-6 py-2 rounded hover:bg-red-800 transition" onclick="openPreview()">Create College</button>
            </div>
        </div>
    </form>

    {{-- Preview Modal Component --}}
    <x-preview-modal id="previewModal" title="Preview College" />
</div>

<script>
    let currentStep = 1;
    const totalSteps = 3;

    function showStep(step) {
        // Hide all steps
        document.querySelectorAll('.form-step').forEach(el => {
            el.classList.add('hidden');
        });

        // Show active step
        document.getElementById(`step-${step}`).classList.remove('hidden');

        updateProgress();
    }

    function updateProgress() {
        for (let i = 1; i <= totalSteps; i++) {
            const dot = document.getElementById(`dot-${i}`);
            if (!dot) continue;

            if (i <= currentStep) {
                dot.classList.remove('bg-gray-300');
                dot.classList.add('bg-red-800');
            } else {
                dot.classList.remove('bg-red-800');
                dot.classList.add('bg-gray-300');
            }

            // Handle connecting lines
            if (i < totalSteps) {
                const line = document.getElementById(`line-${i}`);
                if (!line) continue;

                if (i < currentStep) {
                    line.classList.remove('bg-gray-300');
                    line.classList.add('bg-red-800');
                } else {
                    line.classList.remove('bg-red-800');
                    line.classList.add('bg-gray-300');
                }
            }
        }
    }

    function nextStep() {
        if (currentStep < totalSteps) {
            // validate current step before moving forward (supports async checks)
            Promise.resolve(validateStep(currentStep)).then(ok => {
                if (!ok) return;
                currentStep++;
                showStep(currentStep);
            });
        }
    }

    function prevStep() {
        if (currentStep > 1) {
            currentStep--;
            showStep(currentStep);
        }
    }

    // Init
    showStep(currentStep);

    const logoInput = document.getElementById('logoInput');
    const logoPreview = document.getElementById('logoPreview');
    const logoPlus = document.getElementById('logoPlus');
    const removeLogo = document.getElementById('removeLogo');

    logoInput.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                logoPreview.src = e.target.result;
                logoPreview.classList.remove('hidden');
                logoPlus.classList.add('hidden');
                removeLogo.classList.remove('hidden');
            }
            reader.readAsDataURL(file);
        }
    });

    removeLogo.addEventListener('click', function() {
        logoInput.value = '';
        logoPreview.src = '';
        logoPreview.classList.add('hidden');
        logoPlus.classList.remove('hidden');
        removeLogo.classList.add('hidden');
    });

    const dataStore = {
    course: [],
    year: [],
    section: []
};
// Expose for components (preview) that read from window
window.dataStore = dataStore; 

function addItem(type, value = null) {
    const inputMap = {
        course: 'courseInput',
        year: 'yearInput',
        section: 'sectionInput'
    };

    const listMap = {
        course: 'courseList',
        year: 'yearList',
        section: 'sectionList'
    };

    const input = value ?? document.getElementById(inputMap[type]).value.trim();
    if (!input || dataStore[type].includes(input)) return;

    dataStore[type].push(input);

    const li = document.createElement('li');
    li.className = 'flex justify-between items-center border rounded px-3 py-1';
    li.innerHTML = `
        <span>${input}</span>
        <button type="button"
                onclick="removeItem('${type}', '${input}', this)"
                class="text-red-600 text-xs">
            Remove
        </button>
        <input type="hidden" name="${type}s[]" value="${input}">
    `;

    document.getElementById(listMap[type]).appendChild(li);
        // clear any previous validation message for this list
        const fb = document.getElementById(`${type}Feedback`);
        if (fb) fb.textContent = '';
        if (!value) document.getElementById(inputMap[type]).value = '';
    }

    function removeItem(type, value, btn) {
        dataStore[type] = dataStore[type].filter(v => v !== value);
        btn.parentElement.remove();
    }

    function addDefaultYears() {
        ['1st Year', '2nd Year', '3rd Year', '4th Year']
            .forEach(y => addItem('year', y));
    }

    function addDefaultSections() {
        ['A', 'B', 'C']
            .forEach(s => addItem('section', s));
    }

    // Password visibility toggle
    function togglePassword(inputId, btn) {
        const input = document.getElementById(inputId);
        if (!input) return;
        const isPassword = input.type === 'password';
        input.type = isPassword ? 'text' : 'password';
        // swap icon
        btn.innerHTML = isPassword ? getEyeOffIcon() : getEyeIcon();
        btn.setAttribute('aria-pressed', isPassword ? 'true' : 'false');
    }

    // Validate inputs per step. All fields are required except logo.
    function setListFeedback(type, msg) {
        const el = document.getElementById(`${type}Feedback`);
        if (el) {
            el.textContent = msg;
            el.className = 'text-xs mt-1 text-red-600';
        }
    }

    function validateStep(step) {
        const stepEl = document.getElementById(`step-${step}`);
        if (!stepEl) return true;

        // Step 1: ensure required text inputs are filled
        if (step === 1) {
            const requiredEls = stepEl.querySelectorAll('input[required], textarea[required], select[required]');
            for (const el of requiredEls) {
                // ignore file input (logo) - it's optional
                if (el.type === 'file') continue;
                if (!el.checkValidity()) {
                    el.reportValidity();
                    el.focus();
                    return false;
                }
            }

            // Ensure college code is available; if we haven't checked yet, perform check now.
            const codeEl = document.getElementById('college_code_input');
            const codeVal = codeEl ? codeEl.value.trim() : '';
            if (codeAvailable === false) {
                setFeedback(codeFeedback, 'Already taken', 'text-red-600');
                codeEl && codeEl.focus();
                return false;
            }

            if (codeAvailable === null || codeAvailable === undefined) {
                // perform synchronous check and wait for it
                return checkCollegeCode(codeVal).then(res => {
                    if (!res || !res.available) {
                        codeEl && codeEl.focus();
                        return false;
                    }
                    return true;
                });
            }

            return true;
        }

        // Step 2: require at least one item in each list
        if (step === 2) {
            let ok = true;
            if (!dataStore.course.length) { setListFeedback('course', 'Please add at least one course'); ok = false; }
            if (!dataStore.year.length) { setListFeedback('year', 'Please add at least one year level'); ok = false; }
            if (!dataStore.section.length) { setListFeedback('section', 'Please add at least one section'); ok = false; }
            return ok;
        }

        // Step 3: ensure admin info present and passwords match
        if (step === 3) {
            const requiredEls = stepEl.querySelectorAll('input[required], textarea[required], select[required]');
            for (const el of requiredEls) {
                if (!el.checkValidity()) {
                    el.reportValidity();
                    el.focus();
                    return false;
                }
            }

            const pw = document.getElementById('admin_password').value;
            const pwc = document.getElementById('admin_password_confirmation').value;
            const pwFeedback = document.getElementById('passwordFeedback');
            if (pw !== pwc) {
                if (pwFeedback) pwFeedback.textContent = 'Passwords do not match';
                document.getElementById('admin_password_confirmation').focus();
                return false;
            } else {
                if (pwFeedback) pwFeedback.textContent = '';
            }

            return true;
        }

        return true;
    }

    function getEyeIcon() {
        return `<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8S1 12 1 12z"/><circle cx="12" cy="12" r="3"/></svg>`;
    }

    function getEyeOffIcon() {
        return `<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 2l20 20"/><path d="M17.94 17.94A10.94 10.94 0 0 1 12 20c-7 0-11-8-11-8a21.45 21.45 0 0 1 5.06-7.06"/></svg>`;
    }

    function openPreview() {
        const form = document.getElementById('collegeForm');
        // Validate first
        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }

        // Ensure step 3 validations (password match, etc.) before preview
        if (!validateStep(3)) return;

        const name = form.querySelector('input[name="name"]').value.trim();
        const code = form.querySelector('input[name="college_code"]').value.trim();
        const adminName = form.querySelector('input[name="admin_name"]').value.trim();
        const adminEmail = form.querySelector('input[name="admin_email"]').value.trim();
        const adminPassword = form.querySelector('input[name="admin_password"]').value || '';

        const logoPreviewEl = document.getElementById('logoPreview');
        let logoHtml = '<p class="text-gray-500">No logo uploaded</p>';
        if (logoPreviewEl && !logoPreviewEl.classList.contains('hidden') && logoPreviewEl.src) {
            logoHtml = `<img src="${logoPreviewEl.src}" alt="Logo" class="w-32 h-32 object-cover rounded border" />`;
        }

        const courses = (window.dataStore && window.dataStore.course) ? window.dataStore.course : (typeof dataStore !== 'undefined' && dataStore.course ? dataStore.course : []);
        const years = (window.dataStore && window.dataStore.year) ? window.dataStore.year : (typeof dataStore !== 'undefined' && dataStore.year ? dataStore.year : []);
        const sections = (window.dataStore && window.dataStore.section) ? window.dataStore.section : (typeof dataStore !== 'undefined' && dataStore.section ? dataStore.section : []);

        // Mask password for security
        const maskedPassword = adminPassword ? '•'.repeat(Math.max(4, adminPassword.length)) + `  (length: ${adminPassword.length})` : '<span class="text-gray-500">Not set</span>';

        const html = `
            <div class="space-y-3">
                <div class="flex items-center gap-4">
                    <div class="flex-shrink-0">${logoHtml}</div>
                    <div>
                        <div><strong>College Name:</strong> ${escapeHtml(name)}</div>
                        <div>
                            <strong>College Code:</strong> ${escapeHtml(code)}
                            <div id="preview-code-error" class="text-xs mt-1 text-red-600"></div>
                        </div>
                    </div>
                </div>

                <div>
                    <h4 class="font-semibold">Pre-configured data</h4>
                    <div class="grid grid-cols-3 gap-4 mt-2">
                        <div>
                            <strong>Courses</strong>
                            <ul class="list-disc list-inside text-sm mt-1">${courses.length ? courses.map(c => `<li>${escapeHtml(c)}</li>`).join('') : '<li class="text-gray-500">None</li>'}</ul>
                        </div>
                        <div>
                            <strong>Year Levels</strong>
                            <ul class="list-disc list-inside text-sm mt-1">${years.length ? years.map(y => `<li>${escapeHtml(y)}</li>`).join('') : '<li class="text-gray-500">None</li>'}</ul>
                        </div>
                        <div>
                            <strong>Sections</strong>
                            <ul class="list-disc list-inside text-sm mt-1">${sections.length ? sections.map(s => `<li>${escapeHtml(s)}</li>`).join('') : '<li class="text-gray-500">None</li>'}</ul>
                        </div>
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

        showPreviewModal('previewModal', html, 'collegeForm');
    }

    function escapeHtml(unsafe) {
        if (!unsafe) return '';
        return unsafe.replace(/[&<>"'`]/g, function (m) { return ({
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#39;',
            '`': '&#96;'
        })[m]; });
    }
    
    // Live uniqueness checks for college code and admin email
    const csrfMeta = document.querySelector('meta[name="csrf-token"]');
    const csrfToken = csrfMeta ? csrfMeta.getAttribute('content') : null;
    if (!csrfToken) console.warn('CSRF token meta tag not found; AJAX requests may fail.');
    let codeTimeout = null, emailTimeout = null;
    let codeAvailable = null, emailAvailable = null;
    let codePending = false, emailPending = false;
    let passwordMismatch = false; // true when passwords don't match

    const codeInput = document.getElementById('college_code_input');
    const codeFeedback = document.getElementById('codeFeedback');
    const emailInput = document.getElementById('admin_email_input');
    const emailFeedback = document.getElementById('emailFeedback');
    const openPreviewBtn = document.getElementById('openPreviewBtn');

    const spinnerHTML = '<span class="inline-block w-4 h-4 border-2 border-gray-300 border-t-red-700 rounded-full animate-spin mr-2 align-middle"></span>';

    function setFeedback(el, msg, colorClass) {
        el.innerHTML = msg;
        el.className = `text-xs mt-1 ${colorClass}`;
    }

    // Live confirm-password validation
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

    function toggleCreateDisabled() {
        const nextBtn1 = document.getElementById('nextStepBtn1');
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
    }

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
            codeTimeout = setTimeout(() => checkCollegeCode(val), 500);
        });
    }

    function checkCollegeCode(code) {
        const normalized = (code || '').trim().toUpperCase();
        if (!normalized) {
            setFeedback(codeFeedback, 'Required', 'text-red-600');
            codeAvailable = false;
            toggleCreateDisabled();
            return Promise.resolve({ available: false });
        }

        // show spinner while checking
        setFeedback(codeFeedback, spinnerHTML + 'Checking…', 'text-gray-500');
        codePending = true;
        toggleCreateDisabled();

        return fetch("{{ route('osa.college.checkCode') }}", {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
            body: JSON.stringify({ college_code: normalized })
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
        fetch("{{ route('osa.college.checkEmail') }}", {
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
        })
        .catch(() => {
            setFeedback(emailFeedback, 'Error checking', 'text-red-600');
            emailPending = false;
            toggleCreateDisabled();
        });
    }</script>

<style>
    .form-step {
        transition: all 0.3s ease;
    }

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