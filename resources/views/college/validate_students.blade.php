@extends('layouts.dashboard')

@section('title', 'Validate Students')
@section('page-title', 'Validate Students')

@section('content')
    <h2 class="text-2xl font-bold mb-4">Validate Students</h2>
    <p>Welcome, {{ Auth::user()->name }}. Here you can review student requests and approvals.</p>
<div>
        <a href="{{ route('college.students') }}" 
           class="inline-block mb-2 px-4 py-2 bg-red-800 text-white rounded-lg shadow hover:bg-red-700 transition">
            &larr; Back
        </a>
    </div>

    <table class="min-w-full text-sm text-gray-800">
    <thead class="bg-gray-50">
        <tr class="uppercase text-xs font-semibold text-gray-600">
            <th>Student ID</th>
            <th>Name</th>
            <th>Course</th>
            <th>Year</th>
            <th>Section</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        @foreach($students as $student)
        <tr>
            <td>{{ $student->student_id }}</td>
            <td>{{ strtoupper($student->last_name) }}, {{ strtoupper($student->first_name) }} {{ strtoupper($student->middle_name ?? '') }} {{ $student->suffix ?? '' }}</td>
            <td>
                <form method="POST" action="{{ route('college.students.validate.store', $student->id) }}">
                    @csrf
                    <select name="course_id" required>
                        @foreach($courses as $course)
                        <option value="{{ $course->id }}">{{ $course->name }}</option>
                        @endforeach
                    </select>
            </td>
            <td>
                <select name="year_level_id" required>
                    @foreach($years as $year)
                    <option value="{{ $year->id }}">{{ $year->name }}</option>
                    @endforeach
                </select>
            </td>
            <td>
                <select name="section_id" required>
                    @foreach($sections as $section)
                    <option value="{{ $section->id }}">{{ $section->name }}</option>
                    @endforeach
                </select>
            </td>
            <td>
                <button type="submit" class="px-3 py-1 bg-green-600 text-white rounded hover:bg-green-500">Validate</button>
                </form>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>

@endsection
