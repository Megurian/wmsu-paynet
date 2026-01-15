@extends('layouts.dashboard')

@section('title', 'Student Directory')
@section('page-title', 'Student Directory')

@section('content')
<div class="max-w-6xl mx-auto space-y-6" x-data="studentDirectory()">

    {{-- Header --}}
    <div class="flex justify-between items-center border-b border-gray-300 pb-3 mb-4">
        <div>
            <h2 class="text-2xl font-bold">Student Directory</h2>
            <p class="text-gray-600 text-sm">Manage students under your college</p>
        </div>
        <div class="flex items-center gap-4">
            <button @click="showModal = true" class="bg-red-800 text-white px-4 py-2 rounded hover:bg-red-700 transition">
                + Add Student
            </button>
            <a href="{{ route('college.academics') }}" class="text-blue-600 hover:underline text-sm">
                Manage Courses / Years / Sections
            </a>
        </div>
    </div>

    {{-- Filters --}}
    <div class="flex flex-wrap gap-3 mb-4">
        <input type="text" x-model="search" placeholder="Search by name or student ID" class="flex-1 border rounded px-3 py-2 text-sm">
        <select x-model="filterCourse" class="border rounded px-3 py-2 text-sm">
            <option value="">Filter by Course</option>
            @foreach($courses as $course)
            <option value="{{ $course->id }}">{{ $course->name }}</option>
            @endforeach
        </select>
        <select x-model="filterYear" class="border rounded px-3 py-2 text-sm">
            <option value="">Filter by Year Level</option>
            @foreach($years as $year)
            <option value="{{ $year->id }}">{{ $year->name }}</option>
            @endforeach
        </select>
        <select x-model="filterSection" class="border rounded px-3 py-2 text-sm">
            <option value="">Filter by Section</option>
            @foreach($sections as $section)
            <option value="{{ $section->id }}">{{ $section->name }}</option>
            @endforeach
        </select>
    </div>

    {{-- Students Table --}}
    <div class="bg-white shadow rounded-lg overflow-hidden">
        <table class="min-w-full text-sm">
            <thead class="bg-gray-100">
                <tr class="text-left text-gray-600 uppercase text-xs tracking-wider">
                    <th class="px-4 py-3 cursor-pointer" @click="sortTable('student_id')">
                        Student ID
                        <span x-text="sortKey === 'student_id' ? (sortAsc ? '▲' : '▼') : ''"></span>
                    </th>
                    <th class="px-4 py-3 cursor-pointer" @click="sortTable('name')">
                        Name
                        <span x-text="sortKey === 'name' ? (sortAsc ? '▲' : '▼') : ''"></span>
                    </th>
                    <th class="px-4 py-3 cursor-pointer" @click="sortTable('course')">
                        Course
                        <span x-text="sortKey === 'course' ? (sortAsc ? '▲' : '▼') : ''"></span>
                    </th>
                    <th class="px-4 py-3 cursor-pointer" @click="sortTable('year')">
                        Year
                        <span x-text="sortKey === 'year' ? (sortAsc ? '▲' : '▼') : ''"></span>
                    </th>
                    <th class="px-4 py-3 cursor-pointer" @click="sortTable('section')">
                        Section
                        <span x-text="sortKey === 'section' ? (sortAsc ? '▲' : '▼') : ''"></span>
                    </th>
                    <th class="px-4 py-3">Contact</th>
                    <th class="px-4 py-3">Email</th>
                    <th class="px-4 py-3">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                <template x-for="student in filteredStudents" :key="student.id">
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-4 py-2" x-text="student.student_id"></td>
                        <td class="px-4 py-2" x-text="student.last_name + ', ' + student.first_name + (student.middle_name ? ' ' + student.middle_name : '') + (student.suffix ? ', ' + student.suffix : '')"></td>
                        <td class="px-4 py-2" x-text="student.course"></td>
                        <td class="px-4 py-2" x-text="student.year"></td>
                        <td class="px-4 py-2" x-text="student.section"></td>
                        <td class="px-4 py-2" x-text="student.contact || '-'"></td>
                        <td class="px-4 py-2" x-text="student.email || '-'"></td>
                        <td class="px-4 py-2">
                            <form :action="`/college/students/${student.id}`" method="POST" onsubmit="return confirm('Are you sure?')">
                                @csrf
                                @method('DELETE')
                                <button class="text-red-600 hover:text-red-800 text-sm">Delete</button>
                            </form>
                        </td>
                    </tr>
                </template>
                <tr x-show="filteredStudents.length === 0">
                    <td colspan="8" class="text-center py-4 text-gray-400 italic">No students found</td>
                </tr>
            </tbody>
        </table>
    </div>

    {{-- Add Student Modal --}}
    <div x-show="showModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div @click.away="showModal = false" class="bg-white rounded-lg shadow-lg w-full max-w-lg relative p-6">
            <button @click="showModal = false" class="absolute top-2 right-3 text-gray-500 hover:text-gray-800 text-xl">&times;</button>

            <h3 class="text-lg font-semibold mb-4 border-b pb-2">Add New Student</h3>

            <form method="POST" action="{{ route('college.students.store') }}" class="space-y-4">
                @csrf
                
            <div>
                <label class="text-sm font-medium">
                    Student ID <span class="text-red-500">*</span>
                </label>
                <input
                    type="text"
                    name="student_id"
                    required
                    placeholder="Enter student ID"
                    class="w-full border rounded px-3 py-2 text-sm"
                >
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                <div>
                    <label class="text-sm font-medium">
                        Last Name <span class="text-red-500">*</span>
                    </label>
                    <input
                        type="text"
                        name="last_name"
                        required
                        placeholder="Enter last name"
                        class="w-full border rounded px-3 py-2 text-sm"
                    >
                </div>

                <div>
                    <label class="text-sm font-medium">
                        First Name <span class="text-red-500">*</span>
                    </label>
                    <input
                        type="text"
                        name="first_name"
                        required
                        placeholder="Enter first name"
                        class="w-full border rounded px-3 py-2 text-sm"
                    >
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                <div>
                    <label class="text-sm font-medium">
                        Middle Name <span class="text-red-500">*</span>
                    </label>
                    <input
                        type="text"
                        name="middle_name"
                        required
                        placeholder="Enter middle name"
                        class="w-full border rounded px-3 py-2 text-sm"
                    >
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

