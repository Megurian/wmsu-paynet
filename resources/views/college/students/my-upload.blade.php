@extends('layouts.dashboard')

@section('page-title', 'My Students Upload')

@section('content')

<div x-data="myStudentsUpload()" x-init="">
    {{-- Top Actions --}}
    <div class="flex items-center gap-4 mb-4">
        <button @click="showModal = true" class="bg-red-800 text-white px-4 py-2 rounded hover:bg-red-700 transition">
            New Student
        </button>

        <button @click="showImportModal = true" class="px-4 py-2 bg-blue-600 text-white rounded-lg shadow hover:bg-blue-500 transition">
            Import Student List
        </button>
    </div>

    {{-- Import Modal --}}
    <div x-show="showImportModal" x-cloak class="fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center z-50">
        <div @click.away="showImportModal = false" class="bg-white rounded-xl shadow-lg w-full max-w-md p-6 relative">
            <button @click="showImportModal = false" class="absolute top-2 right-3 text-gray-500 hover:text-gray-800 text-xl">&times;</button>

            <h3 class="text-lg font-semibold mb-4">Import Student List</h3>
            <p class="text-sm text-gray-700 mb-4">
                Download the template, fill in student details, and upload. Students remain unvalidated until manual validation.
            </p>

            <a href="{{ route('college.students.import.template') }}"
                class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-500 transition">
                Download Import Template
            </a>

            <form action="{{ route('college.students.import') }}" method="POST" enctype="multipart/form-data" class="mt-4">
                @csrf
                <input type="file" name="student_file" accept=".csv, .xls, .xlsx" class="mb-4">
                <div class="flex justify-end gap-2">
                    <button type="button" @click="showImportModal = false" class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-500">Upload</button>
                </div>
            </form>
        </div>
    </div>

    {{-- New Student Modal --}}
    <div x-show="showModal" x-cloak class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div @click.away="showModal = false" class="bg-white rounded-lg shadow-lg w-full max-w-lg relative p-6">
            <button @click="showModal = false" class="absolute top-2 right-3 text-gray-500 hover:text-gray-800 text-xl">&times;</button>
            <h3 class="text-lg font-semibold mb-4 border-b pb-2">New Student</h3>

            <form method="POST" action="{{ route('college.students.my-upload.store') }}" class="space-y-4">
                @csrf
                {{-- Student ID --}}
                <div>
                    <label class="text-sm font-medium">Student ID <span class="text-red-500">*</span></label>
                    <input type="text" name="student_id" value="{{ old('student_id') }}" required class="w-full rounded border px-3 py-2 text-sm">
                </div>

                {{-- Names --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <div>
                        <label class="text-sm font-medium">Last Name <span class="text-red-500">*</span></label>
                        <input type="text" name="last_name" required placeholder="Enter last name" class="w-full border rounded px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="text-sm font-medium">First Name <span class="text-red-500">*</span></label>
                        <input type="text" name="first_name" required placeholder="Enter first name" class="w-full border rounded px-3 py-2 text-sm">
                    </div>
                </div>

                {{-- Middle Name / Suffix --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <div>
                        <label class="text-sm font-medium">Middle Name</label>
                        <input type="text" name="middle_name" placeholder="Enter middle name" class="w-full border rounded px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="text-sm font-medium">Suffix</label>
                        <select name="suffix" class="w-full border rounded px-3 py-2 text-sm">
                            <option value="">None</option>
                            <option value="Jr.">Jr.</option>
                            <option value="Sr.">Sr.</option>
                            <option value="II">II</option>
                            <option value="III">III</option>
                        </select>
                    </div>
                </div>

                {{-- Course / Year / Section --}}
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

                {{-- Contact / Email --}}
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

    {{-- Filters --}}
    <div class="bg-white border border-gray-200 rounded-xl p-4 mb-5 shadow-sm">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">

            {{-- Search --}}
            <div class="relative col-span-1 sm:col-span-2">
                <input type="text" x-model="search" placeholder="Search by name or Student ID" class="w-full rounded-lg border px-4 py-2.5 pr-10 text-sm placeholder-gray-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition">
                <button type="button" x-show="search" @click="search=''" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 focus:outline-none transition">&times;</button>
            </div>

            {{-- Course --}}
            <select x-model="filterCourse" class="w-full rounded-lg border px-4 py-2.5 text-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition">
                <option value="">All Courses</option>
                @foreach($courses as $course)
                    <option value="{{ $course->id }}">{{ $course->name }}</option>
                @endforeach
            </select>

            {{-- Year --}}
            <select x-model="filterYear" class="w-full rounded-lg border px-4 py-2.5 text-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition">
                <option value="">All Year Levels</option>
                @foreach($years as $year)
                    <option value="{{ $year->id }}">{{ $year->name }}</option>
                @endforeach
            </select>

            {{-- Section --}}
            <select x-model="filterSection" class="w-full rounded-lg border px-4 py-2.5 text-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition">
                <option value="">All Sections</option>
                @foreach($sections as $section)
                    <option value="{{ $section->id }}">{{ $section->name }}</option>
                @endforeach
            </select>

        </div>
    </div>

    {{-- Students Table --}}
    <div class="bg-white shadow-sm border border-gray-200 rounded-xl overflow-hidden">
        <table class="min-w-full text-sm text-gray-800">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr class="text-left text-[11px] font-semibold uppercase tracking-wide text-gray-600">
                    <th class="px-5 py-3">Student ID</th>
                    <th class="px-5 py-3">Name</th>
                    <th class="px-5 py-3">Status</th>
                    <th class="px-5 py-3">Course</th>
                    <th class="px-5 py-3">Year</th>
                    <th class="px-5 py-3">Section</th>
                    <th class="px-5 py-3">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <template x-for="student in filteredStudents" :key="student.id">
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-5 py-3" x-text="student.student_id"></td>
                        <td class="px-5 py-3" x-text="student.last_name + ', ' + student.first_name + (student.middle_name ? ' ' + student.middle_name : '')"></td>
                        <td class="px-5 py-3" x-text="student.status"></td>
                        <td class="px-5 py-3" x-text="student.course || '-'"></td>
                        <td class="px-5 py-3" x-text="student.year_level || '-'"></td>
                        <td class="px-5 py-3" x-text="student.section || '-'"></td>
                        <td class="px-5 py-3">
                            <form :action="`/college/students/${student.id}/readd`" method="POST" class="flex gap-2 items-center">
                                @csrf
                                <button type="submit" class="bg-green-600 text-white px-3 py-1 rounded hover:bg-green-500">Re-add</button>
                            </form>
                        </td>
                    </tr>
                </template>

                <tr x-show="filteredStudents.length === 0">
                    <td colspan="7" class="text-center py-6 text-gray-400 italic">
                        No students found
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<script>
function myStudentsUpload() {
    return {
        showModal: false,
        showImportModal: false,
        search: '',
        filterCourse: '',
        filterYear: '',
        filterSection: '',
        students: @json($alpineStudents),

        get filteredStudents() {
            let result = this.students;
            if (this.search) {
                const s = this.search.toLowerCase();
                result = result.filter(st =>
                    st.student_id.toLowerCase().includes(s) ||
                    st.first_name.toLowerCase().includes(s) ||
                    st.last_name.toLowerCase().includes(s) ||
                    (st.middle_name && st.middle_name.toLowerCase().includes(s))
                );
            }
            if (this.filterCourse) result = result.filter(st => st.course_id == Number(this.filterCourse));
            if (this.filterYear) result = result.filter(st => st.year_level_id == Number(this.filterYear));
            if (this.filterSection) result = result.filter(st => st.section_id == Number(this.filterSection));

            return result;
        }
    }
}
</script>


@endsection
