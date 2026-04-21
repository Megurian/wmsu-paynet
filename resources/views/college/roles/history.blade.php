@extends('layouts.dashboard')

@section('title', 'Manage College')
@section('page-title', 'College Management')

@section('content')

<div class="bg-white rounded-xl shadow-sm border p-6 space-y-6">

    <div>
        <h2 class="text-xl font-bold text-gray-800">Role History</h2>
        <p class="text-sm text-gray-500">
            Audit trail of all role assignments (never deleted)
        </p>
    </div>

    <form method="GET" class="flex gap-3 text-sm">

    <select name="school_year_id" class="border rounded px-3 py-2">
        <option value="">All School Years</option>

        @foreach($schoolYears as $sy)
            <option value="{{ $sy->id }}"
                {{ request('school_year_id') == $sy->id ? 'selected' : '' }}>
                {{ $sy->sy_start->format('Y') }}-{{ $sy->sy_end->format('Y') }}
            </option>
        @endforeach
    </select>

    <select name="semester_id" class="border rounded px-3 py-2">
        <option value="">All Semesters</option>

        <option value="1st Semester"
            {{ request('semester_id') == '1st Semester' ? 'selected' : '' }}>
            1st Semester
        </option>

        <option value="2nd Semester"
            {{ request('semester_id') == '2nd Semester' ? 'selected' : '' }}>
            2nd Semester
        </option>

        <option value="Summer"
            {{ request('semester_id') == 'Summer' ? 'selected' : '' }}>
            Summer
        </option>
    </select>

    <button class="bg-red-800 text-white px-4 py-2 rounded">
        Filter
    </button>

    @if(request()->has('school_year_id') || request()->has('semester_id'))
        <a href="{{ route('college.roles.history') }}"
           class="px-3 py-2 text-sm text-gray-600 underline">
            Reset
        </a>
    @endif

</form>
    <div class="overflow-x-auto border rounded-lg">
        <table class="w-full text-sm">

            <thead class="bg-gray-50 text-xs uppercase text-gray-600">
                <tr>
                    <th class="px-4 py-3 text-left">Employee</th>
                    <th class="px-4 py-3 text-left">Role</th>
                    <th class="px-4 py-3 text-center">Semester</th>
                    <th class="px-4 py-3 text-center">School Year</th>
                </tr>
            </thead>

            <tbody class="divide-y">

@foreach($history as $row)
    <tr class="hover:bg-gray-50">

        <td class="px-4 py-2 font-medium text-gray-800">
            {{ $row->employee->first_name }} {{ $row->employee->last_name }}
        </td>

        <td class="px-4 py-2">
            @foreach($row->roles as $role)
                <span class="px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-700">
                    {{ ucfirst(str_replace('_', ' ', $role)) }}
                </span>
            @endforeach
        </td>

        <td class="px-4 py-2 text-center">
            {{ $row->semester->name }}
        </td>

        <td class="px-4 py-2 text-center">
            {{ $row->schoolYear->sy_start->format('Y') }}
            -
            {{ $row->schoolYear->sy_end->format('Y') }}
        </td>

    </tr>
@endforeach

</tbody>

        </table>
    </div>

</div>

@endsection