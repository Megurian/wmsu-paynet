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
                    <th class="px-4 py-3 text-center"></th>
                    <th class="px-5 py-3">Student ID</th>
                    <th class="px-5 py-3">Name</th>
                    <th class="px-5 py-3">Course</th>
                    <th class="px-5 py-3">Year & Section</th>
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
                            {{ strtoupper($student->student->last_name) }}, {{ strtoupper($student->student->first_name) }} {{ strtoupper($student->student->middle_name) }}. {{ strtoupper($student->student->suffix) }}
                        </td>
                        <td class="px-5 py-3">
                            {{ $student->course?->name ?? '—' }}
                        </td>
                        <td class="px-5 py-3">
                            {{ $student->yearLevel?->name ?? '—' }}  {{ $student->section?->name ?? '—' }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

</div>

@endsection
