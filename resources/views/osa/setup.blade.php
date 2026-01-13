@extends('layouts.dashboard')

@section('title', 'OSA Setup')
@section('page-title', 'OSA Academic Setup')

@section('content')
<div class="mb-8">
    <h2 class="text-3xl font-bold text-gray-800">OSA Academic Setup</h2>
    <p class="text-sm text-gray-500 mt-1">
        Manage school years and semester timelines for the Office of Student Affairs.
    </p>
</div>


@if(session('status'))
<div class="mb-6 flex items-start gap-3 p-4 rounded-lg border border-green-200 bg-green-50 text-green-800">
    
    <div class="text-sm font-medium">
        {{ session('status') }}
    </div>
</div>
@endif

{{-- Currently Active S.Y and Semester
@if($latestSchoolYear)
<div class="mt-10 bg-green-50 border border-green-200 rounded-xl p-6">
    <h4 class="font-semibold text-green-800 mb-2">
         Currently Active Academic Period
    </h4>
    <p class="text-sm text-green-700">
        <strong>School Year:</strong>
        {{ $latestSchoolYear->sy_start }} – {{ $latestSchoolYear->sy_end }}
    </p>
    <p class="text-sm text-green-700 mt-1">
        <strong>Semester:</strong>
        {{ $latestSchoolYear->activeSemester?->name ?? 'Not set' }}
    </p>
</div>
@endif --}}

{{-- Add School Year --}}
<div class="bg-white rounded-xl shadow-md border border-gray-200 p-6 mb-10">
    <h3 class="text-lg font-semibold text-gray-800 mb-1">Add New School Year</h3>
    <p class="text-sm text-gray-500 mb-6">
        Define the official academic year duration.
    </p>

    <form method="POST" action="{{ route('osa.setup.store') }}">
        @csrf
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Start Date
                </label>
                <input type="date" name="sy_start"
                       class="w-full rounded-lg border-gray-300 focus:border-red-600 focus:ring-red-600"
                       required>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    End Date
                </label>
                <input type="date" name="sy_end"
                       class="w-full rounded-lg border-gray-300 focus:border-red-600 focus:ring-red-600"
                       required>
            </div>

            <button type="submit"
                    class="bg-red-700 hover:bg-red-800 text-white px-6 py-2.5 rounded-lg font-medium transition">
               New School Year
            </button>
        </div>
    </form>
</div>


{{-- List of S.Y and Semester --}}
<h3 class="text-xl font-semibold text-gray-800 mb-4">School Year Records</h3>

<div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
@foreach($schoolYears as $sy)
@php
    $isActive = $sy->id === $latestSchoolYear?->id;
    $activeSemester = $sy->semesters->firstWhere('is_active', true);
@endphp

<div class="bg-white rounded-2xl border shadow-sm
            {{ $isActive ? 'border-green-400 ring-2 ring-green-200' : 'border-gray-200' }}">

    {{-- Card Header --}}
    <div class="p-5 border-b flex justify-between items-start">
        <div>
            <h4 class="text-lg font-bold text-gray-800">
                {{ $sy->sy_start }} – {{ $sy->sy_end }}
            </h4>
            <p class="text-xs text-gray-500 uppercase tracking-wide mt-1">
                School Year
            </p>
        </div>

        @if($isActive)
        <span class="px-3 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-700">
            ACTIVE
        </span>
        @endif
    </div>

    {{-- Card Body --}}
    <div class="p-5 space-y-4">

        {{-- Semester History --}}
        <div>
            <p class="text-sm font-medium text-gray-600 mb-2">
                Semester History
            </p>

            @if($sy->semesters->count())
            <div class="flex flex-wrap gap-2">
                @foreach($sy->semesters as $semester)
                <span class="px-3 py-1 rounded-full text-xs font-medium
                    {{ $semester->is_active
                        ? 'bg-red-100 text-red-700'
                        : 'bg-gray-100 text-gray-700' }}">
                    {{ ucfirst($semester->name) }}
                </span>
                @endforeach
            </div>
            @else
            <p class="text-sm text-gray-400 italic">
                No semesters added yet
            </p>
            @endif
        </div>

        {{-- Current Semester --}}
        <div class="text-sm">
            <span class="text-gray-500">Current Semester:</span>
            <span class="{{ $activeSemester ? 'text-green-700 font-semibold' : 'text-gray-400' }}">
                {{ $activeSemester?->name ?? 'Not set' }}
            </span>
        </div>
    </div>

    {{-- Card Footer --}}
    <div class="px-5 py-4 border-t bg-gray-50 flex justify-end">
        @if($isActive)
        <button onclick="openModal({{ $sy->id }})"
                class="bg-red-700 hover:bg-red-800 text-white px-4 py-2 rounded-lg text-sm font-medium transition">
            New Semester
        </button>
        @else
        <span class="text-sm text-gray-400 italic">
            Inactive School Year
        </span>
        @endif
    </div>
</div>
@endforeach
</div>






{{-- Modal --}}
<div id="semesterModal"
     class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center">

    <div class="bg-white rounded-xl shadow-xl w-full max-w-md p-6 relative">
        <button onclick="closeModal()"
                class="absolute top-3 right-3 text-gray-400 hover:text-gray-600">
            ✕
        </button>

        <h3 class="text-lg font-semibold text-gray-800 mb-1">
            Add New Semester
        </h3>
        <p class="text-sm text-yellow-700 mb-4">
            Note: This will end the currently active semester.
        </p>

        <form id="modalForm" method="POST">
            @csrf
            <label class="block text-sm font-medium text-gray-700 mb-1">
                Semester
            </label>
            <select name="semester"
                    class="w-full rounded-lg border-gray-300 mb-6"
                    required>
                <option value="">Select Semester</option>
                @foreach(['1st','2nd','summer'] as $sem)
                    @if(!in_array($sem, $existingSemesters))
                        <option value="{{ $sem }}">
                            {{ ucfirst($sem) }} Semester
                        </option>
                    @endif
                @endforeach
            </select>

            <div class="flex justify-end gap-3">
                <button type="button" onclick="closeModal()"
                        class="px-4 py-2 rounded-lg border text-gray-600 hover:bg-gray-100">
                    Cancel
                </button>
                <button type="submit"
                        class="bg-red-700 hover:bg-red-800 text-white px-5 py-2 rounded-lg font-medium">
                    Confirm
                </button>
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