</div>

<script>
function studentDirectory() {
    return {
        showModal: false,
        search: '',
        filterCourse: '',
        filterYear: '',
        filterSection: '',
        sortKey: '',
        sortAsc: true,
        students: @json($students),
        get filteredStudents() {
            let result = this.students;

            if (this.search) {
                const searchLower = this.search.toLowerCase();
                result = result.filter(s =>
                    s.student_id.toLowerCase().includes(searchLower) ||
                    s.first_name.toLowerCase().includes(searchLower) ||
                    s.last_name.toLowerCase().includes(searchLower) ||
                    (s.middle_name && s.middle_name.toLowerCase().includes(searchLower))
                );
            }

            if (this.filterCourse) result = result.filter(s => s.course_id == this.filterCourse);
            if (this.filterYear) result = result.filter(s => s.year_level_id == this.filterYear);
            if (this.filterSection) result = result.filter(s => s.section_id == this.filterSection);

            if (this.sortKey) {
                result = result.sort((a, b) => {
                    let valA = a[this.sortKey] ?? '';
                    let valB = b[this.sortKey] ?? '';
                    if (valA < valB) return this.sortAsc ? -1 : 1;
                    if (valA > valB) return this.sortAsc ? 1 : -1;
                    return 0;
                });
            }

            return result;
        },
        sortTable(key) {
            if (this.sortKey === key) {
                this.sortAsc = !this.sortAsc;
            } else {
                this.sortKey = key;
                this.sortAsc = true;
            }
        }
    }
}
</script>
@endsection
