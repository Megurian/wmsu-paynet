@extends('layouts.dashboard')

@section('title', 'Student History')
@section('page-title', 'Student History')

@section('content')
<div class="flex flex-col md:flex-row items-center justify-between mb-6 gap-4">
    <h2 class="text-2xl font-bold text-gray-800">Student History</h2>
</div>

<div class="bg-white shadow rounded-lg p-4 mb-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
    <form method="GET" class="flex flex-wrap gap-3 flex-1 items-center">
        <select name="school_year" class="rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition" onchange="this.form.submit()">
            <option value="">All School Years</option>
            @foreach($schoolYears as $sy)
            <option value="{{ $sy->id }}" @selected($selectedSY == $sy->id)>
                {{ \Carbon\Carbon::parse($sy->sy_start)->year }}
                –
                {{ \Carbon\Carbon::parse($sy->sy_end)->year }}
            </option>

            @endforeach
        </select>

        <select name="semester" class="rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition" onchange="this.form.submit()">
            <option value="">All Semesters</option>
            @foreach(['1st', '2nd', 'summer'] as $semName)
            <option value="{{ $semName }}" @selected($selectedSem==$semName)>
                {{ ucfirst($semName) }}
            </option>
            @endforeach
        </select>



    </form>
</div>

<div class="bg-white shadow-sm border border-gray-200 rounded-xl overflow-hidden">
    @if($students->isEmpty())
    <div class="p-6 text-center text-gray-500">
        No student history found for the selected school year and semester.
    </div>
    @else
    <table class="min-w-full text-sm text-gray-800">
        <thead class="bg-gray-50 border-b border-gray-200">
            <tr class="text-left text-[11px] font-semibold uppercase tracking-wide text-gray-600">
                <th class="px-5 py-3">Student ID</th>
                <th class="px-5 py-3">Name</th>
                <th class="px-5 py-3">Course</th>
                <th class="px-5 py-3">Year</th>
                <th class="px-5 py-3">Section</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @foreach($students as $student)
            <tr class="hover:bg-gray-50">
                <td class="px-5 py-2">{{ $student->student->student_id }}</td>
                <td class="px-5 py-2">{{ strtoupper($student->student->last_name) }}, {{ strtoupper($student->student->first_name) }}</td>
                <td class="px-5 py-2">{{ $student->course?->name ?? '—' }}</td>
                <td class="px-5 py-2">{{ $student->yearLevel?->name ?? '—' }}</td>
                <td class="px-5 py-2">{{ $student->section?->name ?? '—' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif
</div>
@endsection
