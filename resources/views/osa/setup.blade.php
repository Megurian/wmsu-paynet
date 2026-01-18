@extends('layouts.dashboard')
@php
    use Carbon\Carbon;
@endphp

@section('title', 'OSA Setup')
@section('page-title', 'OSA Academic Setup')

@section('content')
<div class="mb-8">
    <h2 class="text-3xl font-bold text-gray-800">OSA Academic Setup</h2>
    <p class="text-sm text-gray-500 mt-1">
        Manage school years and semester timelines for the Office of Student Affairs.
    </p>
</div>

{{-- @if(session('status'))
<div  id="successModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40">
    <div class="bg-white rounded-xl shadow-xl w-full max-w-sm p-6 text-center relative animate-fade-in">
        <div class="mx-auto mb-3 flex items-center justify-center w-12 h-12 rounded-full bg-green-100">
            <svg class="w-6 h-6 text-green-700" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
            </svg>
        </div>
        <h3 class="text-lg font-semibold text-gray-800 mb-1"> Success </h3>
        <p class="text-sm text-gray-600 mb-4"> {{ session('status') }} </p>

        <button onclick="closeSuccessModal()" class="bg-green-600 hover:bg-green-700 text-white px-5 py-2 rounded-lg text-sm font-medium transition"> OK </button>
        <button onclick="closeSuccessModal()" class="absolute top-3 right-3 text-gray-400 hover:text-gray-600"> ✕ </button>
    </div>
</div>
@endif --}}


@if($errors->any())
<div class="mb-6 p-4 rounded-lg border border-red-200 bg-red-50 text-red-800">
    <ul class="text-sm list-disc pl-5">
        @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

<div class="bg-white rounded-xl shadow-md border border-gray-200 p-6 mb-10">
    <h3 class="text-lg font-semibold text-gray-800 mb-1">Add New School Year</h3>
    <p class="text-sm text-gray-500 mb-6">
        Define the official academic year duration.
    </p>

    <form method="POST" action="{{ route('osa.setup.store') }}" onsubmit="return confirmNewSchoolYear()">
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

<h3 class="text-xl font-semibold text-gray-800 mb-4">School Year Records</h3>

@if($schoolYears->isEmpty())
<p class="text-gray-500 italic mb-6">No school years found. Add a new school year to get started.</p>
@endif

<div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
@foreach($schoolYears as $sy)
@php
    $isActive = $sy->id === $latestSchoolYear?->id;
    $activeSemester = $sy->semesters->firstWhere('is_active', true);
@endphp

<div class="bg-white rounded-2xl border shadow-sm
            {{ $isActive ? 'border-green-400 ring-2 ring-green-200' : 'border-gray-200' }}">

    <div class="p-5 border-b flex justify-between items-start">
        <div>
            <h4 class="text-2xl font-bold text-gray-800 leading-tight">
                {{ Carbon::parse($sy->sy_start)->year }}–{{ Carbon::parse($sy->sy_end)->year }}
            </h4>
            <p class="text-xs text-gray-500 uppercase tracking-wide mt-1">
                School Year
            </p>
            <p class="text-sm text-gray-400 mt-2">
                {{ Carbon::parse($sy->sy_start)->format('F d, Y') }}
                – 
                {{ Carbon::parse($sy->sy_end)->format('F d, Y') }}
            </p>
        </div>

        @if($isActive)
        <span class="px-3 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-700">
            ACTIVE
        </span>
        @endif
    </div>

    <div class="p-5 space-y-4">

        <div>
            <p class="text-sm font-medium text-gray-600 mb-2">
                Semester History
            </p>

            @if($sy->semesters->count())
            <div class="flex flex-wrap gap-2">
                @foreach($sy->semesters as $semester)
                <span class="px-3 py-1 rounded-full text-xs font-medium
                    {{ $semester->is_active ? 'bg-red-100 text-red-700' : 'bg-gray-100 text-gray-700' }}">
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

        <div class="text-sm">
            <span class="text-gray-500">Current Semester:</span>
            <span class="{{ $activeSemester ? 'text-green-700 font-semibold' : 'text-gray-400' }}">
                {{ $activeSemester?->name ?? 'Not set' }}
            </span>
        </div>
    </div>

   <div class="px-5 py-4 border-t bg-gray-50 flex justify-end">
    @if($isActive)
        @if($activeSemester)
            {{-- END SEMESTER --}}
            <form method="POST"
                  action="{{ route('osa.setup.end-semester', $sy->id) }}"
                  onsubmit="return confirm('Are you sure you want to end the current semester?')">
                @csrf
                <button type="submit"
                        class="bg-gray-700 hover:bg-gray-800 text-white px-4 py-2 rounded-lg text-sm font-medium transition">
                    End Semester
                </button>
            </form>
        @else
            {{-- NEW SEMESTER --}}
            <button onclick="openModal({{ $sy->id }})"
                    class="bg-red-700 hover:bg-red-800 text-white px-4 py-2 rounded-lg text-sm font-medium transition">
                New Semester
            </button>
        @endif
    @else
        <span class="text-sm text-gray-400 italic">
            Inactive School Year
        </span>
    @endif
</div>

</div>
@endforeach
</div>

<div id="semesterModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center">
    <div class="bg-white rounded-xl shadow-xl w-full max-w-md p-6 relative">
        <button onclick="closeModal()"
                class="absolute top-3 right-3 text-gray-400 hover:text-gray-600">✕</button>

        <h3 class="text-lg font-semibold text-gray-800 mb-1">Add New Semester</h3>
        <p class="text-sm text-yellow-700 mb-4">
            Warning: Adding a new semester will deactivate the currently active semester.
        </p>

        <form id="modalForm" method="POST" onsubmit="return confirmNewSemester()">
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

<script>
    function openModal(id) {
        document.getElementById('semesterModal').classList.remove('hidden');
        document.getElementById('modalForm').action = `/osa/setup/${id}/add-semester`;
    }

    function closeModal() {
        document.getElementById('semesterModal').classList.add('hidden');
    }

    // function closeSuccessModal() {
    //     const modal = document.getElementById('successModal');
    //     if (modal) modal.remove();
    // }

    // setTimeout(() => {
    //     closeSuccessModal();
    // }, 3000);

    function confirmNewSchoolYear() {
        const start = document.querySelector('input[name="sy_start"]').value;
        const end = document.querySelector('input[name="sy_end"]').value;

        if (!start || !end) {
            alert('Please select both start and end dates.');
            return false;
        }

        if (start > end) {
            alert('Start date cannot be after the end date.');
            return false;
        }

        return confirm('Are you sure you want to add this new school year? This will deactivate any currently active school year.');
    }

    function confirmNewSemester() {
        const select = document.querySelector('select[name="semester"]');
        if (!select.value) {
            alert('Please select a semester.');
            return false;
        }

        return confirm('Are you sure you want to add this semester? This will deactivate the currently active semester.');
    }
</script>
@endsection
