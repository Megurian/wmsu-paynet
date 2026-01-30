@extends('layouts.dashboard')

@section('page-title', 'My Students Upload')

@section('content')

<div x-data="{ showModal: false }">
        <div class="flex items-center gap-4">
            
            <button @click="showModal = true" class="bg-red-800 text-white px-4 py-2 rounded hover:bg-red-700 transition">
                New Student
            </button>
        </div>

           {{-- New Student Modal --}}
    <div x-show="showModal" x-cloak class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div @click.away="showModal = false" class="bg-white rounded-lg shadow-lg w-full max-w-lg relative p-6">
            <button @click="showModal = false" class="absolute top-2 right-3 text-gray-500 hover:text-gray-800 text-xl">&times;</button>

            <h3 class="text-lg font-semibold mb-4 border-b pb-2">New Student</h3>

            <form method="POST" action="{{ route('college.students.my-upload.store') }}" class="space-y-4">
                @csrf
                
            <div>
                <label class="text-sm font-medium">
                    Student ID <span class="text-red-500">*</span>
                </label>

                <input
                    type="text" name="student_id"  value="{{ old('student_id') }}"  required class="w-full rounded border px-3 py-2 text-sm  @error('student_id') border-red-500 focus:ring-red-200 @enderror" placeholder="Enter student ID">

                @error('student_id')
                    <p class="text-red-600 text-xs mt-1">
                        {{ $message }}
                    </p>
                @enderror
            </div>


            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                <div>
                    <label class="text-sm font-medium">
                        Last Name <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="last_name" required placeholder="Enter last name" class="w-full border rounded px-3 py-2 text-sm" >
                </div>

                <div>
                    <label class="text-sm font-medium">
                        First Name <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="first_name" required placeholder="Enter first name" class="w-full border rounded px-3 py-2 text-sm">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                <div>
                    <label class="text-sm font-medium">
                        Middle Name <span class="text-red-500"></span>
                    </label>
                    <input type="text" name="middle_name" placeholder="Enter middle name" class="w-full border rounded px-3 py-2 text-sm">
                </div>

                <div>
                    <label class="text-sm font-medium">Suffix</label>
                    <select
                        name="suffix"
                        class="w-full border rounded px-3 py-2 text-sm"
                    >
                        <option value="">None</option>
                        <option value="Jr.">Jr.</option>
                        <option value="Sr.">Sr.</option>
                        <option value="II">II</option>
                        <option value="III">III</option>
                        <option value="IV">IV</option>
                        <option value="V">V</option>
                    </select>
                </div>
            </div>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                    <div>
                        <label class="text-sm font-medium">Course <span class="text-red-500">*</span></label>
                        <select name="course_id" required class="w-full border rounded px-3 py-2 text-sm">
                            <option value="">Select Course</option>
                            @foreach($courses as $course)
                            <option value="{{ $course->id }}">{{ $course->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="text-sm font-medium">Year Level <span class="text-red-500">*</span></label>
                        <select name="year_level_id" required class="w-full border rounded px-3 py-2 text-sm">
                            <option value="">Select Year Level</option>
                            @foreach($years as $year)
                            <option value="{{ $year->id }}">{{ $year->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="text-sm font-medium">Section <span class="text-red-500">*</span></label>
                        <select name="section_id" required class="w-full border rounded px-3 py-2 text-sm">
                            <option value="">Select Section</option>
                            @foreach($sections as $section)
                            <option value="{{ $section->id }}">{{ $section->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <div>
                        <label class="text-sm font-medium">Contact</label>
                        <input type="text" name="contact" placeholder="Optional" class="w-full border rounded px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="text-sm font-medium">Email</label>
                        <input type="email" name="email" placeholder="Optional" class="w-full border rounded px-3 py-2 text-sm">
                    </div>
                </div>
                <button type="submit" class="w-full bg-red-800 text-white py-2 rounded hover:bg-red-700 transition">
                    Add Student
                </button>
            </form>
        </div>
    </div>

<table class="min-w-full table-auto mt-4">
        <thead>
            <tr>
                <th>Student ID</th>
                <th>Name</th>
                <th>Course</th>
                <th>Year</th>
                <th>Section</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            @foreach($students as $enrollment)
            <tr>
                <td>{{ $enrollment->student->student_id }}</td>
                <td>{{ $enrollment->student->last_name }}, {{ $enrollment->student->first_name }}</td>
                <td>{{ $enrollment->course?->name }}</td>
                <td>{{ $enrollment->yearLevel?->name }}</td>
                <td>{{ $enrollment->section?->name }}</td>
                <td>{{ $enrollment->status }}</td>
                <td>
                    @if($enrollment->status !== 'ENROLLED')
                    <form method="POST" action="{{ route('college.students.my-upload.readd', $enrollment->student->id) }}">
                        @csrf
                        <button type="submit" class="bg-green-600 text-white px-3 py-1 rounded hover:bg-green-500">
                            Re-add
                        </button>
                    </form>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

@endsection
