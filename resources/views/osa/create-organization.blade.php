@extends('layouts.dashboard')

@section('title', 'Add Organization')
@section('page-title', 'Add Organization')

@section('content')
<form method="POST" action="{{ route('osa.organizations.store') }}" enctype="multipart/form-data" id="orgForm">
    @csrf

    <!-- Step 1 -->
    <div class="step" id="step-1">
        <h3 class="font-semibold mb-2">Step 1: Organization Type</h3>
        <select name="role" id="org-role" class="border p-2 rounded w-full">
            <option value="">Select Type</option>
            <option value="university_org">University-wide</option>
            <option value="college_org">Under a College</option>
        </select>
        <button type="button" onclick="nextStep(1)" class="mt-3 bg-red-700 text-white px-4 py-2 rounded">Next</button>
    </div>

    <!-- Step 2 -->
    <div class="step hidden" id="step-2">
        <h3 class="font-semibold mb-2">Step 2: Organization Details</h3>
        <input type="text" name="org_code" placeholder="Organization Code" class="border p-2 rounded w-full mb-2" required>
        <input type="text" name="name" placeholder="Organization Name" class="border p-2 rounded w-full mb-2" required>
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
        <input type="text" name="admin_name" placeholder="Admin Name" class="border p-2 rounded w-full mb-2" required>
        <input type="email" name="admin_email" placeholder="Admin Email" class="border p-2 rounded w-full mb-2" required>
        <input type="password" name="admin_password" placeholder="Password" class="border p-2 rounded w-full mb-2" required>
        <input type="password" name="admin_password_confirmation" placeholder="Confirm Password" class="border p-2 rounded w-full mb-2" required>

        <button type="button" onclick="prevStep(3)" class="mt-3 bg-gray-500 text-white px-4 py-2 rounded">Back</button>
        <button type="submit" class="mt-3 bg-red-700 text-white px-4 py-2 rounded">Create Organization</button>
    </div>
</form>

<script>
const roleSelect = document.getElementById('org-role');

function nextStep(current) {
    if(current === 1) {
        if(roleSelect.value === '') { alert('Select a type'); return; }
        document.getElementById('college-select').classList.toggle('hidden', roleSelect.value !== 'college_org');
        document.getElementById('step-1').classList.add('hidden');
        document.getElementById('step-2').classList.remove('hidden');
    } else if(current === 2) {
        document.getElementById('step-2').classList.add('hidden');
        document.getElementById('step-3').classList.remove('hidden');
    }
}

function prevStep(current) {
    if(current === 2) {
        document.getElementById('step-2').classList.add('hidden');
        document.getElementById('step-1').classList.remove('hidden');
    } else if(current === 3) {
        document.getElementById('step-3').classList.add('hidden');
        document.getElementById('step-2').classList.remove('hidden');
    }
}
</script>
@endsection
