@extends('layouts.dashboard')

@section('title', 'Offices')
@section('page-title', ($organization?->org_code ?? 'Organization') . ' Offices')

@section('content')
<div class="mb-8">
    <h2 class="text-3xl font-bold text-gray-800"> {{ ($organization?->org_code ?? 'Organization') . " Offices" }} </h2>
    <p class="text-sm text-gray-500 mt-1">
        Welcome, {{ Auth::user()->name }}. Here you can manage the Offices associated with different colleges within the university.
    </p>
</div>
<div class="flex justify-center">
    <form method="POST" action="" enctype="multipart/form-data" class="w-full max-w-2xl bg-white p-8 rounded-lg shadow-lg">
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

            <div class="mb-4">
                <label class="block font-medium mb-1">Select Type</label>
                <select name="role" id="org-role" class="w-full border border-gray-300 px-4 py-2 rounded focus:outline-none focus:ring-2 focus:ring-blue-400">
                    <option value="">-- Select Type --</option>
                    <option value="college_org">Under a College</option>
                </select>
            </div>

            <div id="college-select" class="mb-4">
                <label class="block font-medium mb-1">Select College</label>
                <select name="college_code" class="shadow border-gray-300 rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="college">
                    <option value="" disabled {{ empty(old('college_code')) ? 'selected' : '' }}> Select College</option>
                    @forelse($colleges as $college)
                        <option value="{{ $college->college_code }}" {{ old('college_code') == $college->college_code ? 'selected' : '' }}>
                            {{ $college->name }}
                        </option>
                    @empty
                        <option value="" disabled>No colleges available</option>
                    @endforelse
                </select>
            </div>

            <div class="flex justify-end">
                <button type="button" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700 transition" onclick="nextStep(1)">Next</button>
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
                <input type="text" name="org_code" value="" placeholder="Enter Organization Code" class="w-full border border-gray-300 px-4 py-2 rounded focus:outline-none focus:ring-2 focus:ring-blue-400" required>
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
            <h3 class="text-xl font-bold mb-6 text-center">Create Admin Account</h3>

            <div class="mb-4">
                <label class="block font-medium mb-1">Admin Name</label>
                <input type="text" name="admin_name" value="" placeholder="Enter Admin Name" class="w-full border border-gray-300 px-4 py-2 rounded focus:outline-none focus:ring-2 focus:ring-blue-400" required>
            </div>

            <div class="mb-4">
                <label class="block font-medium mb-1">Admin Email</label>
                <input type="email" name="admin_email" value="}" placeholder="Enter Admin Email" class="w-full border border-gray-300 px-4 py-2 rounded focus:outline-none focus:ring-2 focus:ring-blue-400" required>
            </div>

            <div class="mb-4">
                <label class="block font-medium mb-1">Password</label>
                <input type="password" name="admin_password" placeholder="Enter Password" class="w-full border border-gray-300 px-4 py-2 rounded focus:outline-none focus:ring-2 focus:ring-blue-400" required>
            </div>

            <div class="mb-6">
                <label class="block font-medium mb-1">Confirm Password</label>
                <input type="password" name="admin_password_confirmation" placeholder="Confirm Password" class="w-full border border-gray-300 px-4 py-2 rounded focus:outline-none focus:ring-2 focus:ring-blue-400" required>
            </div>

            <div class="flex justify-between">
                <button type="button" class="bg-gray-400 text-white px-6 py-2 rounded hover:bg-gray-500 transition" onclick="prevStep(3)">Back</button>
                <button type="submit" class="bg-red-700 text-white px-6 py-2 rounded hover:bg-red-800" onclick="return confirm('Create this organization and admin account?')">Create Organization</button>
            </div>
        </div>
    </form>
</div>

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

function showStep(step) {
    document.querySelectorAll('[id^="step-"]').forEach(step => {
        step.classList.add('hidden');
    });
    
    document.getElementById(`step-${step}`)?.classList.remove('hidden');
    currentStep = step;
    updateProgress();
}

function nextStep() {
    if (currentStep < 3) { 
        showStep(currentStep + 1);
    } else {
        alert('Form submitted successfully!');
        document.getElementById('fee-form')?.reset();
        showStep(1);
    }
}

function prevStep() { 
    if (currentStep > 1) {
        showStep(currentStep - 1);
    }
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
    
    const form = document.getElementById('fee-form');
    if (form) {
        form.onsubmit = function(e) {
            e.preventDefault();
            nextStep();
        };
    }
});
</script>

<style>
.form-step { transition: all 0.3s ease; }
</style>
@endsection
