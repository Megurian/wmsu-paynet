@extends('layouts.dashboard')

@section('title', 'OSA Fees')
@section('page-title', 'OSA Fees')

@section('content')
<div class="mb-8">
    <h2 class="text-3xl font-bold text-gray-800">OSA Fee Approval</h2>
    <p class="text-sm text-gray-500 mt-1">
        Welcome, {{ Auth::user()->name }}. Here you can manage the fees associated with different colleges within the university.
    </p>
</div>

<!-- Main Content -->
<div class="flex flex-col lg:flex-row gap-6">
    <!-- Left Column - Fee Details -->
    <div class="w-full lg:w-1/2">
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-xl font-semibold text-gray-800">Fee Details</h3>
                <div class="flex space-x-3">
                    <button id="rejectBtn" 
                            class="px-4 py-2 border border-red-300 text-red-700 rounded-md hover:bg-red-50">
                        Reject
                    </button>
                    <button onclick="approveFee()" 
                            class="px-4 py-2 bg-red-700 text-white rounded-md hover:bg-red-800">
                        Approve
                    </button>
                </div>
            </div>

            <!-- Organization Info -->
            <div class="mb-6">
                <h4 class="font-medium text-gray-900 mb-2">Organization</h4>
                <div class="bg-gray-50 p-4 rounded-lg">
                    <div class="flex items-center space-x-4">
                        <div class="w-16 h-16 bg-gray-200 rounded-full flex items-center justify-center">
                            <span class="text-gray-500 text-3xl">🏛️</span>
                        </div>
                        <div>
                            <h4 class="text-lg font-semibold">Supreme Student Government</h4>
                            <p class="text-sm text-gray-500">SSG-001</p>
                            <p class="text-sm text-gray-500 mt-1">University Organization</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Fee Information -->
            <div class="space-y-4">
                <div>
                    <h4 class="font-medium text-gray-900 mb-3">Fee Information</h4>
                    <div class="space-y-4">
                        <div>
                            <p class="text-sm text-gray-500">Fee Name</p>
                            <p class="font-medium">Student Activity Fee</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Amount</p>
                            <p class="text-2xl font-bold text-red-700">₱500.00</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Requested On</p>
                            <p class="font-medium">January 20, 2024</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Description</p>
                            <p class="text-gray-700">Annual fee for student activities and organization events throughout the academic year. This includes funding for seminars, workshops, and other student development programs.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Right Column - Rejection Form (Initially hidden) -->
    <div id="rejectFormContainer" class="w-full lg:w-1/2 hidden">
        <div class="bg-white rounded-lg shadow-md p-6 h-full">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-xl font-semibold text-gray-800">Reject Fee Request</h3>
                <button onclick="hideRejectForm()" class="text-gray-500 hover:text-gray-700">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <div class="space-y-6">
                <div>
                    <p class="text-gray-700 mb-4">You are about to reject the fee request from <span class="font-semibold">Supreme Student Government</span>. Please provide a reason for rejection.</p>
                    
                    <div class="space-y-4">
                        <div>
                            <label for="rejectionReason" class="block text-sm font-medium text-gray-700 mb-1">
                                Reason for Rejection
                            </label>
                            <textarea id="rejectionReason" rows="8" 
                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500"
                                placeholder="Please specify the reason for rejection..."></textarea>
                        </div>
                        
                        <div class="pt-2">
                            <button onclick="submitRejection()" 
                                    class="w-full px-4 py-2 bg-red-700 text-white rounded-md hover:bg-red-800">
                                Submit Rejection
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Show/hide rejection form
function showRejectForm() {
    document.getElementById('rejectFormContainer').classList.remove('hidden');
    // Add active class to reject button
    document.getElementById('rejectBtn').classList.add('bg-red-100', 'border-red-400');
}

function hideRejectForm() {
    document.getElementById('rejectFormContainer').classList.add('hidden');
    // Remove active class from reject button
    document.getElementById('rejectBtn').classList.remove('bg-red-100', 'border-red-400');
}

// Toggle reject form when reject button is clicked
document.getElementById('rejectBtn').addEventListener('click', function(e) {
    e.preventDefault();
    const formContainer = document.getElementById('rejectFormContainer');
    if (formContainer.classList.contains('hidden')) {
        showRejectForm();
    } else {
        hideRejectForm();
    }
});

// Action handlers
function approveFee() {
    if (confirm('Are you sure you want to approve this fee?')) {
        alert('Fee approved successfully!');
        // Add any additional approval logic here
    }
}

function submitRejection() {
    const reason = document.getElementById('rejectionReason').value.trim();
    if (!reason) {
        alert('Please provide a reason for rejection.');
        return;
    }
    
    // Add your rejection submission logic here
    console.log('Rejection reason:', reason);
    alert('Fee has been rejected with the provided reason.');
    // Reset form
    document.getElementById('rejectionReason').value = '';
    hideRejectForm();
}

// Close form when clicking outside
document.addEventListener('click', function(event) {
    const rejectForm = document.getElementById('rejectFormContainer');
    const rejectBtn = document.getElementById('rejectBtn');
    
    if (!rejectForm.contains(event.target) && !rejectBtn.contains(event.target)) {
        hideRejectForm();
    }
});
</script>

<style>
/* Smooth transitions for the rejection form */
#rejectFormContainer {
    transition: all 0.3s ease;
    animation: slideIn 0.3s ease-out;
}

@keyframes slideIn {
    from {
        opacity: 0;
        transform: translateX(20px);
    }
    to {
        opacity: 1;
        transform: translateX(0);
    }
}

/* Custom scrollbar */
::-webkit-scrollbar {
    width: 6px;
}
::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 10px;
}
::-webkit-scrollbar-thumb {
    background: #cbd5e0;
    border-radius: 10px;
}
::-webkit-scrollbar-thumb:hover {
    background: #a0aec0;
}
</style>
@endsection