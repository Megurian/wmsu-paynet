@extends('layouts.dashboard')

@section('title', 'Fees')
@section('page-title', 'Setup of Fees')

@section('content')
<div class="mb-8">
    <h2 class="text-3xl font-bold text-gray-800">Setup of Fees</h2>
    <p class="text-sm text-gray-500 mt-1">
        Welcome, {{ Auth::user()->name }}. Here you can manage the fees associated with different colleges within the university.
    </p>
</div>

<div class="flex justify-center">
    <form id="fee-form" method="POST" action="" enctype="multipart/form-data" class="w-full max-w-2xl bg-white p-8 rounded-lg shadow-lg">
        @csrf
        <input type="hidden" name="current_step" id="current_step" value="1">

        <!-- Progress Bar -->
        <div class="flex items-center justify-center mb-8">
            <!-- Step 1 -->
            <div class="flex items-center">
                <div id="step-1-indicator" class="step-indicator flex items-center justify-center w-10 h-10 rounded-full bg-red-700 text-white">
                    1
                </div>
                <div id="step-1-line" class="w-24 h-1 bg-gray-200"></div>
            </div>
            
            <!-- Step 2 -->
            <div class="flex items-center">
                <div id="step-2-indicator" class="step-indicator flex items-center justify-center w-10 h-10 rounded-full bg-gray-200">
                    2
                </div>
            </div>
        </div>

        <!-- Step 1: Fee Details -->
        <div id="step-1" class="form-step">
            <div class="space-y-6">
                <div>
                    <label for="fee_name" class="block text-sm font-medium text-gray-700 mb-1">Fee Name</label>
                    <input type="text" id="fee_name" name="fee_name" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-red-500 focus:border-red-500"
                           placeholder="e.g., Student Activity Fee">
                </div>

                <div>
                    <label for="amount" class="block text-sm font-medium text-gray-700 mb-1">Amount</label>
                    <div class="mt-1 relative rounded-md shadow-sm">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <span class="text-gray-500 sm:text-sm">₱</span>
                        </div>
                        <input type="number" id="amount" name="amount" required
                               class="focus:ring-red-500 focus:border-red-500 block w-full pl-7 pr-12 sm:text-sm border-gray-300 rounded-md"
                               placeholder="0.00">
                    </div>
                </div>

                <div class="flex justify-end mt-8">
                    <button type="button" onclick="nextStep()" 
                            class="ml-3 inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-red-700 hover:bg-red-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                        Next
                    </button>
                </div>
            </div>
        </div>

        <!-- Step 2: Fee Description -->
        <div id="step-2" class="form-step hidden">
            <div class="space-y-6">
                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                    <textarea id="description" name="description" rows="4"
                              class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-red-500 focus:border-red-500"
                              placeholder="Enter a brief description about this fee..."></textarea>
                </div>

                <div class="flex justify-between mt-8">
                    <button type="button" onclick="prevStep()" 
                            class="inline-flex justify-center py-2 px-4 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                        Back
                    </button>
                    <button type="submit" 
                            class="ml-3 inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-red-700 hover:bg-red-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                        Submit Fee
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
let currentStep = 1;

function updateProgress() {
    // Update step indicators
    for (let i = 1; i <= 2; i++) {
        const indicator = document.getElementById(`step-${i}-indicator`);
        const line = document.getElementById(`step-${i}-line`);
        
        if (i < currentStep) {
            // Completed steps
            indicator.classList.remove('bg-gray-200');
            indicator.classList.add('bg-green-500', 'text-white');
            if (line) line.classList.add('bg-green-500');
        } else if (i === currentStep) {
            // Current step
            indicator.classList.remove('bg-gray-200', 'bg-green-500');
            indicator.classList.add('bg-red-700', 'text-white');
            if (line) line.classList.remove('bg-green-500');
        } else {
            // Upcoming steps
            indicator.classList.remove('bg-red-700', 'bg-green-500', 'text-white');
            indicator.classList.add('bg-gray-200');
            if (line) line.classList.remove('bg-green-500');
        }
    }
}

function showStep(step) {
    // Hide all steps
    document.querySelectorAll('.form-step').forEach(step => {
        step.classList.add('hidden');
    });
    
    // Show current step
    const currentStepElement = document.getElementById(`step-${step}`);
    if (currentStepElement) {
        currentStepElement.classList.remove('hidden');
    }
    currentStep = step;
    updateProgress();
}

function nextStep() {
    if (currentStep < 2) {
        showStep(currentStep + 1);
    } else {
        // On last step, submit the form
        document.getElementById('fee-form').submit();
    }
}

function prevStep() { 
    if (currentStep > 1) {
        showStep(currentStep - 1);
    }
}

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    showStep(1);
    
    // Update button behaviors
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
            // You can add form validation here before submission
            alert('Fee submitted successfully!');
            form.reset();
            showStep(1);
        };
    }
});
</script>

<style>
.form-step {
    transition: all 0.3s ease;
}
.step-indicator {
    transition: all 0.3s ease;
}
</style>
@endsection