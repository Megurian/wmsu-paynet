@extends('layouts.dashboard')

@section('title', 'Student History')
@section('page-title', 'Student History')

@section('content')
{{-- 
<div class="mb-6">
    <h2 class="text-2xl font-semibold text-gray-800">Student History</h2>
    <p class="text-sm text-gray-500 mt-1">
        View historical enrollment records filtered by school year and semester.
    </p>
</div> --}}

<div class="bg-white border border-gray-200 rounded-xl shadow-sm p-4 mb-6">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-6">
        @if($selectedSchoolYear)
        <div class="md:w-1/2">
            <div class="flex items-start gap-3 bg-gray-50 border border-gray-200 rounded-lg p-4">
                <div>
                    <p class="text-sm font-semibold text-gray-800">
                        AY {{ \Carbon\Carbon::parse($selectedSchoolYear->sy_start)->year }}
                        –
                        {{ \Carbon\Carbon::parse($selectedSchoolYear->sy_end)->year }}
                        • {{ ucfirst($selectedSem) }} Semester
                    </p>

                    <p class="text-xs text-gray-500 mt-1">
                        {{ \Carbon\Carbon::parse($selectedSchoolYear->sy_start)->format('F d, Y') }}
                        –
                        {{ \Carbon\Carbon::parse($selectedSchoolYear->sy_end)->format('F d, Y') }}
                    </p>
                </div>
            </div>
        </div>
        @endif

        <form method="GET" class="grid grid-cols-1 sm:grid-cols-3 gap-4 md:w-1/2 items-end">
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">
                    School Year
                </label>
                <select
                    name="school_year"
                    onchange="this.form.submit()"
                    class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm
                           focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition">
                    <option value="">All School Years</option>
                    @foreach($schoolYears as $sy)
                        <option value="{{ $sy->id }}" @selected($selectedSY == $sy->id)>
                            {{ \Carbon\Carbon::parse($sy->sy_start)->year }}
                            –
                            {{ \Carbon\Carbon::parse($sy->sy_end)->year }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">
                    Semester
                </label>
                <select
                    name="semester"
                    onchange="this.form.submit()"
                    class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm
                           focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition"
                >
                    @foreach(['1st', '2nd', 'summer'] as $semName)
                        <option value="{{ $semName }}" @selected($selectedSem == $semName)>
                            {{ ucfirst($semName) }} Semester
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="flex sm:justify-end">
                <a href="{{ route(request()->route()->getName()) }}"
                   class="text-sm text-gray-500 hover:text-gray-700 transition">
                    Reset
                </a>
            </div>
        </form>
    </div>

    {{-- Sub-tabs (Pill badges) --}}
    <div class="mt-4 border-b border-gray-200">
        <nav class="-mb-px flex space-x-4" aria-label="Tabs">
            <a href="{{ route('college.history', array_merge(request()->query(), ['tab' => 'enrollments'])) }}"
               class="px-3 py-2 font-medium text-sm rounded-t-lg
                      @if(request('tab', 'enrollments') === 'enrollments')
                          bg-blue-100 text-blue-700
                      @else
                          text-gray-500 hover:text-gray-700
                      @endif
                      ">
                Student Enrollments
            </a>

            <a href="{{ route('college.history', array_merge(request()->query(), ['tab' => 'payments'])) }}"
               class="px-3 py-2 font-medium text-sm rounded-t-lg
                      @if(request('tab') === 'payments')
                          bg-blue-100 text-blue-700
                      @else
                          text-gray-500 hover:text-gray-700
                      @endif
                      ">
                Payments
            </a>
        </nav>
    </div>
</div>


<div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">

    @if($students->isEmpty())
        <div class="p-8 text-center">
            <p class="text-gray-500 text-sm">
                No student history found for the selected filters.
            </p>
            <p class="text-xs text-gray-400 mt-1">
                Try adjusting the school year or semester.
            </p>
        </div>
    @else
        <table class="min-w-full text-sm">
    <thead class="bg-gray-50 border-b border-gray-200">
        <tr class="text-left text-[11px] font-semibold uppercase tracking-wide text-gray-600">
            <th class="px-4 py-3 text-center">#</th>
            <th class="px-5 py-3">Student ID</th>
            <th class="px-5 py-3">Name</th>
            <th class="px-5 py-3">Course</th>
            <th class="px-5 py-3">Year & Section</th>
            <th class="px-5 py-3">Adviser</th>
            <th class="px-5 py-3">Status</th>
            <th class="px-5 py-3"> </th>
        </tr>
    </thead>

    <tbody class="divide-y divide-gray-100 text-gray-700">
        @foreach($students as $student)
            <tr class="hover:bg-gray-50 transition">
                <td class="px-4 py-3 text-center text-gray-500">
                    {{ $loop->iteration }}
                </td>
                <td class="px-5 py-3 font-medium">
                    {{ $student->student->student_id }}
                </td>
                <td class="px-5 py-3">
                    {{ strtoupper($student->student->last_name) }},
                    {{ strtoupper($student->student->first_name) }}
                    {{ strtoupper($student->student->middle_name) }}.
                    {{ strtoupper($student->student->suffix) }}
                </td>
                <td class="px-5 py-3">
                    {{ $student->course?->name ?? '—' }}
                </td>
                <td class="px-5 py-3">
                    {{ $student->yearLevel?->name ?? '—' }} {{ $student->section?->name ?? '—' }}
                </td>
                <td class="px-5 py-3">
                    {{ $student->adviser?->last_name ?? '—' }}
                </td>
                <td class="px-5 py-3">
                    @php
                        if($student->assessed_at) {
                            $status = 'Assessed';
                            $badgeColor = 'bg-green-100 text-green-700';
                        } elseif($student->validated_at) {
                            $status = 'To be Assessed';
                            $badgeColor = 'bg-yellow-100 text-yellow-700';
                        } elseif($student->advised_at) {
                            $status = 'Pending Payment';
                            $badgeColor = 'bg-blue-100 text-blue-700';
                        } else {
                            $status = 'Not Enrolled';
                            $badgeColor = 'bg-gray-100 text-gray-500';
                        }
                    @endphp
                    <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $badgeColor }}">
                        {{ $status }}
                    </span>
                </td>
                <td class="px-5 py-3">
                    <a href="{{ route('college.students.history', $student->student->id) }}"
                    class="inline-block px-3 py-1 text-xs font-semibold text-white bg-blue-600 rounded hover:bg-blue-700 transition">
                    View
                    </a>
                </td>
            </tr>
        @endforeach
    </tbody>
</table>
    @endif

</div>

@endsection
