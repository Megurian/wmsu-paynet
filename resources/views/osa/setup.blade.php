@extends('layouts.dashboard')

@section('title', 'OSA Setup')
@section('page-title', 'Setup')

@section('content')
<h2 class="text-2xl font-bold mb-6">OSA Setup</h2>

@if(session('status'))
    <div class="mb-4 p-3 bg-green-50 text-green-700 rounded border border-green-200">
        {{ session('status') }}
    </div>
@endif

{{-- Add School Year --}}
<form method="POST" action="{{ route('osa.setup.store') }}" class="mb-6 space-y-4 bg-white p-6 rounded shadow-md">
    @csrf
    <h3 class="text-lg font-semibold mb-2">Add New School Year</h3>

    <div class="flex gap-4">
        <div class="flex-1">
            <label class="block text-sm font-medium text-gray-700 mb-1">Start Date</label>
            <input type="date" name="sy_start" class="w-full rounded-md border-gray-300 focus:border-red-600 focus:ring-red-600" required>
        </div>
        <div class="flex-1">
            <label class="block text-sm font-medium text-gray-700 mb-1">End Date</label>
            <input type="date" name="sy_end" class="w-full rounded-md border-gray-300 focus:border-red-600 focus:ring-red-600" required>
        </div>
        <div class="flex items-end">
            <button type="submit" class="bg-red-700 hover:bg-red-800 text-white py-2.5 px-4 rounded-md font-medium transition">
                Add S.Y
            </button>
        </div>
    </div>
</form>

{{-- List of S.Y and Semester --}}
<h3 class="text-lg font-semibold mb-2">All School Years</h3>
<table class="w-full bg-white rounded shadow-md">
    <thead class="bg-red-700 text-white">
        <tr>
            <th class="px-4 py-2 text-left">School Year</th>
            <th class="px-4 py-2 text-left">Semester</th>
            <th class="px-4 py-2 text-left">Status</th>
            <th class="px-4 py-2 text-left">Actions</th>
        </tr>
    </thead>
<tbody>
@foreach($schoolYears as $sy)
<tr class="{{ $sy->id === $latestSchoolYear?->id ? 'bg-green-100 font-semibold' : '' }}">
    <td class="border px-4 py-2">
        {{ $sy->sy_start }} – {{ $sy->sy_end }}
    </td>

    <td class="border px-4 py-2">
        {{ $sy->semesters->pluck('name')->implode(', ') ?: '-' }}
    </td>

    <td class="border px-4 py-2">
        {{ $sy->id === $latestSchoolYear?->id ? 'Active' : '' }}
    </td>

    <td class="border px-4 py-2">
        @if($sy->id === $latestSchoolYear?->id)
            <button
                type="button"
                onclick="openModal({{ $sy->id }})"
                class="bg-red-700 hover:bg-red-800 text-white px-3 py-1 rounded-md transition">
                Add Semester
            </button>
        @else
            -
        @endif
    </td>
</tr>
@endforeach
</tbody>


</table>

{{-- Currently Active S.Y and Semester --}}
@if($latestSchoolYear)
<div class="mt-6 p-4 bg-green-50 text-green-700 rounded border border-green-200">
    <h4 class="font-semibold">Currently Active:</h4>
    <p>
        S.Y:
        <strong>{{ $latestSchoolYear->sy_start }} – {{ $latestSchoolYear->sy_end }}</strong>
    </p>
    <p>
        Semester:
        <strong>{{ $latestSchoolYear->activeSemester?->name ?? 'Not set' }}</strong>
    </p>
</div>
@endif



{{-- Modal --}}
<div id="semesterModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50">
    <div class="bg-white rounded shadow-lg w-96 p-6 relative">
        <button onclick="closeModal()" class="absolute top-2 right-2 text-gray-500 hover:text-gray-700">&times;</button>
        <h3 class="text-lg font-semibold mb-4">Add Semester</h3>
        <p class="mb-4 text-yellow-700">⚠️ Warning: Adding a new semester will end the current active semester. Proceed with caution.</p>
        <form id="modalForm" method="POST" action="">
    @csrf
    <div>
        <label>Semester</label>
        <select name="semester" required>
            <option value="">Select Semester</option>
            @foreach(['1st', '2nd', 'summer'] as $sem)
                @if(!in_array($sem, $existingSemesters))
                    <option value="{{ $sem }}">{{ ucfirst($sem) }} Semester</option>
                @endif
            @endforeach
        </select>
    </div>
    <div>
        <button type="button" onclick="closeModal()">Cancel</button>
        <button type="submit">Add</button>
    </div>
</form>


    </div>
</div>

{{-- JS for modal --}}

<script>
function openModal(id) {
    document.getElementById('semesterModal').classList.remove('hidden');
    document.getElementById('modalForm').action = `/osa/setup/${id}/add-semester`;
}
function closeModal() {
    document.getElementById('semesterModal').classList.add('hidden');
}
</script>

@endsection
