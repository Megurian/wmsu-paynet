@extends('layouts.dashboard')

@section('title', 'Create Organization')
@section('page-title', 'Create College Organization')

@section('content')
<div class="flex justify-center">
<form id="organizationForm" action="{{ route('college.local_organizations.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
    @csrf
    <h3 class="text-lg font-semibold">Organization Details</h3>
     <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-4">
        <div class="grid grid-cols-1 md:grid-cols-1 gap-5 mb-4">
            <div class="mb-4">
                <label class="block font-medium mb-1">Organization Name</label>
                <input type="text" name="name"  placeholder="Enter Organization Name" class="w-full border border-gray-300 px-4 py-2 rounded focus:outline-none focus:ring-2 focus:ring-blue-400" required>
            </div>

            <div class="mb-4">
                <label class="block font-medium mb-1">Organization Code</label>
                <input id="org_code_input" type="text" name="org_code" placeholder="Enter Organization Code" class="w-full border border-gray-300 px-4 py-2 rounded focus:outline-none focus:ring-2 focus:ring-blue-400" required>
                <p class="text-xs text-gray-500 mt-1">Unique code for the organization</p>
                <p id="codeFeedback" class="text-xs mt-1"></p>
            </div>
        </div>

        <div class="mb-6 relative">
            <label class="block font-medium px-5 mb-2">Organization Logo (Optional)</label>
            <div class="w-40 h-40 border-2 border-dashed border-gray-300 rounded-lg flex items-center justify-center cursor-pointer relative mx-auto">
                <button type="button" class="hidden absolute -top-7 -right-3 right-0 bg-red-600 text-white rounded-full w-6 h-6 flex items-center justify-center text-xs hover:bg-red-700">×</button>
                <span class="text-gray-400 text-4xl font-bold">+</span>
                <img class="hidden w-full h-full object-cover rounded-lg absolute top-0 left-0" alt="Logo Preview">
                <input type="file" name="logo" id="logoInput" accept="image/*" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer">
            </div>
            <p class="text-xs text-gray-500 mt-1 text-center">Click to upload organization logo</p>
        </div>
    </div>
    
    <h3 class="text-lg font-semibold">Initial Admin Details</h3>
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div>
            <label class="block mb-1 text-gray-700 font-medium">Last Name <span class="text-red-500">*</span></label>
            <input type="text" name="last_name" class="w-full border border-gray-300 px-4 p-2 rounded focus:ring-1 focus:ring-blue-400" required>
        </div>
        <div>
            <label class="block mb-1 text-gray-700 font-medium">First Name <span class="text-red-500">*</span></label>
            <input type="text" name="first_name" class="w-full border border-gray-300 px-4 p-2 rounded focus:ring-1 focus:ring-blue-400" required>
        </div>
        <div>
            <label class="block mb-1 text-gray-700 font-medium">Middle Name</label>
            <input type="text" name="middle_name" class="w-full border border-gray-300 px-4 p-2 rounded focus:ring-1 focus:ring-blue-400">
        </div>
        <div>
            <label class="block mb-1 text-gray-700 font-medium">Suffix</label>
            <select name="suffix" class="w-full border border-gray-300 px-4 p-2 rounded focus:ring-1 focus:ring-blue-400">
                <option value="">None</option>
                <option value="Jr.">Jr.</option>
                <option value="Sr.">Sr.</option>
                <option value="III">III</option>
                <option value="IV">IV</option>
            </select>
        </div>
    </div>
    <div>
        <label class="block font-medium mb-1">Admin Email</label>
        <input id="admin_email_input" type="email" name="admin_email" placeholder="Enter Admin Email" class="w-full border border-gray-300 px-4 p-2 rounded focus:ring-1 focus:ring-blue-400" required>
        <p id="emailFeedback" class="text-xs mt-1"></p>
    </div>
    <div>
        <label class="block font-medium mb-1">Password</label>
        <input id="admin_password" type="password" name="admin_password" placeholder="Enter Password" class="w-full border border-gray-300 px-4 p-2 rounded focus:ring-1 focus:ring-blue-400" required>
    </div>
    <div>
        <label class="block font-medium mb-1">Confirm Password</label>
        <input id="admin_password_confirmation" type="password" name="admin_password_confirmation" placeholder="Confirm Password" class="w-full border border-gray-300 px-4 p-2 rounded focus:ring-1 focus:ring-blue-400" required>
        <p id="passwordFeedback" class="text-xs mt-1 text-red-600"></p>
    </div>
    <div class="flex justify-end">
    <button type="submit" id="submitOrganizationBtn" class="bg-red-700 text-white px-6 py-2 rounded hover:bg-red-800">Submit Organization</button>
    </div>
</div>
</form>
</div>

