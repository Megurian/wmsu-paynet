@extends('layouts.dashboard')

@section('title', 'Create Fee')
@section('page-title', 'Create Fee (OSA)')

@section('content')
<div class="mb-8">
    <h2 class="text-3xl font-bold text-gray-800">Create Fee (OSA)</h2>
    <p class="text-sm text-gray-500 mt-1">As OSA (super admin) you can create fees for any organization. Fees created here are <strong>auto-approved</strong>. A legal basis document is required.</p>
</div>

<div class="max-w-2xl bg-white p-8 rounded shadow">
    <form method="POST" action="{{ route('osa.fees.store') }}" enctype="multipart/form-data">
        @csrf

        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">Organization</label>
            <select name="organization_id" required class="w-full border rounded px-3 py-2">
                <option value="">Select organization</option>
                @foreach($organizations as $org)
                    <option value="{{ $org->id }}">{{ $org->name }} ({{ $org->org_code }})</option>
                @endforeach
            </select>
            @error('organization_id') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
        </div>

        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">Fee Name</label>
            <input name="fee_name" required class="w-full border rounded px-3 py-2" placeholder="e.g., Student Activity Fee">
            @error('fee_name') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
        </div>

        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">Purpose</label>
            <input name="purpose" required class="w-full border rounded px-3 py-2" placeholder="e.g., Library Fund">
            @error('purpose') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
        </div>

        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
            <textarea name="description" required class="w-full border rounded px-3 py-2" rows="4"></textarea>
            @error('description') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
        </div>

        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">Amount</label>
            <input type="number" name="amount" required step="0.01" min="0" class="w-full border rounded px-3 py-2">
            @error('amount') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
        </div>

        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">Remittance (%) <span class="text-sm text-gray-500">(optional)</span></label>
            <input type="number" name="remittance_percent" step="0.01" min="0" max="100" class="w-full border rounded px-3 py-2" placeholder="e.g., 10 for 10%">
            @error('remittance_percent') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
        </div>

        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">Requirement Level</label>
            <select name="requirement_level" required class="w-full border rounded px-3 py-2">
                <option value="mandatory">Mandatory</option>
                <option value="optional">Optional</option>
            </select>
            @error('requirement_level') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
        </div>

        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">Legal Basis Document (required)</label>
            <input type="file" name="legal_basis_file" accept=".pdf,image/*" required class="w-full">
            @error('legal_basis_file') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
        </div>

        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">Certificate of Accreditation (optional)</label>
            <input type="file" name="accreditation_file" accept=".pdf,image/*" class="w-full">
            @error('accreditation_file') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
        </div>

        <div class="flex justify-end">
            <button class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">Create Fee</button>
        </div>
    </form>
</div>
@endsection