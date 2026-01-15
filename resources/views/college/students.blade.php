@extends('layouts.dashboard')

@section('title', 'Student Directory')
@section('page-title', 'Student Directory')

@section('content')
<div class="max-w-6xl mx-auto space-y-6">

    {{-- Header --}}
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <h1 class="text-3xl font-bold text-gray-800">Student Directory</h1>
            <p class="text-gray-600 mt-1">Manage all students under your college. Add, view, or remove students as needed.</p>
        </div>

        <div class="flex flex-wrap gap-3">
            <button onclick="document.getElementById('addStudentModal').classList.remove('hidden')"
                    class="bg-red-800 text-white px-4 py-2 rounded hover:bg-red-700 transition">
                + New Student
            </button>

            <a href="{{ route('college.academics') }}"
               class="text-blue-500 px-4 ">
                Manage Courses / Years / Sections
            </a>
        </div>
    </div>

    {{-- Students Table --}}
    <div class="bg-white rounded-xl shadow overflow-x-auto">
        <table class="min-w-full text-sm text-left">
            <thead class="bg-gray-100">
                <tr class="text-gray-700 uppercase text-xs">
                    <th class="px-6 py-3 rounded-tl-xl">Student ID</th>
                    <th class="px-6 py-3">Name</th>
                    <th class="px-6 py-3">Course</th>
                    <th class="px-6 py-3">Year</th>
                    <th class="px-6 py-3">Section</th>
                    <th class="px-6 py-3">Contact</th>
                    <th class="px-6 py-3">Email</th>
                    <th class="px-6 py-3 rounded-tr-xl">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($students as $student)
                <tr class="hover:bg-gray-50 transition">
                    <td class="px-6 py-3">{{ $student->student_id }}</td>
                    <td class="px-6 py-3">{{ $student->last_name }}, {{ $student->first_name }} {{ $student->middle_name }} {{ $student->suffix }}</td>
                    <td class="px-6 py-3">{{ $student->course?->name ?? '-' }}</td>
                    <td class="px-6 py-3">{{ $student->yearLevel?->name ?? '-' }}</td>
                    <td class="px-6 py-3">{{ $student->section?->name ?? '-' }}</td>
                    <td class="px-6 py-3">{{ $student->contact ?? '-' }}</td>
                    <td class="px-6 py-3">{{ $student->email ?? '-' }}</td>
                    <td class="px-6 py-3">
                        <form action="{{ route('college.students.destroy', $student->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this student?')">
                            @csrf
                            @method('DELETE')
                            <button class="text-red-600 hover:text-red-800 text-sm font-medium">Delete</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="px-6 py-4 text-center text-gray-400 italic">No students added yet.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Add Student Modal --}}
    <div id="addStudentModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50">
        <div class="bg-white rounded-xl shadow-lg p-6 w-full max-w-lg relative">
            <button onclick="document.getElementById('addStudentModal').classList.add('hidden')"
                    class="absolute top-4 right-4 text-gray-400 hover:text-gray-700 text-2xl">&times;</button>

            <h2 class="text-2xl font-bold text-gray-800 mb-2">Add New Student</h2>
            <p class="text-gray-600 text-sm mb-4">Fill out the form below to add a student. Fields marked with <span class="text-red-500">*</span> are required.</p>

            <form method="POST" action="{{ route('college.students.store') }}" class="space-y-4">
                @csrf

                {{-- Identification --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700">Student ID <span class="text-red-500">*</span></label>
                    <input type="text" name="student_id" required placeholder="e.g. 2026-001" class="w-full border rounded px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-blue-500">
                    @error('student_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- Name --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Last Name <span class="text-red-500">*</span></label>
                        <input type="text" name="last_name" required class="w-full border rounded px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-blue-500">
                        @error('last_name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">First Name <span class="text-red-500">*</span></label>
                        <input type="text" name="first_name" required class="w-full border rounded px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-blue-500">
                        @error('first_name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Middle Name <span class="text-red-500">*</span></label>
                        <input type="text" name="middle_name" class="w-full border rounded px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-blue-500">
                        @error('middle_name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Suffix</label>
                        <select name="suffix" class="w-full border rounded px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-blue-500">
                            <option value="">Select Suffix (Optional)</option>
                            <option value="Jr.">Jr.</option>
                            <option value="Sr.">Sr.</option>
                            <option value="II">II</option>
                            <option value="III">III</option>
                            <option value="IV">IV</option>
                            <option value="V">V</option>
                        </select>
                        @error('suffix') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                {{-- Academic --}}
                <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Course <span class="text-red-500">*</span></label>
                        <select name="course_id" required class="w-full border rounded px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-blue-500">
                            <option value="">Select Course</option>
                            @foreach($courses as $course)
                            <option value="{{ $course->id }}">{{ $course->name }}</option>
                            @endforeach
                        </select>
                        @error('course_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Year Level <span class="text-red-500">*</span></label>
                        <select name="year_level_id" required class="w-full border rounded px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-blue-500">
                            <option value="">Select Year Level</option>
                            @foreach($years as $year)
                            <option value="{{ $year->id }}">{{ $year->name }}</option>
                            @endforeach
                        </select>
                        @error('year_level_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Section <span class="text-red-500">*</span></label>
                        <select name="section_id" required class="w-full border rounded px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-blue-500">
                            <option value="">Select Section</option>
                            @foreach($sections as $section)
                            <option value="{{ $section->id }}">{{ $section->name }}</option>
                            @endforeach
                        </select>
                        @error('section_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                {{-- Contact Info --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Contact</label>
                        <input type="text" name="contact" placeholder="Optional" class="w-full border rounded px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-blue-500">
                        @error('contact') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Email</label>
                        <input type="email" name="email" placeholder="Optional" class="w-full border rounded px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-blue-500">
                        @error('email') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <button type="submit" class="w-full bg-red-800 text-white py-2 rounded hover:bg-red-700 transition font-medium">Add Student</button>
            </form>
        </div>
    </div>
</div>
@endsection
