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
        Manage academic years and semester timelines for the Office of Student Affairs.
    </p>
</div>



@if($errors->any())
<div class="mb-6 p-4 rounded-lg border border-red-200 bg-red-50 text-red-800">
    <ul class="text-sm list-disc pl-5">
        @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

@php
    $formatSemesterName = static function (?string $name): string {
        $name = trim((string) $name);
        $normalized = strtolower($name);

        if ($normalized === 'summer' || $normalized === 'summer semester') {
            return 'SUMMER';
        }

        if (preg_match('/^(\d+)(st|nd|rd|th)?(?:\s+semester)?$/i', $normalized, $matches)) {
            $number = (int) $matches[1];
            $suffix = in_array($number % 100, [11, 12, 13], true)
                ? 'th'
                : match ($number % 10) {
                    1 => 'st',
                    2 => 'nd',
                    3 => 'rd',
                    default => 'th',
                };

            return $number . $suffix . ' SEMESTER';
        }

        return $name !== '' ? strtoupper($name) : '';
    };
@endphp

<div class="bg-gradient-to-r from-red-800 via-red-700 to-rose-700 rounded-3xl shadow-lg border border-red-900/10 p-6 mb-10 text-white">
    <div class="flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">
        <div class="max-w-2xl">
            <p class="text-xs uppercase tracking-[0.24em] text-red-100/80">Academic year workflow</p>
            <h3 class="text-2xl font-bold mt-2">Create the next academic year and its semester structure in one step.</h3>
            <p class="text-sm text-red-50/85 mt-3">
                Choose a preset, review the generated semester windows, then create the full academic year with one confirmation.
            </p>
        </div>

        <a href="{{ route('osa.setup.create-academic-setup') }}"
           class="inline-flex items-center justify-center rounded-xl bg-white px-6 py-3 text-sm font-semibold text-red-800 shadow-sm transition hover:bg-red-50">
            New Academic Year
        </a>
    </div>
</div>

<h3 class="text-xl font-semibold text-gray-800 mb-4">Academic Year Records</h3>

@if($schoolYears->isEmpty())
<p class="text-gray-500 italic mb-6">No academic years found. Add a new academic year to get started.</p>
@endif

<div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
@foreach($schoolYears as $sy)
@php
    $isActive = $sy->id === $latestSchoolYear?->id;
    $activeSemester = $sy->semesters->firstWhere('is_active', true);
    $semesterNames = $sy->semesters->pluck('name')->map($formatSemesterName)->all();
    $hasAllSemesters = count(array_intersect(['1st SEMESTER', '2nd SEMESTER', 'SUMMER'], $semesterNames)) === 3;
@endphp

