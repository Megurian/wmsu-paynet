@extends('layouts.dashboard')

@section('title', 'Create College Fee')
@section('page-title', 'Create College Fee')

@section('content')

<div class="mb-6">
    <a href="{{ route('college.fees') }}" 
        class="inline-block bg-gray-200 hover:bg-gray-300 text-gray-800 px-4 py-2 rounded-lg shadow">
        &larr; Back to Fees List
    </a>
</div>

<div class="max-w-2xl mx-auto bg-white shadow rounded-lg p-6">
    <h2 class="text-2xl font-semibold text-gray-800 mb-4">Add New College Fee</h2>
    <p class="text-gray-600 mb-6">Fill in the details below to create a new fee. Once submitted, it will be sent for dean approval.</p>

    <form method="POST" action="{{ route('college.fees.store') }}" class="space-y-5">
        @csrf

        <div>
            <label for="fee_name" class="block text-sm font-medium text-gray-700 mb-1">Fee Name <span class="text-red-500">*</span></label>
            <input type="text" id="fee_name" name="fee_name" placeholder="Enter fee name"
                   class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-red-700 focus:border-red-700"
                   required>
        </div>

        <!-- Purpose -->
        <div>
            <label for="purpose" class="block text-sm font-medium text-gray-700 mb-1">Purpose <span class="text-red-500">*</span></label>
            <input type="text" id="purpose" name="purpose" placeholder="What is this fee for?"
                   class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-red-700 focus:border-red-700"
                   required>
        </div>

        <!-- Description -->
        <div>
            <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Description</label>
            <textarea id="description" name="description" rows="4" placeholder="Optional detailed description"
                      class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-red-700 focus:border-red-700"></textarea>
        </div>


        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
           <!-- Amount -->
            <div>
                <label for="amount" class="block text-sm font-medium text-gray-700 mb-1">Amount (₱)</label>
                <input type="number" id="amount" name="amount" step="0.01" placeholder="Enter fee amount"
                    class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-red-700 focus:border-red-700">
            </div>

            <!-- Requirement Level -->
            <div>
                <label for="requirement_level" class="block text-sm font-medium text-gray-700 mb-1">Requirement Level</label>
                <select id="requirement_level" name="requirement_level"
                        class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-red-700 focus:border-red-700">
                    <option value="mandatory">Mandatory</option>
                    <option value="optional">Optional</option>
                </select>
            </div>
        </div>
        <div class="pt-4">
            <button type="submit"
                    class="bg-red-700 hover:bg-red-800 text-white font-semibold px-6 py-2 rounded-lg shadow">
                Submit for Approval
            </button>
        </div>
    </form>
</div>
@endsection