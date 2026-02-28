@extends('layouts.dashboard')

@section('title', 'Validate Students')
@section('page-title', 'Validate Students')

@section('content')
<div class="flex flex-col md:flex-row items-center justify-between mb-6 gap-4">
    <h2 class="text-2xl font-bold text-gray-800">Validate Students</h2>
</div>

<div class="bg-white shadow rounded-lg p-4 mb-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
    <form method="GET" class="flex flex-wrap gap-3 flex-1 items-center">
     
        <div class="relative flex-1 min-w-[150px]">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by name or Student ID" class="w-full px-3 py-2 pr-10 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-400 focus:outline-none">
            @if(request('search'))
                <button type="button" onclick="this.closest('form').querySelector('input[name=search]').value=''; this.closest('form').submit();" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 focus:outline-none transition" aria-label="Clear search">
                    &times;
                </button>
            @endif
        </div>
        <select name="course" class="rounded-lg border border-gray-300 px-4 py-2.5 pr-8 appearance-none text-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition" onchange="this.form.submit()">
            <option value="">All Courses</option>
            @foreach($courses as $course)
                <option value="{{ $course->id }}" @selected(request('course') == $course->id)>{{ $course->name }}</option>
            @endforeach
        </select>

        <select name="year" class="rounded-lg border border-gray-300 px-4 py-2.5 pr-8 appearance-none text-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition" onchange="this.form.submit()">
            <option value="">All Years</option>
            @foreach($years as $year)
                <option value="{{ $year->id }}" @selected(request('year') == $year->id)>{{ $year->name }}</option>
            @endforeach
        </select>

        <select name="section" class="rounded-lg border border-gray-300 px-4 py-2.5 pr-8 appearance-none text-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition" onchange="this.form.submit()">
            <option value="">All Sections</option>
            @foreach($sections as $section)
                <option value="{{ $section->id }}" @selected(request('section') == $section->id)>{{ $section->name }}</option>
            @endforeach
        </select>
    </form>
    {{-- <div class="flex justify-end mb-4">
       <button onclick="document.getElementById('importModal').classList.remove('hidden')" 
            class="px-4 py-2 bg-blue-600 text-white rounded-lg shadow hover:bg-blue-500 transition">
            Import Student List
        </button>
    </div> --}}
</div>

@if(session('success'))
<div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-800 rounded">
    {{ session('success') }}
</div>
@endif

