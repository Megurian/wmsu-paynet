@extends('layouts.dashboard')

@section('title', 'Add Organization')
@section('page-title', 'Add Organization')

@section('content')
<form method="POST" action="{{ route('osa.organizations.store') }}" enctype="multipart/form-data" id="orgForm">
    @csrf
    <input type="hidden" name="current_step" id="current_step" value="{{ old('current_step', 1) }}">

    <!-- Step 1 -->
    <div class="step" id="step-1">
        <h3 class="font-semibold mb-2">Step 1: Organization Type</h3>
        <select name="role" id="org-role" class="border p-2 rounded w-full">
            <option value="">Select Type</option>
            <option value="university_org" {{ old('role')=='university_org'?'selected':'' }}>University-wide</option>
            <option value="college_org" {{ old('role')=='college_org'?'selected':'' }}>Under a College</option>
        </select>
        <button type="button" onclick="nextStep(1)" class="mt-3 bg-red-700 text-white px-4 py-2 rounded">Next</button>
    </div>

    <!-- Step 2 -->
    <div class="step hidden" id="step-2">
        <h3 class="font-semibold mb-2">Step 2: Organization Details</h3>
        <input type="text" name="org_code" value="{{ old('org_code') }}" placeholder="Organization Code" class="border p-2 rounded w-full mb-2" required>
        <input type="text" name="name" value="{{ old('name') }}" placeholder="Organization Name" class="border p-2 rounded w-full mb-2" required>
        <input type="file" name="logo" class="border p-2 rounded w-full mb-2">

        <div id="college-select" class="hidden">
            <label for="college_id">Select College</label>
            <select name="college_id" class="border p-2 rounded w-full mb-2">
                <option value="">-- Choose College --</option>
                @foreach($colleges as $college)
                    <option value="{{ $college->id }}">{{ $college->name }}</option>
                @endforeach
            </select>
        </div>

        <button type="button" onclick="prevStep(2)" class="mt-3 bg-gray-500 text-white px-4 py-2 rounded">Back</button>
        <button type="button" onclick="nextStep(2)" class="mt-3 bg-red-700 text-white px-4 py-2 rounded">Next</button>
    </div>

    <!-- Step 3 -->
    <div class="step hidden" id="step-3">
        <h3 class="font-semibold mb-2">Step 3: Create Admin Account</h3>
        <input type="text" name="admin_name" value="{{ old('admin_name') }}" placeholder="Admin Name" class="border p-2 rounded w-full mb-2" required>
        <input type="email" name="admin_email" value="{{ old('admin_email') }}" placeholder="Admin Email" class="border p-2 rounded w-full mb-2" required>
        <input type="password" name="admin_password" placeholder="Password" class="border p-2 rounded w-full mb-2" required>
        <input type="password" name="admin_password_confirmation" placeholder="Confirm Password" class="border p-2 rounded w-full mb-2" required>

        <button type="button" onclick="prevStep(3)" class="mt-3 bg-gray-500 text-white px-4 py-2 rounded">Back</button>
        <button type="submit" class="mt-3 bg-red-700 text-white px-4 py-2 rounded">Create Organization</button>
    </div>
</form>

<script>
const roleSelect = document.getElementById('org-role');

function showStep(step) {
    document.querySelectorAll('.step').forEach(s => s.classList.add('hidden'));
    document.getElementById('step-' + step).classList.remove('hidden');
    document.getElementById('current_step').value = step;
}

// Update nextStep and prevStep to use showStep
function nextStep(current) {
    if(current === 1) {
        if(roleSelect.value === '') { alert('Select a type'); return; }
        document.getElementById('college-select').classList.toggle('hidden', roleSelect.value !== 'college_org');
        showStep(2);
    } else if(current === 2) {
        showStep(3);
    }
}

function prevStep(current) {
    if(current === 2) {
        showStep(1);
    } else if(current === 3) {
        showStep(2);
    }
}

// On page load, show the step that was last submitted (or default to 1)
document.addEventListener('DOMContentLoaded', function() {
    let step = parseInt(document.getElementById('current_step').value);
    showStep(step);

    // Restore old select values for college dropdown
    @if(old('college_id'))
        document.querySelector('[name="college_id"]').value = "{{ old('college_id') }}";
    @endif
});

</script>
@endsection
