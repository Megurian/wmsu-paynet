@extends('layouts.dashboard')

@section('title', 'Add Organization')
@section('page-title', 'Add Organization')

@section('content')
<div class="flex justify-center">
    <form method="POST" action="{{ route('osa.organizations.store') }}" enctype="multipart/form-data" class="w-full max-w-2xl bg-white p-8 rounded-lg shadow-lg">
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
                <input type="text" name="org_code" value="{{ old('org_code') }}" placeholder="Enter Organization Code" class="w-full border border-gray-300 px-4 py-2 rounded focus:outline-none focus:ring-2 focus:ring-blue-400" required>
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
                <input type="text" name="admin_name" value="{{ old('admin_name') }}" placeholder="Enter Admin Name" class="w-full border border-gray-300 px-4 py-2 rounded focus:outline-none focus:ring-2 focus:ring-blue-400" required>
            </div>

            <div class="mb-4">
                <label class="block font-medium mb-1">Admin Email</label>
                <input type="email" name="admin_email" value="{{ old('admin_email') }}" placeholder="Enter Admin Email" class="w-full border border-gray-300 px-4 py-2 rounded focus:outline-none focus:ring-2 focus:ring-blue-400" required>
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

function nextStep(step) {
    if (step === 1) {
        const role = document.getElementById('org-role').value;
        const collegeSelect = document.querySelector('[name="college_id"]');

        if (!role) {
            alert('Please select organization type.');
            return;
        }

        if (role === 'college_org' && !collegeSelect.value) {
            alert('Please select a college.');
            return;
        }
    }

    showStep(step + 1);
}

function prevStep(step){ showStep(step-1); }

document.addEventListener('DOMContentLoaded', function(){
    showStep(currentStep);

    @if(old('college_id'))
        document.querySelector('[name="college_id"]').value = "{{ old('college_id') }}";
    @endif
});

const logoInput = document.getElementById('logoInput');
const logoPreview = document.getElementById('logoPreview');
const logoPlus = document.getElementById('logoPlus');
const removeLogo = document.getElementById('removeLogo');

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
});

</script>

<style>
.form-step { transition: all 0.3s ease; }
</style>
@endsection