<div class="bg-white rounded-2xl border shadow-sm
            {{ $isActive ? 'border-green-400 ring-2 ring-green-200' : 'border-gray-200' }}">

    <div class="p-5 border-b flex justify-between items-start">
        <div>
            <h4 class="text-2xl font-bold text-gray-800 leading-tight">
                {{ Carbon::parse($sy->sy_start)->year }}–{{ Carbon::parse($sy->sy_end)->year }}
            </h4>
            <p class="text-xs text-gray-500 uppercase tracking-wide mt-1">
                academic year
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
            <div class="grid gap-2 sm:grid-cols-2">
                @foreach($sy->semesters as $semester)
                <div class="rounded-xl border px-3 py-2 text-xs {{ $semester->is_active ? 'border-red-200 bg-red-50 text-red-800' : 'border-gray-200 bg-gray-50 text-gray-700' }}">
                    <div class="flex items-center justify-between gap-3">
                        <span class="font-semibold">{{ $formatSemesterName($semester->name) }}</span>
                        @if($semester->is_active)
                            <span class="rounded-full px-2 py-0.5 font-semibold bg-red-100 text-red-700">
                                ACTIVE
                            </span>
                        @elseif($semester->ended_at)
                            <span class="rounded-full px-2 py-0.5 font-semibold bg-gray-200 text-gray-700">
                                ENDED
                            </span>
                        @endif
                    </div>
                    <div class="mt-1 text-[11px] leading-5 text-inherit/80">
                        @if($semester->starts_at || $semester->will_end_at)
                            <div>
                                @if($semester->starts_at)
                                    Starts {{ Carbon::parse($semester->starts_at)->format('M d, Y') }}
                                @endif
                                @if($semester->starts_at && $semester->will_end_at)
                                    ·
                                @endif
                                @if($semester->will_end_at)
                                    Planned end {{ Carbon::parse($semester->will_end_at)->format('M d, Y') }}
                                @endif
                            </div>
                        @endif
                        @if($semester->ended_at)
                            Ended {{ Carbon::parse($semester->ended_at)->format('M d, Y h:i A') }}
                        @elseif($semester->is_active)
                            @if($semester->will_end_at)
                                Planned to close {{ Carbon::parse($semester->will_end_at)->format('M d, Y') }}.
                            @else
                                Closes when this semester is ended from the action button.
                            @endif
                        @else
                            Ended status not yet recorded.
                        @endif
                    </div>
                </div>
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
                {{ $activeSemester ? $formatSemesterName($activeSemester->name) : 'Not set' }}
            </span>
            @if($activeSemester)
                <div class="mt-1 text-xs text-gray-500">
                    Deadline: {{ $activeSemester->effectiveEndDate() ? Carbon::parse($activeSemester->effectiveEndDate())->format('M d, Y') : 'Not yet closed' }}
                </div>
            @endif
        </div>
    </div>

   <div class="px-5 py-4 border-t bg-gray-50 flex justify-end">
    @if($isActive)
        @if($activeSemester)
            {{-- END SEMESTER --}}
            <form  id="createForm" method="POST" 
                  action="{{ route('osa.setup.end-semester', $sy->id) }}"
                  onsubmit="return confirm('Are you sure you want to end the current semester?')"
                  class="inline">
                @csrf
                <button type="button"
                        onclick="openConfirmModal({
                            title: 'End Semester',
                            message: 'Are you sure you want to end the current semester?',
                            confirmText: 'Confirm',
                            onConfirm: () => document.getElementById('createForm').submit()
                        })"
                        class="bg-gray-700 hover:bg-gray-800 text-white px-4 py-2 rounded-lg text-sm font-medium transition">
                    End Semester
                </button>
            </form>
        @else
            {{-- SHOW START BUTTON FOR NEXT UNSTARTED SEMESTER --}}
            @php
                $nextSemester = $sy->semesters->firstWhere(fn($s) => !$s->is_active && !$s->ended_at);
            @endphp
            @if($nextSemester)
                <form id="createForm" method="POST"
                      action="{{ route('osa.setup.start-semester', ['schoolYear' => $sy->id, 'semester' => $nextSemester->id]) }}"
                      onsubmit="return confirm('Start {{ $formatSemesterName($nextSemester->name) }}?')"
                      class="inline">
                    @csrf
                    <button type="button"
                        onclick="openConfirmModal({
                            title: 'Start Semester',
                            message: 'Start {{ $formatSemesterName($nextSemester->name) }}?',
                            confirmText: 'Confirm',
                            onConfirm: () => document.getElementById('createForm').submit()
                        })"
                            class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition">
                        Start Semester
                    </button>
                </form>
            @else
                @if($hasAllSemesters)
                    {{-- ACADEMIC YEAR COMPLETED --}}
                    <button onclick="openCompletedModal()"
                            class="bg-gray-400 hover:bg-gray-400 text-white px-4 py-2 rounded-lg text-sm font-medium transition opacity-80 cursor-pointer"
                            aria-disabled="true">
                        Academic Year Completed
                    </button>
                @else
                    <span class="text-sm text-gray-400 italic">
                        Semester structure is awaiting action.
                    </span>
                @endif
            @endif
        @endif
    @else
        <span class="text-sm text-gray-400 italic">
            Inactive academic year
        </span>
    @endif
</div>

</div>
@endforeach
</div>

<!-- Completed Academic Year Modal -->
<div id="completedModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center">
    <div class="bg-white rounded-xl shadow-xl w-full max-w-sm p-6 relative">
        <button onclick="closeCompletedModal()"
                class="absolute top-3 right-3 text-gray-400 hover:text-gray-600">✕</button>

        <div class="mx-auto mb-3 flex items-center justify-center w-12 h-12 rounded-full bg-yellow-100">
            <svg class="w-6 h-6 text-yellow-700" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m-1-4h6" />
            </svg>
        </div>

        <h3 class="text-lg font-semibold text-gray-800 mb-1">Academic Year Ended</h3>
        <p class="text-sm text-gray-600 mb-4">Academic Year ended. Please create a new Academic Year to continue.</p>

        <div class="flex justify-end gap-3">
            <button type="button" onclick="closeCompletedModal()"
                    class="px-4 py-2 rounded-lg border text-gray-600 hover:bg-gray-100">
                OK
            </button>
        </div>
    </div>
</div>

<script>
    function openCompletedModal() {
        document.getElementById('completedModal').classList.remove('hidden');
    }

    function closeCompletedModal() {
        document.getElementById('completedModal').classList.add('hidden');
    }
</script>

@endsection
