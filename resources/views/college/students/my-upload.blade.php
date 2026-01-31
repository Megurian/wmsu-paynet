@extends('layouts.dashboard')

@section('title', 'Students')
@section('page-title', 'My Students Upload')

@section('content')

<div x-data="myStudentsUpload()" x-init="">
    {{-- Top Actions --}}


    {{-- Import Modal --}}
    <div x-show="showImportModal" x-cloak class="fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center z-50">
        <div @click.away="showImportModal = false" class="bg-white rounded-xl shadow-lg w-full max-w-md p-6 relative">
            <button @click="showImportModal = false" class="absolute top-2 right-3 text-gray-500 hover:text-gray-800 text-xl">&times;</button>

            <h3 class="text-lg font-semibold mb-4">Import Student List</h3>
            <p class="text-sm text-gray-700 mb-4">
                Download the template, fill in student details, and upload. Students remain unvalidated until manual validation.
            </p>

            <a href="{{ route('college.students.import.template') }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-500 transition">
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


    {{-- Filters and Actions --}}
<div class="bg-white border border-gray-200 rounded-xl p-4 mb-5 shadow-sm">
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3 items-end">

        {{-- Search --}}
        <div class="relative col-span-1 sm:col-span-2">
            <input type="text" x-model="search" placeholder="Search by name or Student ID"
                class="w-full px-3 py-2 pr-10 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-400 focus:outline-none">
            <button type="button" x-show="search" @click="search=''"
                class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 focus:outline-none transition">&times;</button>
        </div>

        {{-- Course --}}
        <select x-model="filterCourse"
            class="rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition">
            <option value="">All Courses</option>
            @foreach($courses as $course)
            <option value="{{ $course->id }}">{{ $course->name }}</option>
            @endforeach
        </select>

        {{-- Year --}}
        <select x-model="filterYear"
            class="rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition">
            <option value="">All Year Levels</option>
            @foreach($years as $year)
            <option value="{{ $year->id }}">{{ $year->name }}</option>
            @endforeach
        </select>

        {{-- Section --}}
        <select x-model="filterSection"
            class="rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition">
            <option value="">All Sections</option>
            @foreach($sections as $section)
            <option value="{{ $section->id }}">{{ $section->name }}</option>
            @endforeach
        </select>

    </div>

    {{-- Action Buttons --}}
    <div class="flex justify-end gap-4 mt-4">

        <button @click="showModal = true"
            class="bg-red-800 text-white px-4 py-2 rounded hover:bg-red-700 transition">
            New Student
        </button>

        <button @click="showImportModal = true"
            class="px-4 py-2 bg-blue-600 text-white rounded-lg shadow hover:bg-blue-500 transition">
            Import Student List
        </button>
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

                <div>
                    <label class="text-sm font-medium">Religion</label>
                    <input type="text" name="religion" placeholder="Optional" class="w-full border rounded px-3 py-2 text-sm" value="{{ old('religion') }}">
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

    <div class="flex items-center justify-between mb-3">
        <div class="flex items-center space-x-2">
            <input type="checkbox" @click="toggleAll($event)" class="w-5 h-5 border-gray-400 rounded cursor-pointer">
            <label class="font-medium text-gray-700 select-none">Select All</label>
        </div>
        <button @click="proceedToPayment()" 
                x-show="selectedStudents.length > 0"
                class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-500">
            Proceed to Payment
        </button>
    </div>

    {{-- Students Table as Cards --}}
    <div class="space-y-4">
        <template x-for="student in filteredStudents" :key="student.id">
            <div class="bg-white shadow rounded-xl p-4 flex flex-col md:flex-row md:items-center justify-between space-y-3 md:space-y-0 md:space-x-4">
               <div class="flex items-center md:w-1/3 space-x-2">
                    <template x-if="student.status === 'NOT ENROLLED'">
                        <!-- Bulk select checkbox -->
                        <div class="flex items-center space-x-2 md:w-1/12">
                            <input type="checkbox" 
                                x-model="selectedStudents" 
                                :value="student.id" 
                                class="w-5 h-5 border-gray-400 rounded cursor-pointer">
                        </div>
                    </template>
                    <div class="text-sm font-semibold" x-text="student.student_id"></div>
                     <div class="text-sm font-medium" x-text="student.last_name + ', ' + student.first_name + (student.middle_name ? ' ' + student.middle_name : '')"></div>
                </div>
                {{-- Student Info --}}
                {{-- <div class="flex items-center space-x-4 md:w-1/3">
                    <div class="text-sm font-semibold" x-text="student.student_id"></div>
                    <div class="text-sm font-medium" x-text="student.last_name + ', ' + student.first_name + (student.middle_name ? ' ' + student.middle_name : '')"></div>
                </div> --}}

                {{-- Course / Year / Section --}}
                <div class="md:w-1/3 text-sm text-gray-700">
                    <div class="grid grid-cols-3 gap-2 items-center">
                        {{-- Course --}}
                        <div>
                            <select 
                                x-model="student.course_id" 
                                @change="updateStudent(student.id, 'course_id', student.course_id)" 
                                class="w-full border rounded px-2 py-1 text-xs"
                                :disabled="student.status === 'ENROLLED' || student.status === 'FOR_PAYMENT_VALIDATION'">
                                <option value="">Select Course</option>
                                @foreach($courses as $course)
                                <option value="{{ $course->id }}">{{ $course->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Year --}}
                        <div>
                            <select 
                                x-model="student.year_level_id" 
                                @change="updateStudent(student.id, 'year_level_id', student.year_level_id)" 
                                class="w-full border rounded px-2 py-1 text-xs"
                                :disabled="student.status === 'ENROLLED' || student.status === 'FOR_PAYMENT_VALIDATION'">
                                <option value="">Select Year</option>
                                @foreach($years as $year)
                                <option value="{{ $year->id }}">{{ $year->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Section --}}
                        <div>
                            <select 
                                x-model="student.section_id" 
                                @change="updateStudent(student.id, 'section_id', student.section_id)" 
                                class="w-full border rounded px-2 py-1 text-xs"
                                :disabled="student.status === 'ENROLLED' || student.status === 'FOR_PAYMENT_VALIDATION'">
                                <option value="">Select Section</option>
                                @foreach($sections as $section)
                                <option value="{{ $section->id }}">{{ $section->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                
                {{-- Status / Actions --}}
                <div class="flex flex-col items-end md:w-1/3 space-y-2">
                    <div>
                        <template x-if="student.status === 'NOT ENROLLED'">
                            <form :action="`{{ url('/college/students') }}/${student.id}/readd`" method="POST" class="flex gap-2 items-center">
                                @csrf
                                <input type="hidden" name="course_id" :value="student.course_id">
                                <input type="hidden" name="year_level_id" :value="student.year_level_id">
                                <input type="hidden" name="section_id" :value="student.section_id">
                                <button type="submit" class="px-3 py-1 bg-green-600 text-white rounded hover:bg-green-500 text-xs">
                                    Proceed to Payment
                                </button>
                            </form>
                        </template>
                        <template x-if="student.status === 'FOR_PAYMENT_VALIDATION'">
                            <span class="text-yellow-700 italic text-sm">Pending payment</span>
                        </template>
                        <template x-if="student.status === 'ENROLLED'">
                            <span class="text-indigo-600 font-semibold text-sm">Enrolled</span>
                        </template>
                    </div>

                    {{-- Progress Steps --}}
                    <div class="flex items-center space-x-3 mt-2 w-full">
                        <!-- Advising -->
                        <div class="flex items-center space-x-1">
                            <div class="w-5 h-5 flex items-center justify-center rounded-full" :class="student.status !== 'NOT ENROLLED' ? 'bg-blue-600' : 'bg-gray-200'">
                                <template x-if="student.status !== 'NOT ENROLLED'">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                                    </svg>
                                </template>
                                <template x-if="student.status === 'NOT ENROLLED'">
                                    <span class="text-[8px] font-semibold text-gray-500">A</span>
                                </template>
                            </div>
                            <span class="text-[10px]" :class="student.status !== 'NOT ENROLLED' ? 'text-blue-600 font-semibold' : 'text-gray-400'">Advising</span>
                        </div>

                        <div class="flex-1 border-t-2 border-dashed" :class="student.status !== 'NOT ENROLLED' ? 'border-blue-300' : 'border-gray-300'"></div>

                        <!-- Payment -->
                        <div class="flex items-center space-x-1">
                            <div class="w-5 h-5 flex items-center justify-center rounded-full"
                                :class="student.isPaid ? 'bg-green-600' : 'bg-gray-200'">
                                <template x-if="student.isPaid">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                                    </svg>
                                </template>
                                <template x-if="!student.isPaid">
                                    <span class="text-[8px] font-semibold text-gray-500">P</span>
                                </template>
                            </div>
                            <span class="text-[10px]" :class="student.isPaid ? 'text-green-600 font-semibold' : 'text-gray-400'">Payment</span>
                        </div>

                        <div class="flex-1 border-t-2 border-dashed" :class="student.status === 'FOR_PAYMENT_VALIDATION' || student.status === 'ENROLLED' ? 'border-green-300' : 'border-gray-300'"></div>

                        <!-- Enrollment -->
                        <div class="flex items-center space-x-1">
                            <div class="w-5 h-5 flex items-center justify-center rounded-full" :class="student.status === 'ENROLLED' ? 'bg-indigo-600' : 'bg-gray-200'">
                                <template x-if="student.status === 'ENROLLED'">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                                    </svg>
                                </template>
                                <template x-if="student.status !== 'ENROLLED'">
                                    <span class="text-[8px] font-semibold text-gray-500">E</span>
                                </template>
                            </div>
                            <span class="text-[10px]" :class="student.status === 'ENROLLED' ? 'text-indigo-600 font-semibold' : 'text-gray-400'">Enrollment</span>
                        </div>
                    </div>
                </div>
            </div>
        </template>

        <div x-show="filteredStudents.length === 0" class="text-center text-gray-500 py-6 italic">
            No students found.
        </div>
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

        selectedStudents: [], 

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
        },

        toggleAll(event) {
            const checked = event.target.checked;
            if (checked) {
                this.selectedStudents = this.filteredStudents.map(s => s.id);
            } else {
                this.selectedStudents = [];
            }
        },

        proceedToPayment() {
            if (this.selectedStudents.length === 0) return;

            if (!confirm(`Proceed to payment for ${this.selectedStudents.length} student(s)?`)) return;

            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '{{ route("college.students.readd.bulk") }}';
            form.innerHTML = `
                @csrf
                ${this.selectedStudents.map(id => `<input type="hidden" name="students[]" value="${id}">`).join('')}
            `;
            document.body.appendChild(form);
            form.submit();
        },

        updateStudent(studentId, field, value) {
            fetch(`/college/students/${studentId}/update-field`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ field, value })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) console.log('Updated successfully');
            })
            .catch(err => console.error(err));
        }
    }
}


</script>


@endsection
