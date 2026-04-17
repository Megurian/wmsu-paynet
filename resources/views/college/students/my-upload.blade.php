@extends('layouts.dashboard')

@section('title', 'Students')
@section('page-title', 'My Students Upload')

@section('content')
@php
    $isCleared =  false;
@endphp
<div x-data="myStudentsUpload()" x-init="init()">
    {{-- Import Modal --}}
    <div x-show="showImportModal" x-cloak class="fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center z-50">
        <div @click.away="resetImport()" class="bg-white rounded-xl shadow-lg w-full max-w-lg p-6 relative">
            <button @click="resetImport()" class="absolute top-2 right-3 text-gray-500 hover:text-gray-800 text-xl">&times;</button>

            {{-- Step 1: File Select --}}
            <div x-show="importStep === 'select'">
                <h3 class="text-lg font-semibold mb-2">Import Student List</h3>
                <p class="text-sm text-gray-600 mb-4">
                    Download the template, fill in student details, and upload. The system will detect existing students and ask for confirmation before updating.
                </p>
                <a href="{{ route('college.students.import.template') }}?v={{ time() }}" class="inline-block mb-4 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-500 transition text-sm">
                    Download Import Template
                </a>
                <form id="importForm" action="{{ route('college.students.import') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="file" id="importFileInput" name="student_file" accept=".csv, .xls, .xlsx"
                        @change="importFile = $event.target.files[0]"
                        class="block w-full text-sm text-gray-700 border border-gray-300 rounded-lg px-3 py-2 mb-4">
                    <div class="flex justify-end gap-2">
                        <button type="button" @click="resetImport()" class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400 text-sm">Cancel</button>
                        <button type="button" @click="runPreviewImport()"
                            :disabled="!importFile || importPreviewing"
                            class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-500 text-sm disabled:opacity-50">
                            <span x-show="!importPreviewing">Upload</span>
                            <span x-show="importPreviewing">Checking...</span>
                        </button>
                    </div>
                </form>
            </div>

            {{-- Step 2: Preview / Confirm --}}
            <div x-show="importStep === 'confirm'">
                <h3 class="text-lg font-semibold mb-3">Confirm Import</h3>

                {{-- Summary bar --}}
                <div class="flex gap-3 mb-4 text-sm">
                    <span class="px-3 py-1 bg-green-100 text-green-700 rounded-full font-medium" x-text="importPreview.new_count + ' new'"></span>
                    <span class="px-3 py-1 bg-yellow-100 text-yellow-700 rounded-full font-medium" x-text="importPreview.existing_match.length + ' will be updated'"></span>
                    <template x-if="importPreview.existing_mismatch.length > 0">
                        <span class="px-3 py-1 bg-red-100 text-red-700 rounded-full font-medium" x-text="importPreview.existing_mismatch.length + ' will be skipped'"></span>
                    </template>
                </div>

                {{-- Students that will be updated --}}
                <template x-if="importPreview.existing_match.length > 0">
                    <div class="mb-4">
                        <p class="text-sm font-semibold text-yellow-700 mb-1">⚠ The following students already exist and their information will be updated:</p>
                        <div class="max-h-36 overflow-y-auto border border-yellow-200 rounded-lg bg-yellow-50 text-xs divide-y divide-yellow-100">
                            <template x-for="s in importPreview.existing_match" :key="s.student_id">
                                <div class="px-3 py-1.5 flex gap-3">
                                    <span class="font-mono font-semibold" x-text="s.student_id"></span>
                                    <span x-text="s.last_name + ', ' + s.first_name"></span>
                                </div>
                            </template>
                        </div>
                    </div>
                </template>

                {{-- Students that will be skipped (mismatch) --}}
                <template x-if="importPreview.existing_mismatch.length > 0">
                    <div class="mb-4">
                        <p class="text-sm font-semibold text-red-700 mb-1">🚫 The following rows will be <strong>skipped</strong> — student ID exists but last name does not match:</p>
                        <div class="max-h-36 overflow-y-auto border border-red-200 rounded-lg bg-red-50 text-xs divide-y divide-red-100">
                            <template x-for="s in importPreview.existing_mismatch" :key="s.student_id">
                                <div class="px-3 py-1.5">
                                    <span class="font-mono font-semibold" x-text="s.student_id"></span> —
                                    File: <span class="font-medium" x-text="s.file_last_name"></span>
                                    vs DB: <span class="font-medium" x-text="s.db_last_name"></span>
                                </div>
                            </template>
                        </div>
                    </div>
                </template>

                <p class="text-sm text-gray-600 mb-4">Do you want to proceed with the import?</p>

                <div class="flex justify-end gap-2">
                    <button type="button" @click="importStep = 'select'" class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400 text-sm">Back</button>
                    <button type="button" @click="submitImport()" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-500 text-sm">Confirm &amp; Import</button>
                </div>
            </div>
        </div>
    </div>

    <div x-show="showPromotionModal" x-cloak
     class="fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center z-50">

    <div class="bg-white rounded-xl shadow-lg w-full max-w-2xl p-6">

        <h3 class="text-lg font-semibold mb-2">
            Year Level Promotion
        </h3>

        <p class="text-sm text-gray-600 mb-4">
            Total eligible students: <span x-text="promotionPreview.total"></span>
        </p>

        <template x-if="promotionLoading">
            <p class="text-sm text-gray-500">Loading...</p>
        </template>

        <template x-if="!promotionLoading">

            <div class="space-y-3 max-h-96 overflow-y-auto">

                <template x-for="group in promotionPreview.breakdown" :key="group.from">

                    <div class="border rounded-lg p-3">

                        <div class="font-semibold text-sm mb-2">
                            <span x-text="group.from"></span>
                            →
                            <span x-text="group.to"></span>
                            (<span x-text="group.count"></span>)
                        </div>

                        <div class="text-xs text-gray-600 max-h-24 overflow-y-auto">
                            <template x-for="s in group.students" :key="s.id">
                                <div x-text="s.student_id + ' - ' + s.name"></div>
                            </template>
                        </div>

                    </div>

                </template>

            </div>

        </template>

        <div class="flex justify-end gap-2 mt-4">

            <button @click="showPromotionModal = false"
                class="px-4 py-2 bg-gray-300 rounded text-sm">
                Cancel
            </button>

            <button @click="confirmPromotion()"
                class="px-4 py-2 bg-indigo-600 text-white rounded text-sm">
               Proceed to Payment
            </button>

        </div>

    </div>
