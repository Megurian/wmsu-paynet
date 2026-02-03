@extends('layouts.dashboard')

@section('title', 'Edit Fee')
@section('page-title', 'Edit Fee')

@section('content')
<div class="mb-6">
    <a href="{{ route('university_org.fees') }}" class="text-sm text-gray-600 hover:underline">&larr; Back to Fees</a>
</div>

<div class="bg-white rounded shadow p-6 max-w-2xl">
    <h2 class="text-2xl font-bold mb-4">Edit Fee: {{ $fee->fee_name }}</h2>

    <form method="POST" action="{{ route('university_org.fees.update', $fee->id) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">Fee Name</label>
            <input name="fee_name" required value="{{ old('fee_name', $fee->fee_name) }}" class="w-full border rounded px-3 py-2">
            @error('fee_name') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
        </div>

        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">Purpose</label>
            <input name="purpose" required value="{{ old('purpose', $fee->purpose) }}" class="w-full border rounded px-3 py-2">
            @error('purpose') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
        </div>

        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
            <textarea name="description" required class="w-full border rounded px-3 py-2" rows="4">{{ old('description', $fee->description) }}</textarea>
            @error('description') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
        </div>

        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">Amount</label>
            <input type="number" name="amount" required step="0.01" min="0" value="{{ old('amount', $fee->amount) }}" class="w-full border rounded px-3 py-2">
            @error('amount') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
        </div>

        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">Remittance (%) <span class="text-sm text-gray-500">(optional)</span></label>
            <input type="number" name="remittance_percent" step="0.01" min="0" max="100" value="{{ old('remittance_percent', $fee->remittance_percent) }}" class="w-full border rounded px-3 py-2" placeholder="e.g., 10 for 10%">
            @error('remittance_percent') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
        </div>

        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">Requirement Level</label>
            <select id="requirement_level_select" name="requirement_level" required class="w-full border rounded px-3 py-2">
                <option value="mandatory" {{ old('requirement_level', $fee->requirement_level) == 'mandatory' ? 'selected' : '' }}>Mandatory</option>
                <option value="optional" {{ old('requirement_level', $fee->requirement_level) == 'optional' ? 'selected' : '' }}>Optional</option>
            </select>
            @error('requirement_level') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
        </div>

        @if ($fee->accreditation_file || $fee->resolution_file)
            <div class="mb-4" id="current-files">
                <p class="text-sm font-medium text-gray-700 mb-1">Existing Files</p>
                @if ($fee->accreditation_file)
                    <p class="text-sm"><a href="{{ \Illuminate\Support\Facades\Storage::url($fee->accreditation_file) }}" target="_blank" class="text-blue-600 hover:underline">View Certificate of Accreditation</a></p>
                @endif
                @if ($fee->resolution_file)
                    <p class="text-sm"><a href="{{ \Illuminate\Support\Facades\Storage::url($fee->resolution_file) }}" target="_blank" class="text-blue-600 hover:underline">View Resolution of Collection</a></p>
                @endif
            </div>
        @endif

        <div class="mb-4" id="accreditation-group">
            <label class="block text-sm font-medium text-gray-700 mb-1">Replace Certificate of Accreditation (optional)</label>
            <input type="file" id="accreditation_file_edit" name="accreditation_file" accept=".pdf,image/*" class="w-full">
            @error('accreditation_file') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
        </div>

        <div class="mb-4 {{ old('requirement_level', $fee->requirement_level) == 'mandatory' ? '' : 'hidden' }}" id="resolution-group">
            <label class="block text-sm font-medium text-gray-700 mb-1">Replace Resolution of Collection (optional)</label>
            <input type="file" id="resolution_file_edit" name="resolution_file" accept=".pdf,image/*" class="w-full">
            @error('resolution_file') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const select = document.getElementById('requirement_level_select');
                const resGroup = document.getElementById('resolution-group');
                const resInput = document.getElementById('resolution_file_edit');

                function updateEditSupportingDocs() {
                    if (!select) return;
                    if (select.value === 'mandatory') {
                        if (resGroup) resGroup.classList.remove('hidden');
                        if (resInput) resInput.disabled = false;
                    } else {
                        if (resGroup) resGroup.classList.add('hidden');
                        if (resInput) resInput.disabled = true;
                    }
                }

                select.addEventListener('change', updateEditSupportingDocs);
                // Initialize
                updateEditSupportingDocs();
            });
        </script>

        <div class="flex justify-end gap-2">
            <a href="{{ route('university_org.fees.show', $fee->id) }}" class="px-4 py-2 border rounded">Cancel</a>
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded">Save Changes</button>
        </div>
    </form>
</div>
@endsection