<div x-data="paymentVerification()">
<form method="POST" action="{{ route('college.students.validate.bulk') }}" x-data="studentSelection()" x-init="init()" class="space-y-4">
    @csrf

    <div class="flex flex-col md:flex-row items-center justify-between mt-4 gap-2">
        <div class="mt-2 md:mt-0">
            {{ $students->links() }}
        </div>
    </div>

    <div class="space-y-4">
        <div class="flex items-center justify-between mb-2">
            <div class="flex items-center space-x-2">
                @if(auth()->user()->isAssessor())
                <input type="checkbox"
                    @click="toggleAll($event)"
                    class="w-5 h-5 border-gray-400 rounded focus:ring-2 focus:ring-blue-400 cursor-pointer">
                <label class="text-sm font-medium text-gray-700 select-none">Select All</label>
                @endif
            </div>
            <div x-show="selected.length > 0" x-transition class="transition duration-200" x-cloak>
                @if(auth()->user()->isAssessor())
                <button type="submit" class="px-4 py-2 bg-red-800 text-white rounded-md hover:bg-red-700 shadow transition">
                    Enroll Selected Students
                </button>
                @endif
            </div>
        </div>
        @forelse($students as $student)
        @php
            $currentEnrollment = $activeEnrollments[$student->id] ?? null;
            $displayEnrollment = $student->displayEnrollment;
            $isAdvised  = $currentEnrollment && $currentEnrollment->status !== 'NOT_ENROLLED';
            $isCleared  = $currentEnrollment && $currentEnrollment->cleared_for_enrollment;
            $isPaid     = $currentEnrollment && $currentEnrollment->status === 'PAID';
            $isEnrolled = $currentEnrollment && $currentEnrollment->status === 'ENROLLED';
        @endphp

        <div class="bg-white shadow rounded-xl p-4 flex flex-col md:flex-row md:items-center justify-between space-y-3 md:space-y-0 md:space-x-4">
            {{-- Left Section: Student Info --}}
            <div class="flex items-center md:w-1/3 space-x-2">
                @if($isCleared && !$isEnrolled)
                    @if(auth()->user()->isAssessor())
                        <input type="checkbox"
                            name="selected_students[]"
                            class="w-5 h-5 border-gray-400 rounded focus:ring-2 focus:ring-blue-400 cursor-pointer"
                            value="{{ $student->id }}"
                            @click="toggleOne($event, '{{ $student->id }}')">
                    @endif
                @endif
                <div class="text-sm font-semibold">{{ $student->student_id }}</div>
                <div class="text-sm font-medium">{{ strtoupper($student->last_name) }}, {{ strtoupper($student->first_name) }}</div>
            </div>

            {{-- Middle Section: Course/Year/Section --}}
            <div class="md:w-1/3 text-sm text-gray-700">
                <div class="grid grid-cols-3 gap-2 items-center">
                    <!-- Course Dropdown -->
                    <div>
                        <select name="course_id[{{ $student->id }}]" class="w-full border rounded px-2 py-1 text-xs" required {{ $isEnrolled ? 'disabled' : '' }}>
                            @foreach($courses as $course)
                                <option value="{{ $course->id }}"
                                    @if($displayEnrollment && $course->id == $displayEnrollment->course_id) selected @endif
                                >{{ $course->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Year Dropdown -->
                    <div>
                        <select name="year_level_id[{{ $student->id }}]" class="w-full border rounded px-2 py-1 text-xs" required {{ $isEnrolled ? 'disabled' : '' }}>
                            @foreach($years as $year)
                                <option value="{{ $year->id }}"
                                    @if($displayEnrollment && $year->id == $displayEnrollment->year_level_id) selected @endif
                                >{{ $year->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Section Dropdown -->
                    <div>
                        <select name="section_id[{{ $student->id }}]" class="w-full border rounded px-2 py-1 text-xs" required {{ $isEnrolled ? 'disabled' : '' }}>
                            @foreach($sections as $section)
                                <option value="{{ $section->id }}"
                                    @if($displayEnrollment && $section->id == $displayEnrollment->section_id) selected @endif
                                >{{ $section->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            {{-- Right Section: Status & Progress --}}
            <div class="flex flex-col items-end md:w-1/3 space-y-2">
                {{-- Status/Action Button --}}
                <div>
                    @if($isEnrolled)
                        <span class="text-indigo-600 font-semibold text-sm">Assessment Completed</span>

                    @elseif(!$isAdvised)
                        <span class="text-gray-400 italic text-sm">Waiting for adviser</span>

                    @elseif(!$isCleared)
                        @if(auth()->user()->isStudentCoordinator())
                           <button
                                type="button"
                                @click="openPaymentModal(
                                    {{ $student->id }},
                                    '{{ $student->student_id }}',
                                    '{{ strtoupper($student->last_name) }}, {{ strtoupper($student->first_name) }}',
                                    '{{ $displayEnrollment->course->name ?? '—' }}',
                                    '{{ $displayEnrollment->yearLevel->name ?? '—' }}',
                                    '{{ $displayEnrollment->section->name ?? '—' }}',
                                    '{{ $student->email ?? '—' }}',
                                    '{{ $student->contact ?? '—' }}',
                                    '{{ $student->religion ?? '—' }}'
                                )"
                                class="px-3 py-1 bg-green-600 text-white rounded text-xs hover:bg-green-500"
                            >
                                Verify Payment
                            </button>

                        @else
                            <span class="text-yellow-700 font-semibold text-sm">Pending payment</span>
                        @endif

                    @else
                        @if(auth()->user()->isAssessor())
                            <button 
                                formaction="{{ route('college.students.validate.store', $student->id) }}" 
                                formmethod="POST"
                                class="px-3 py-1 bg-green-600 text-white rounded hover:bg-green-500 transition text-xs"
                                onclick="return confirm('Validate and enroll this student?');"
                            >
                                Enroll
                            </button>
                        
                        @else
                            <span class="text-green-600 font-semibold text-sm">For Assessment</span>
                        @endif
                    @endif  
                </div>

                {{-- Progress Indicator --}}
                <div class="flex items-center space-x-3 mt-2 w-full">
                    <!-- Advising -->
                    <div class="flex items-center space-x-1">
                        <div class="w-5 h-5 flex items-center justify-center rounded-full {{ $isAdvised ? 'bg-blue-600' : 'bg-gray-200' }} text-white">
                            @if($isAdvised)
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                                </svg>
                            @else
                                <span class="text-[8px] font-semibold text-gray-500">A</span>
                            @endif
                        </div>
                        <span class="text-[10px] {{ $isAdvised ? 'text-blue-600 font-semibold' : 'text-gray-400' }}">Advising</span>
                    </div>

                    <div class="flex-1 border-t-2 border-dashed {{ $isAdvised ? 'border-blue-300' : 'border-gray-300' }}"></div>

                    <!-- Payment -->
                    <div class="flex items-center space-x-1">
                        <div class="w-5 h-5 flex items-center justify-center rounded-full {{ $isCleared ? 'bg-green-600' : 'bg-gray-200' }} text-white">
                            @if($isCleared)
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                                </svg>
                            @else
                                <span class="text-[8px] font-semibold text-gray-500">P</span>
                            @endif
                        </div>
                        <span class="text-[10px] {{ $isCleared ? 'text-green-600 font-semibold' : 'text-gray-400' }}">Payment</span>
                    </div>

                    <div class="flex-1 border-t-2 border-dashed {{ $isCleared ? 'border-green-300' : 'border-gray-300' }}"></div>

                    <!-- Assessment -->
                    <div class="flex items-center space-x-1">
                        <div class="w-5 h-5 flex items-center justify-center rounded-full {{ $isEnrolled ? 'bg-indigo-600' : 'bg-gray-200' }} text-white">
                            @if($isEnrolled)
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                                </svg>
                            @else
                                <span class="text-[8px] font-semibold text-gray-500">A</span>
                            @endif
                        </div>
                        <span class="text-[10px] {{ $isEnrolled ? 'text-indigo-600 font-semibold' : 'text-gray-400' }}">Assessment</span>
                    </div>
                </div>
            </div>
        </div>
        @empty
        <div class="text-center text-gray-500 py-6 italic">
            No students found.
        </div>
        @endforelse
    </div>
</form>

<!-- Verify Payment Modal -->
<div
    x-show="showPaymentModal"
    x-cloak
    class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4 sm:p-6"
>
    <div
        @click.away="close()"
        class="bg-white rounded-xl shadow-xl w-full max-w-6xl max-h-[95vh] "
    >
        <!-- Header -->
        <div class="px-6 py-4 border-b flex justify-between items-start">
            <h3 class="text-xl font-bold text-gray-800">Verify Payment</h3>
            <button @click="close()" class="text-gray-400 hover:text-gray-600 text-xl">&times;</button>
        </div>

        <div class="p-6 text-sm overflow-y-auto max-h-[calc(92vh-100px)]">
            @include('college.verify-payment-content')
        </div>

        <!-- Footer -->
        <div class="px-6 py-4 border-t flex justify-end gap-3 bg-gray-50">
            <button @click="close()" class="px-4 py-2 border rounded text-gray-600 hover:bg-gray-100">
                Cancel
            </button>
            <form :action="clearEnrollmentUrl" method="POST" @submit.prevent="submitClearForm">
                @csrf
                <button :disabled="!allMandatoryFeesPaid" type="submit" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-500 disabled:bg-gray-400 disabled:cursor-not-allowed transition"
                    :title="!allMandatoryFeesPaid ? 'All mandatory fees must be paid before clearing for assessment' : 'Clear this student for assessment'">
                    Clear Student for Assessment
                </button>
            </form>
        </div>

    </div>
</div>





</div>
<script>
function studentSelection() {
    return {
        selected: [],
        submitting: false,
        toggleAll(event) {
            const checked = event.target.checked;
            const checkboxes = document.querySelectorAll('input[name="selected_students[]"]');
            checkboxes.forEach(cb => {
                if (!cb.disabled) {
                    cb.checked = checked;
                    if (checked && !this.selected.includes(cb.value)) this.selected.push(cb.value);
                }
            });
            if (!checked) this.selected = [];
        },
        toggleOne(event, studentId) {
            if (event.target.checked) this.selected.push(studentId);
            else this.selected = this.selected.filter(id => id != studentId);
        },
        init() {
            window.addEventListener('beforeunload', (e) => {
                if (!this.submitting && this.selected.length > 0) {
                    e.preventDefault();
                    e.returnValue = "You have selected students that are not yet validated. Please validate them before leaving the page.";
                }
            });

            const form = this.$el;

            form.addEventListener('submit', (e) => {
                if (!this.submitting) {
                    if (this.selected.length > 0) {
                        const confirmed = confirm(`Are you sure you want to validate ${this.selected.length} student(s)?`);
                        if (!confirmed) {
                            e.preventDefault(); 
                            return;
                        }
                    }
                    this.submitting = true;
                }
            });
        }
    }
}

function paymentVerification() {
    return {
        showPaymentModal: false,
        studentId: null,
        studentName: '',
        studentNumber: '',
        studentCourse: '',
        studentYear: '',
        studentSection: '',
        studentEmail: '',
        studentContact: '',
        studentReligion: '',
        markPaidUrl: '',
        clearEnrollmentUrl: '',
        fees: [],
        optionalFeeShown: false,

        get mandatoryFees() {
            return this.fees.filter(f => f.requirement_level === 'mandatory');
        },

        get optionalFees() {
            return this.fees.filter(f => f.requirement_level !== 'mandatory');
        },

        get allMandatoryFeesPaid() {
            return this.mandatoryFees.every(fee => fee.payments && fee.payments.length > 0);
        },

        submitClearForm(e) {
            if (!this.allMandatoryFeesPaid) {
                e.preventDefault();
                return false;
            }
            if (!confirm('Confirm this Student for Enrollment?')) {
                e.preventDefault();
                return false;
            }
            e.target.submit();
        },

        openPaymentModal(id, studentNo, name, course, year, section, email, contact, religion) {
            this.studentId = id;
            this.studentNumber = studentNo;
            this.studentName = name;
            this.studentCourse = course;
            this.studentYear = year;
            this.studentSection = section;
            this.studentEmail = email;
            this.studentContact = contact;
            this.studentReligion = religion;
            this.markPaidUrl = `/college/students/${id}/mark-paid`;
            this.clearEnrollmentUrl = `/college/students/${id}/clear-for-enrollment`;
            this.optionalFeeShown = false;
            this.showPaymentModal = true;
            fetch(`/college/students/${id}/fees`)
                .then(res => res.json())
                .then(data => {
                    this.fees = data;
                });
        },
        close() {
            this.showPaymentModal = false;
        }
    }
}

</script>
@endsection