</div>

    @if(session('import_success'))
    <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-800 rounded-lg text-sm">
        ✓ {{ session('import_success') }}
    </div>
    @endif
    @if(session('import_skipped') && count(session('import_skipped')) > 0)
    <div class="mb-4 p-4 bg-yellow-100 border border-yellow-400 text-yellow-800 rounded-lg text-sm">
        <p class="font-semibold mb-1">⚠ The following rows were skipped (student ID / last name mismatch):</p>
        <ul class="list-disc list-inside space-y-0.5">
            @foreach(session('import_skipped') as $row)
            <li><span class="font-mono">{{ $row['student_id'] }}</span> — file: "{{ $row['file_last_name'] }}" vs DB: "{{ $row['db_last_name'] }}"</li>
            @endforeach
        </ul>
    </div>
    @endif

    {{-- Filters and Actions --}}
    <div class="bg-white border border-gray-200 rounded-xl p-4 mb-5 shadow-sm">
        <form method="GET"  @submit="if(isDirty && !confirm('You have selected students. Leave anyway?')) $event.preventDefault()" class="bg-white border border-gray-200 rounded-xl p-4 mb-5 shadow-sm">
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3 items-end">

        <div class="relative col-span-1 sm:col-span-2">
            <input type="text"
                   name="search"
                   value="{{ request('search') }}"
                   placeholder="Search by name or Student ID"
                   class="w-full px-3 py-2 pr-10 rounded-lg border border-gray-300">
        </div>

        {{-- Adviser Course (LOCKED) --}}
            <select class="rounded-lg border border-gray-300 px-4 py-2.5 text-sm bg-gray-100 cursor-not-allowed" disabled>
                <option>
                    {{ Auth::user()->course?->name ?? 'No course assigned' }}
                </option>
            </select>

        {{-- Year --}}
        <select name="year_level_id"
                class="rounded-lg border border-gray-300 px-4 py-2.5 text-sm">
            <option value="">All Year Levels</option>
            @foreach($years as $year)
                <option value="{{ $year->id }}"
                    {{ request('year_level_id') == $year->id ? 'selected' : '' }}>
                    {{ $year->name }}
                </option>
            @endforeach
        </select>

        {{-- Section --}}
        <select name="section_id"
                class="rounded-lg border border-gray-300 px-4 py-2.5 text-sm">
            <option value="">All Sections</option>
            @foreach($sections as $section)
                <option value="{{ $section->id }}"
                    {{ request('section_id') == $section->id ? 'selected' : '' }}>
                    {{ $section->name }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="flex justify-between mt-4">
        <div class="text-gray-500 text-sm italic">
            This view shows the students you are assigned to.
        </div>

        <div class="flex gap-2">
            <a href="{{ route('college.students.my-upload') }}"
               class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400 text-sm">
                Reset
            </a>

            <button type="submit"
                    class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-500 text-sm">
                Apply Filters
            </button>
        </div>
    </div>
</form>

        {{-- Action Buttons --}}
        <div class="flex justify-end gap-4 mt-4 i items-center">

             <div class="text-gray-500 text-sm italic">
                This view shows the students you are assigned to. You can manage, add, or import student data for your course.
            </div>
            <button @click="showModal = true"
                class="bg-red-800 text-white px-4 py-2 rounded hover:bg-red-700 transition">
                New Student
            </button>

            <button @click="showImportModal = true"
                class="px-4 py-2 bg-blue-600 text-white rounded-lg shadow hover:bg-blue-500 transition">
                Import Student List
            </button>

            <button @click="openPromotionPreview()"
                class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-500 text-sm">
                Year Level Promotion
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
                   @if(Auth::user()->role === 'adviser')
                        <div>
                            <label class="text-sm font-medium">Course</label>
                            <select class="w-full border rounded px-3 py-2 text-sm bg-gray-100" disabled>
                                <option value="{{ Auth::user()->course_id }}" selected>
                                    {{ Auth::user()->course?->name ?? 'No course assigned' }}
                                </option>
                            </select>
                            <input type="hidden" name="course_id" value="{{ Auth::user()->course_id }}">
                        </div>
                    @else
                        <div>
                            <label class="text-sm font-medium">Course <span class="text-red-500">*</span></label>
                            <select name="course_id" required class="w-full border rounded px-3 py-2 text-sm">
                                <option value="">Select Course</option>
                                @foreach($courses as $course)
                                <option value="{{ $course->id }}">{{ $course->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif        
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

        <div class="mt-4">
    {{ $students->links() }}
</div>
        <template x-for="student in students" :key="student.id">
            <div class="bg-white shadow rounded-xl p-4 flex flex-col md:flex-row md:items-center justify-between space-y-3 md:space-y-0 md:space-x-4">
               <div class="flex items-center md:w-1/3 space-x-2">
                    <template x-if="student.status === 'NOT_ENROLLED'">
                        <!-- Bulk select checkbox -->
                        <div class="flex items-center space-x-2 md:w-1/12">
                            <input type="checkbox" 
                                x-model="selectedStudents" 
                                :value="student.id" 
                                :disabled="student.status !== 'NOT_ENROLLED'"
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
                            <select x-model="student.course_id" class="w-full border rounded px-2 py-1 text-xs" disabled>
                                <option value="{{ Auth::user()->course_id }}" selected>
                                    {{ Auth::user()->course?->name ?? 'No course assigned' }}
                                </option>
                            </select>
                        </div>

                        {{-- Year --}}
                        <div>
                            <select 
                                x-model="student.year_level_id" 
                                @change="updateStudent(student.id, 'year_level_id', student.year_level_id)" 
                                class="w-full border rounded px-2 py-1 text-xs"
                                :disabled="student.status === 'ENROLLED' || student.status === 'PAID' || student.status === 'FOR_PAYMENT_VALIDATION'">
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
                                :disabled="student.status === 'ENROLLED' || student.status === 'PAID' || student.status === 'FOR_PAYMENT_VALIDATION'">
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
                        <template x-if="student.status === 'NOT_ENROLLED'">
                            <form :action="`{{ url('/college/students') }}/${student.id}/readd`" method="POST" class="flex gap-2 items-center">
                                @csrf
                                <input type="hidden" name="course_id" :value="student.course_id">
                                <input type="hidden" name="year_level_id" :value="student.year_level_id">
                                <input type="hidden" name="section_id" :value="student.section_id">
                                <button type="submit" class="px-3 py-1 bg-green-600 text-white rounded hover:bg-green-500 text-xs" onclick="return confirm('Confirm this Student is Advised and may proceed to payment?')">
                                    Proceed to Payment
                                </button>
                            </form>
                        </template>
                        <template x-if="student.status === 'FOR_PAYMENT_VALIDATION'">
                            <span class="text-yellow-700 font-semibold text-sm">Pending Payment</span>
                        </template>
                        <template x-if="student.status === 'PAID'">
                            <span class="text-green-700 font-semibold text-sm">For Assessment</span>
                        </template>
                        <template x-if="student.status === 'ENROLLED'">
                            <span class="text-indigo-600 font-semibold text-sm">Assessment Completed</span>
                        </template>
                    </div>

                    {{-- Progress Steps --}}
                    <div class="flex items-center space-x-3 mt-2 w-full">
                        <!-- Advising -->
                        <div class="flex items-center space-x-1">
                            <div class="w-5 h-5 flex items-center justify-center rounded-full" :class="student.status !== 'NOT_ENROLLED' ? 'bg-blue-600' : 'bg-gray-200'">
                                <template x-if="student.status !== 'NOT_ENROLLED'">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                                    </svg>
                                </template>
                                <template x-if="student.status === 'NOT_ENROLLED'">
                                    <span class="text-[8px] font-semibold text-gray-500">A</span>
                                </template>
                            </div>
                            <span class="text-[10px]" :class="student.status !== 'NOT_ENROLLED' ? 'text-blue-600 font-semibold' : 'text-gray-400'">Advising</span>
                        </div>

                        <div class="flex-1 border-t-2 border-dashed" :class="student.status !== 'NOT_ENROLLED' ? 'border-blue-300' : 'border-gray-300'"></div>

                        <!-- Payment -->
                        <div class="flex items-center space-x-1">
                            <div class="w-5 h-5 flex items-center justify-center rounded-full"
                                :class="student.isCleared ? 'bg-green-600' : 'bg-gray-200'">
                                <template x-if="student.isCleared">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                                    </svg>
                                </template>
                                <template x-if="!student.isCleared">
                                    <span class="text-[8px] font-semibold text-gray-500">P</span>
                                </template>
                            </div>
                            <span class="text-[10px]" :class="student.isCleared ? 'text-green-600 font-semibold' : 'text-gray-400'">Payment</span>
                        </div>

                        <div class="flex-1 border-t-2 border-dashed" :class="student.status === 'FOR_PAYMENT_VALIDATION' || student.status === 'PAID' || student.status === 'ENROLLED' ? 'border-green-300' : 'border-gray-300'"></div>

                        <!-- Enrollment -->
                        <div class="flex items-center space-x-1">
                            <div class="w-5 h-5 flex items-center justify-center rounded-full" :class="student.status === 'ENROLLED' ? 'bg-indigo-600' : 'bg-gray-200'">
                                <template x-if="student.status === 'ENROLLED'">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                                    </svg>
                                </template>
                                <template x-if="student.status !== 'ENROLLED'">
                                    <span class="text-[8px] font-semibold text-gray-500">A</span>
                                </template>
                            </div>
                            <span class="text-[10px]" :class="student.status === 'ENROLLED' ? 'text-indigo-600 font-semibold' : 'text-gray-400'">Assessment</span>
                        </div>
                    </div>
                </div>
            </div>
        </template>

        <div x-show="students.length === 0" class="text-center text-gray-500 py-6 italic">
            No students found.
        </div>
    </div>


</div>



<script>
 function myStudentsUpload() {
    return {
        showModal: false,
        showImportModal: false,
        importStep: 'select',    // 'select' | 'confirm'
        importFile: null,
        importPreview: { new_count: 0, existing_match: [], existing_mismatch: [] },
        importPreviewing: false,
        students: @json($alpineStudents),

        selectedStudents: [],
        isDirty: false,
        showPromotionModal: false,
        promotionPreview: { breakdown: [], total: 0 },
        promotionLoading: false,

       init() {
            this.$watch('selectedStudents', (value) => {
                this.isDirty = value.length > 0;
            });

            this._beforeUnloadHandler = (e) => {
                if (this.selectedStudents.length > 0) {
                    e.preventDefault();
                    e.returnValue = '';
                }
            };

            window.addEventListener('beforeunload', this._beforeUnloadHandler);
        },
        destroy() {
            window.removeEventListener('beforeunload', this._beforeUnloadHandler);
        },
        beforeUnloadHandler(e) {
            if (this.selectedStudents.length > 0) {
                e.preventDefault();
                e.returnValue = '';
            }
        },
        toggleAll(event) {
            const checked = event.target.checked;

            if (checked) {
                this.selectedStudents = this.students
                    .filter(s => s.status === 'NOT_ENROLLED')
                    .map(s => s.id);
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
            this.isDirty = false;
             this.selectedStudents = [];
            form.submit();
        },

        resetImport() {
            this.showImportModal = false;
            this.importStep = 'select';
            this.importFile = null;
            this.importPreview = { new_count: 0, existing_match: [], existing_mismatch: [] };
            this.importPreviewing = false;
            const fi = document.getElementById('importFileInput');
            if (fi) fi.value = '';
        },

        async runPreviewImport() {
            if (!this.importFile) return;
            this.importPreviewing = true;
            const fd = new FormData();
            fd.append('student_file', this.importFile);
            fd.append('_token', '{{ csrf_token() }}');
            try {
                const res = await fetch('{{ route("college.students.import.preview") }}', {
                    method: 'POST',
                    body: fd,
                });
                if (!res.ok) throw new Error('Preview request failed');
                this.importPreview = await res.json();
                if (this.importPreview.existing_match.length === 0 && this.importPreview.existing_mismatch.length === 0) {
                    this.submitImport();
                } else {
                    this.importStep = 'confirm';
                }
            } catch (e) {
                alert('Could not preview file. Please check the file format and try again.');
            } finally {
                this.importPreviewing = false;
            }
        },

        submitImport() {
            document.getElementById('importForm').submit();
        },

        updateStudent(studentId, field, value) {
        fetch(`/college/students/${studentId}/update-field`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ field, value })
        })
        .then(async res => {
            if (!res.ok) {
                const text = await res.text();
                throw new Error(text);
            }
            return res.json();
        })
        .then(data => {
            if (data.success) {
                console.log('Updated successfully');
            } else {
                console.error(data.message);
            }
        })
        .catch(err => console.error('Update failed:', err));
    },

    watchSelected() {
        this.selectedStudents = this.selectedStudents.filter(id =>
            this.students.some(s => s.id === id && s.status === 'NOT_ENROLLED')
        );
    },
    async openPromotionPreview() {
        this.showPromotionModal = true;
        this.promotionLoading = true;

        try {
            const res = await fetch('{{ route("college.students.promotion.preview") }}');
            this.promotionPreview = await res.json();
        } catch (e) {
            alert('Failed to load promotion preview');
        } finally {
            this.promotionLoading = false;
        }
    },

    async confirmPromotion() {
        if (!confirm('Proceed with year promotion and send students to payment process?')) return;

        try {
            const res = await fetch('{{ route("college.students.promotion.execute") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            });

            const data = await res.json();

            alert(data.message);

            this.showPromotionModal = false;
            window.location.reload();

        } catch (e) {
            alert('Promotion failed');
        }
    },
    }
}


</script>


@endsection