<script>
(function() {
    const form = document.getElementById('organizationForm');
    const codeInput = document.getElementById('org_code_input');
    const emailInput = document.getElementById('admin_email_input');
    const pwInput = document.getElementById('admin_password');
    const pwcInput = document.getElementById('admin_password_confirmation');
    const codeFeedback = document.getElementById('codeFeedback');
    const emailFeedback = document.getElementById('emailFeedback');
    const passwordFeedback = document.getElementById('passwordFeedback');
    const submitBtn = document.getElementById('submitOrganizationBtn');

    let codeAvailable = null;
    let emailAvailable = null;
    let codePending = false;
    let emailPending = false;
    let passwordMismatch = false;
    let codeTimeout = null;
    let emailTimeout = null;

    const csrfMeta = document.querySelector('meta[name="csrf-token"]');
    const csrfToken = csrfMeta ? csrfMeta.getAttribute('content') : null;
    const spinnerHTML = '<span class="inline-block w-4 h-4 border-2 border-gray-300 border-t-red-700 rounded-full animate-spin mr-2 align-middle"></span>';

    function setFeedback(el, msg, colorClass) {
        if (!el) return;
        el.innerHTML = msg;
        el.className = `text-xs mt-1 ${colorClass}`;
    }

    function toggleSubmitDisabled() {
        const disabled = codeAvailable === false || emailAvailable === false || codePending || emailPending || passwordMismatch;
        if (!submitBtn) return;
        submitBtn.disabled = disabled;
        submitBtn.classList.toggle('opacity-60', disabled);
        submitBtn.classList.toggle('cursor-not-allowed', disabled);
    }

    function checkPasswordMatch() {
        const pw = pwInput ? pwInput.value : '';
        const pwc = pwcInput ? pwcInput.value : '';
        if (!pw && !pwc) {
            passwordMismatch = false;
            if (passwordFeedback) passwordFeedback.textContent = '';
            toggleSubmitDisabled();
            return;
        }
        if (pw !== pwc) {
            passwordMismatch = true;
            if (passwordFeedback) passwordFeedback.textContent = 'Passwords do not match';
        } else {
            passwordMismatch = false;
            if (passwordFeedback) passwordFeedback.textContent = '';
        }
        toggleSubmitDisabled();
    }

    function checkOrgCode(code) {
        if (!codeInput || !csrfToken) return Promise.resolve({ available: false });
        const normalized = code.trim().toUpperCase();
        if (!normalized) {
            setFeedback(codeFeedback, 'Required', 'text-red-600');
            codeAvailable = false;
            codePending = false;
            toggleSubmitDisabled();
            return Promise.resolve({ available: false });
        }

        setFeedback(codeFeedback, spinnerHTML + 'Checking…', 'text-gray-500');
        codePending = true;
        toggleSubmitDisabled();

        return fetch("{{ route('college.local_organizations.checkCode') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
            },
            body: JSON.stringify({ org_code: normalized }),
        })
        .then((res) => res.json())
        .then((data) => {
            if (data.available) {
                setFeedback(codeFeedback, 'Available', 'text-green-600');
                codeAvailable = true;
            } else {
                setFeedback(codeFeedback, 'Already taken', 'text-red-600');
                codeAvailable = false;
            }
            codePending = false;
            toggleSubmitDisabled();
            return data;
        })
        .catch(() => {
            setFeedback(codeFeedback, 'Error checking', 'text-red-600');
            codeAvailable = false;
            codePending = false;
            toggleSubmitDisabled();
            return { available: false };
        });
    }

    function checkAdminEmail(email) {
        if (!emailInput || !csrfToken) return Promise.resolve({ available: false });
        const trimmed = email.trim();
        if (!trimmed) {
            setFeedback(emailFeedback, 'Required', 'text-red-600');
            emailAvailable = false;
            emailPending = false;
            toggleSubmitDisabled();
            return Promise.resolve({ available: false });
        }

        setFeedback(emailFeedback, spinnerHTML + 'Checking…', 'text-gray-500');
        emailPending = true;
        toggleSubmitDisabled();

        return fetch("{{ route('college.local_organizations.checkEmail') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
            },
            body: JSON.stringify({ admin_email: trimmed }),
        })
        .then((res) => res.json())
        .then((data) => {
            if (data.available) {
                setFeedback(emailFeedback, 'Available', 'text-green-600');
                emailAvailable = true;
            } else {
                setFeedback(emailFeedback, 'Already taken', 'text-red-600');
                emailAvailable = false;
            }
            emailPending = false;
            toggleSubmitDisabled();
            return data;
        })
        .catch(() => {
            setFeedback(emailFeedback, 'Error checking', 'text-red-600');
            emailAvailable = false;
            emailPending = false;
            toggleSubmitDisabled();
            return { available: false };
        });
    }

    if (codeInput) {
        codeInput.addEventListener('input', () => {
            clearTimeout(codeTimeout);
            codeAvailable = null;
            codePending = true;
            toggleSubmitDisabled();
            codeTimeout = setTimeout(() => checkOrgCode(codeInput.value), 500);
        });
    }

    if (emailInput) {
        emailInput.addEventListener('input', () => {
            clearTimeout(emailTimeout);
            emailAvailable = null;
            emailPending = true;
            toggleSubmitDisabled();
            emailTimeout = setTimeout(() => checkAdminEmail(emailInput.value), 500);
        });
    }

    if (pwInput && pwcInput) {
        pwInput.addEventListener('input', checkPasswordMatch);
        pwcInput.addEventListener('input', checkPasswordMatch);
    }

    if (form) {
        form.addEventListener('submit', function(event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                return;
            }

            if (passwordMismatch) {
                event.preventDefault();
                pwcInput?.focus();
                return;
            }

            if (codeAvailable === false || emailAvailable === false) {
                event.preventDefault();
                if (codeAvailable === false) codeInput?.focus();
                else emailInput?.focus();
                return;
            }

            if (codeAvailable === null || emailAvailable === null || codePending || emailPending) {
                event.preventDefault();
                Promise.all([
                    codeAvailable === null ? checkOrgCode(codeInput.value) : Promise.resolve({ available: codeAvailable }),
                    emailAvailable === null ? checkAdminEmail(emailInput.value) : Promise.resolve({ available: emailAvailable }),
                ]).then(([codeResult, emailResult]) => {
                    if (codeResult.available && emailResult.available && !passwordMismatch) {
                        form.submit();
                    }
                });
            }
        });
    }

    toggleSubmitDisabled();
})();
</script>
@endsection
