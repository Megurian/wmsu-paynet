@extends('layouts.dashboard')

@section('title', 'New College')
@section('page-title', 'Create New College')

@section('content')
<div class="flex justify-center">
    <form id="collegeForm" action="{{ route('osa.college.store') }}" method="POST" enctype="multipart/form-data" class="w-full max-w-2xl bg-white p-8 rounded-lg shadow-lg">
        @csrf

        <div class="flex items-center justify-center mb-8">
            <div class="flex items-center">
                <div id="dot-1" class="w-6 h-6 rounded-full bg-blue-600 flex items-center justify-center text-white font-bold">1</div>
                <div id="line-1" class="w-24 h-1 bg-gray-300"></div>
            </div>

            <div class="flex items-center">
                <div id="dot-2" class="w-6 h-6 rounded-full bg-gray-300 flex items-center justify-center text-white font-bold">2</div>
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
                <input type="text" name="college_code" class="w-full border border-gray-300 px-4 py-2 rounded focus:outline-none focus:ring-2 focus:ring-blue-400" placeholder="Enter College Code (e.g., CCS001)" required>
                <p class="text-xs text-gray-500 mt-1">Unique code for the college</p>
            </div>

            <div class="mb-6">
                <label class="block font-medium mb-1">College Logo (Optional)</label>
                <input type="file" name="logo" class="w-full">
            </div>

            <div class="flex justify-end">
                <button type="button" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700 transition" onclick="nextStep(1)">Next</button>
            </div>
        </div>

        <div class="form-step hidden" id="step-2">
            <h3 class="text-xl font-bold mb-6 text-center">Initial College Admin</h3>

            <div class="mb-4">
                <label class="block font-medium mb-1">Admin Name</label>
                <input type="text" name="admin_name" class="w-full border border-gray-300 px-4 py-2 rounded focus:outline-none focus:ring-2 focus:ring-blue-400" placeholder="Enter Admin Name" required>
            </div>

            <div class="mb-4">
                <label class="block font-medium mb-1">Admin Email</label>
                <input type="email" name="admin_email" class="w-full border border-gray-300 px-4 py-2 rounded focus:outline-none focus:ring-2 focus:ring-blue-400" placeholder="Enter Admin Email" required>
            </div>

            <div class="mb-4">
                <label class="block font-medium mb-1">Admin Password</label>
                <input type="password" name="admin_password" class="w-full border border-gray-300 px-4 py-2 rounded focus:outline-none focus:ring-2 focus:ring-blue-400" placeholder="Enter Password" required>
            </div>

            <div class="mb-6">
                <label class="block font-medium mb-1">Confirm Password</label>
                <input type="password" name="admin_password_confirmation" class="w-full border border-gray-300 px-4 py-2 rounded focus:outline-none focus:ring-2 focus:ring-blue-400" placeholder="Confirm Password" required>
            </div>

            <div class="flex justify-between">
                <button type="button" class="bg-gray-400 text-white px-6 py-2 rounded hover:bg-gray-500 transition" onclick="prevStep(2)">Back</button>
                <button type="submit" class="bg-red-700 text-white px-6 py-2 rounded hover:bg-red-800 transition">Create College</button>
            </div>
        </div>
    </form>
</div>

<script>
    let currentStep = 1;
    const totalSteps = 2;

    function updateProgress() {
        if(currentStep === 1){
            document.getElementById('dot-1').classList.add('bg-blue-600');
            document.getElementById('dot-1').classList.remove('bg-gray-300');
            document.getElementById('line-1').classList.remove('bg-blue-600');
            document.getElementById('line-1').classList.add('bg-gray-300');
            document.getElementById('dot-2').classList.add('bg-gray-300');
            document.getElementById('dot-2').classList.remove('bg-blue-600');
        } else if(currentStep === 2){
            document.getElementById('dot-1').classList.add('bg-blue-600');
            document.getElementById('line-1').classList.add('bg-blue-600');
            document.getElementById('dot-2').classList.add('bg-blue-600');
            document.getElementById('dot-2').classList.remove('bg-gray-300');
        }
    }

    function nextStep(step) {
        document.getElementById(`step-${step}`).classList.add('hidden');
        document.getElementById(`step-${step + 1}`).classList.remove('hidden');
        currentStep++;
        updateProgress();
    }

    function prevStep(step) {
        document.getElementById(`step-${step}`).classList.add('hidden');
        document.getElementById(`step-${step - 1}`).classList.remove('hidden');
        currentStep--;
        updateProgress();
    }

    // Initialize
    updateProgress();
</script>

<style>
    .form-step {
        transition: all 0.3s ease;
    }
</style>
@endsection
