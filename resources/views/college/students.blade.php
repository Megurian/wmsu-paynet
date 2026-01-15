@extends('layouts.dashboard')

@section('title', 'Student Directory')
@section('page-title', 'Student Directory')

@section('content')
<div class="flex justify-between items-center mb-6">
    <div>
        <h2 class="text-2xl font-bold">Student Directory</h2>
        <p class="text-sm text-gray-600">Manage students under your college</p>
    </div>

    <button onclick="document.getElementById('addStudentModal').classList.remove('hidden')" class="bg-red-800 text-white px-4 py-2 rounded hover:bg-red-600 transition">
        New Student
    </button>

    <a href="{{ route('college.academics') }}" class="text-blue-500">
        Manage Courses / Years / Sections
    </a>
</div>

<!-- Students Table -->
<div class="bg-white rounded-lg shadow p-4">
    <table class="min-w-full text-sm text-left border">
        <thead class="bg-gray-100">
            <tr>
                <th class="px-4 py-2 border">Student ID</th>
                <th class="px-4 py-2 border">Name</th>
                <th class="px-4 py-2 border">Course</th>
                <th class="px-4 py-2 border">Year</th>
                <th class="px-4 py-2 border">Section</th>
                <th class="px-4 py-2 border">Contact</th>
                <th class="px-4 py-2 border">Email</th>
                <th class="px-4 py-2 border">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($students as $student)
            <tr class="border-b">
                 <td class="px-4 py-2 border">{{ $student->student_id }}</td>
                <td class="px-4 py-2 border">{{ $student->last_name }}, {{ $student->first_name }} {{ $student->middle_name }}</td>
                <td class="px-4 py-2 border">{{ $student->course?->name }}</td>
                <td class="px-4 py-2 border">{{ $student->yearLevel?->name }}</td>
                <td class="px-4 py-2 border">{{ $student->section?->name }}</td>
                <td class="px-4 py-2 border">{{ $student->contact ?? '-' }}</td>
                <td class="px-4 py-2 border">{{ $student->email ?? '-' }}</td>
                <td class="px-4 py-2 border">
                    <form action="{{ route('college.students.destroy', $student->id) }}" method="POST" onsubmit="return confirm('Are you sure?')">
                        @csrf
                        @method('DELETE')
                        <button class="text-red-600 hover:text-red-800 text-sm">Delete</button>
                    </form>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="px-4 py-2 text-gray-400 text-center italic">No students yet</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<!-- Add Student Modal -->
<div id="addStudentModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden">
    <div class="bg-white rounded-lg shadow p-6 w-full max-w-md relative">
        <button onclick="document.getElementById('addStudentModal').classList.add('hidden')" class="absolute top-2 right-2 text-gray-500 hover:text-gray-800">&times;</button>

        <h3 class="text-lg font-semibold mb-4">New Student</h3>

        <form method="POST" action="{{ route('college.students.store') }}" class="space-y-3">
            @csrf
            <input type="text" name="student_id" required placeholder="Student ID" class="w-full border rounded px-3 py-2 text-sm">
            <input type="text" name="last_name" required placeholder="Last Name" class="w-full border rounded px-3 py-2 text-sm">
            <input type="text" name="first_name" required placeholder="First Name" class="w-full border rounded px-3 py-2 text-sm">
            <input type="text" name="middle_name" required placeholder="Middle Name" class="w-full border rounded px-3 py-2 text-sm">

            <select name="suffix" class="w-full border rounded px-3 py-2 text-sm">
                <option value="">Select Suffix (Optional)</option>
                <option value="Jr.">Jr.</option>
                <option value="Sr.">Sr.</option>
                <option value="II">II</option>
                <option value="III">III</option>
                <option value="IV">IV</option>
                <option value="V">V</option>
            </select>

            <select name="course_id" required class="w-full border rounded px-3 py-2 text-sm">
                <option value="">Select Course</option>
                @foreach($courses as $course)
                <option value="{{ $course->id }}">{{ $course->name }}</option>
                @endforeach
            </select>

            <select name="year_level_id" required class="w-full border rounded px-3 py-2 text-sm">
                <option value="">Select Year Level</option>
                @foreach($years as $year)
                <option value="{{ $year->id }}">{{ $year->name }}</option>
                @endforeach
            </select>

            <select name="section_id" required class="w-full border rounded px-3 py-2 text-sm">
                <option value="">Select Section</option>
                @foreach($sections as $section)
                <option value="{{ $section->id }}">{{ $section->name }}</option>
                @endforeach
            </select>

            <input type="text" name="contact" placeholder="Contact (Optional)" class="w-full border rounded px-3 py-2 text-sm">
            <input type="email" name="email" placeholder="Email (Optional)" class="w-full border rounded px-3 py-2 text-sm">

            <button type="submit" class="w-full bg-blue-600 text-white py-2 rounded hover:bg-blue-700 transition">Add Student</button>
        </form>

    </div>
</div>
@endsection
