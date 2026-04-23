@extends('layouts.dashboard')

@section('title', 'Create Fee')
@section('page-title', 'Create Fee (OSA)')

@section('content')
<div class="mb-8">
    <h2 class="text-3xl font-bold text-gray-800">Create Fee (OSA)</h2>
    <p class="text-sm text-gray-500 mt-1">
        As OSA (super admin) you can create fees for any organization. Fees created here are <strong>auto-approved</strong>.
    </p>
</div>

<div class="max-w-2xl bg-white p-8 rounded shadow">

    <form method="POST" action="{{ route('osa.fees.store') }}" enctype="multipart/form-data">
        @csrf

        {{-- Organization --}}
        <div class="mb-4">
            <label class="block text-sm font-medium mb-1">Organization</label>
            <select name="organization_id" required class="w-full border rounded px-3 py-2">
                <option value="">Select organization</option>
                @foreach($organizations as $org)
                    <option value="{{ $org->id }}"
                        {{ old('organization_id') == $org->id ? 'selected' : '' }}>
                        {{ $org->name }} ({{ $org->org_code }})
                    </option>
                @endforeach
            </select>
            @error('organization_id') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
        </div>

        {{-- Fee Name --}}
        <div class="mb-4">
            <label class="block text-sm font-medium mb-1">Fee Name</label>
            <input name="fee_name"
                   value="{{ old('fee_name') }}"
                   required
                   class="w-full border rounded px-3 py-2">
            @error('fee_name') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
        </div>

        {{-- Purpose --}}
        <div class="mb-4">
            <label class="block text-sm font-medium mb-1">Purpose</label>
            <input name="purpose"
                   value="{{ old('purpose') }}"
                   required
                   class="w-full border rounded px-3 py-2">
            @error('purpose') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
        </div>

        {{-- Description --}}
        <div class="mb-4">
            <label class="block text-sm font-medium mb-1">Description</label>
            <textarea name="description" required class="w-full border rounded px-3 py-2" rows="4">{{ old('description') }}</textarea>
            @error('description') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
        </div>

        {{-- Amount --}}
        <div class="mb-4">
            <label class="block text-sm font-medium mb-1">Amount</label>
            <input type="number"
                   name="amount"
                   value="{{ old('amount') }}"
                   step="0.01"
                   min="0"
                   required
                   class="w-full border rounded px-3 py-2">
            @error('amount') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
        </div>

        {{-- Remittance --}}
        <div class="mb-4">
            <label class="block text-sm font-medium mb-1">
                Remittance (%) <span class="text-gray-500 text-sm">(optional)</span>
            </label>
            <input type="number"
                   name="remittance_percent"
                   value="{{ old('remittance_percent') }}"
                   step="0.01"
                   min="0"
                   max="100"
                   class="w-full border rounded px-3 py-2">
            @error('remittance_percent') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
        </div>

        {{-- Requirement Level --}}
        <div class="mb-4">
            <label class="block text-sm font-medium mb-1">Requirement Level</label>
            <select name="requirement_level" required class="w-full border rounded px-3 py-2">
                <option value="mandatory" {{ old('requirement_level') == 'mandatory' ? 'selected' : '' }}>Mandatory</option>
                <option value="optional" {{ old('requirement_level') == 'optional' ? 'selected' : '' }}>Optional</option>
            </select>
            @error('requirement_level') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
        </div>

        {{-- Recurrence --}}
        <div class="mb-4">
            <label class="block text-sm font-medium mb-1">Fee Recurrence</label>
            <select name="recurrence" required class="w-full border rounded px-3 py-2">
                <option value="one_time" {{ old('recurrence') == 'one_time' ? 'selected' : '' }}>One Time</option>
                <option value="semestrial" {{ old('recurrence') == 'semestrial' ? 'selected' : '' }}>Semestrial</option>
                <option value="annual" {{ old('recurrence') == 'annual' ? 'selected' : '' }}>Annual</option>
            </select>
            @error('recurrence') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
        </div>

        {{-- Files --}}
        <div class="mb-4">
            <label class="block text-sm font-medium mb-1">Legal Basis Document (required)</label>
            <input type="file" name="legal_basis_file" accept=".pdf,image/*" required class="w-full">
            @error('legal_basis_file') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
            <p class="text-xs text-gray-500 mt-1">
                If validation fails, you must re-upload the file.
            </p>
        </div>

        <div class="mb-4">
            <label class="block text-sm font-medium mb-1">Certificate of Accreditation (optional)</label>
            <input type="file" name="accreditation_file" accept=".pdf,image/*" class="w-full">
            @error('accreditation_file') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
        </div>

        {{-- Submit --}}
        <div class="flex justify-end">
            <button type="button"
                onclick="openConfirmModal({
                    title: 'Create Fee',
                    message: 'Do you want to proceed?',
                    confirmText: 'Create',
                    onConfirm: () => this.closest('form').submit()
                })"
                class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
                Create Fee
            </button>
        </div>

    </form>
</div>
@endsection