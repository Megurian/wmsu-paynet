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
                <input type="text" name="college_code" class="w-full border border-gray-300 px-4 py-2 rounded focus:outline-none focus:ring-2 focus:ring-blue-400" placeholder="Enter College Code (e.g., CCS001)" required>
                <p class="text-xs text-gray-500 mt-1">Unique code for the college</p>
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
                <button type="button" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700 transition" onclick="nextStep()">Next</button>
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
                <button type="button" class="bg-gray-400 text-white px-6 py-2 rounded hover:bg-gray-500 transition" onclick="prevStep()">Back</button>
                <button type="submit" class="bg-red-700 text-white px-6 py-2 rounded hover:bg-red-800 transition">Create College</button>
            </div>
        </div>
    </form>
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
            currentStep++;
            showStep(currentStep);
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
</script>

<style>
    .form-step {
        transition: all 0.3s ease;
    }

</style>
@endsection