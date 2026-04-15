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
            $financialStatus = $currentEnrollment ? ($currentEnrollment->financial_status ?? $currentEnrollment->computeFinancialStatus()) : null;
            $isFinanciallyClearable = in_array($financialStatus, ['PAID', 'DEFERRED']);
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
                        <span class="text-gray-400 italic text-sm">To be Advised</span>

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
                        <div class="w-5 h-5 flex items-center justify-center rounded-full {{ $isFinanciallyClearable ? 'bg-green-600' : 'bg-gray-200' }} text-white">
                            @if($isFinanciallyClearable)
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                                </svg>
                            @else
                                <span class="text-[8px] font-semibold text-gray-500">P</span>
                            @endif
                        </div>
                        <span class="text-[10px] {{ $isFinanciallyClearable ? 'text-green-600 font-semibold' : 'text-gray-400' }}">Financial</span>
                    </div>

                    <div class="flex-1 border-t-2 border-dashed {{ $isFinanciallyClearable ? 'border-green-300' : 'border-gray-300' }}"></div>

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
        @click.away="!showPromissoryPreviewModal && close()"
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
                <button :disabled="!canClearEnrollment" type="submit" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-500 disabled:bg-gray-400 disabled:cursor-not-allowed transition"
                    :title="!canClearEnrollment ? 'Student is not financially clearable yet' : 'Clear this student for assessment'">
                    Clear Student for Assessment
                </button>
            </form>
        </div>

    </div>
</div>

<div
    x-show="showPromissoryPreviewModal"
    x-cloak
    class="fixed inset-0 z-[60] flex items-center justify-center bg-black bg-opacity-50 p-4 sm:p-6"
>
    <div
        @click.away="closePromissoryPreview()"
        class="flex w-full max-w-6xl max-h-[95vh] flex-col overflow-hidden rounded-xl bg-white shadow-xl"
    >
        <div class="flex items-start justify-between border-b px-6 py-4">
            <div>
                <h3 class="text-xl font-bold text-gray-800">Preview Promissory Note</h3>
                <p class="text-sm text-gray-500">Review and adjust the note before it is created.</p>
            </div>
            <button @click="closePromissoryPreview()" class="text-xl text-gray-400 hover:text-gray-600">&times;</button>
        </div>

        <form :action="issuePromissoryNoteUrl" method="POST" @submit.prevent="submitPromissoryNote" class="flex min-h-0 flex-1 flex-col">
            @csrf
            <div class="min-h-0 flex-1 overflow-y-auto p-6">
                <div class="grid gap-6 lg:grid-cols-[1.15fr_0.85fr]">
                    <div class="space-y-4">
                        <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                            <h4 class="font-semibold text-slate-800">Student Summary</h4>
                            <dl class="mt-3 grid grid-cols-1 gap-3 sm:grid-cols-2">
                                <div>
                                    <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Student</dt>
                                    <dd class="text-sm font-medium text-slate-900" x-text="studentName"></dd>
                                </div>
                                <div>
                                    <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Student ID</dt>
                                    <dd class="text-sm font-medium text-slate-900" x-text="studentNumber"></dd>
                                </div>
                                <div>
                                    <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Course</dt>
                                    <dd class="text-sm font-medium text-slate-900" x-text="studentCourse || '—'"></dd>
                                </div>
                                <div>
                                    <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Year · Section</dt>
                                    <dd class="text-sm font-medium text-slate-900" x-text="`${studentYear || '—'} · ${studentSection || '—'}`"></dd>
                                </div>
                            </dl>
                        </div>

                        <div class="rounded-xl border border-slate-200 p-4">
                            <h4 class="font-semibold text-slate-800">Note Details</h4>
                            <div class="mt-4 grid gap-4 sm:grid-cols-2">
                                <label class="block">
                                    <span class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500">Due Date</span>
                                    <input type="date" name="due_date" x-model="promissoryPreviewDueDate" :min="promissoryPreviewDueDateMin || todayIsoDate" :max="promissoryPreviewDueDateMax || null" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-red-500 focus:ring-red-500" :class="dueDateValidationError ? 'border-red-500 bg-red-50' : ''">
                                    <p class="mt-1 text-xs text-slate-500" x-show="semesterStartDate && semesterEndDate" x-text="`Semester: ${semesterStartDate} to ${semesterEndDate}`"></p>
                                    <p class="mt-1 text-xs text-red-600 font-semibold" x-show="dueDateValidationError" x-text="dueDateValidationError"></p>
                                </label>
                                <label class="block sm:col-span-2">
                                    <span class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500">Purpose / Reason / Important Notes</span>
                                    <textarea name="notes" x-model="promissoryPreviewNotes" rows="4" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-red-500 focus:ring-red-500" placeholder="Explain why the note is being issued, special terms, or other important matters."></textarea>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-4">
                        <div class="rounded-xl border border-red-200 bg-red-50 p-4">
                            <div class="flex items-center justify-between gap-3">
                                <h4 class="font-semibold text-red-900">Selected Fees</h4>
                                <span class="text-sm font-medium text-red-800" x-text="`${selectedPromissoryFees.length} fee(s)`"></span>
                            </div>
                            <p class="mt-1 text-xs text-red-700">Only unpaid mandatory fees are eligible for this note. Deselect any fee the coordinator does not want to defer.</p>
                            <div class="mt-4 max-h-[24rem] space-y-3 overflow-y-auto pr-1">
                                <template x-for="fee in unpaidMandatoryFees" :key="fee.id">
                                    <label class="flex cursor-pointer items-start gap-3 rounded-lg border border-red-100 bg-white px-3 py-3 text-sm shadow-sm hover:border-red-300">
                                        <input type="checkbox" name="selected_fee_ids[]" class="mt-1 rounded border-gray-300 text-red-700 focus:ring-red-500" :value="String(fee.id)" x-model="selectedPromissoryFeeIds">
                                        <div class="min-w-0 flex-1">
                                            <div class="flex items-start justify-between gap-3">
                                                <div class="min-w-0">
                                                    <p class="font-semibold text-gray-900" x-text="fee.fee_name"></p>
                                                    <p class="text-xs text-gray-500" x-text="fee.organization?.name || 'College'"></p>
                                                </div>
                                                <p class="shrink-0 font-semibold text-gray-900" x-text="`₱ ${parseFloat(fee.amount || 0).toFixed(2)}`"></p>
                                            </div>
                                        </div>
                                    </label>
                                </template>
                            </div>
                        </div>

                        <div class="rounded-xl border border-gray-200 bg-gray-50 p-4">
                            <div class="flex items-center justify-between text-sm">
                                <span class="font-medium text-gray-600">Projected Amount</span>
                                <span class="text-lg font-bold text-gray-900" x-text="`₱ ${selectedPromissoryTotal.toFixed(2)}`"></span>
                            </div>
                            <div class="mt-2 text-xs text-gray-500">
                                Preview only. The note is not created until you finalize below.
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 border-t bg-gray-50 px-6 py-4">
                <button type="button" @click="closePromissoryPreview()" class="rounded-lg border px-4 py-2 text-gray-600 hover:bg-gray-100">
                    Back
                </button>
                <button type="submit" class="rounded-lg bg-red-800 px-4 py-2 font-semibold text-white transition hover:bg-red-700">
                    Finalize Promissory Note
                </button>
            </div>
        </form>
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
        showPromissoryPreviewModal: false,
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
        issuePromissoryNoteUrl: '',
        fees: [],
        optionalFeeShown: false,
        financialStatus: null,
        promissoryNote: null,
        canIssuePromissoryNote: false,
        canClearEnrollment: false,
        selectedPromissoryFeeIds: [],
        promissoryPreviewDueDate: '',
        promissoryPreviewDueDateMin: '',
        promissoryPreviewDueDateMax: '',
        promissoryPreviewNotes: '',
        semesterStartDate: '',
        semesterEndDate: '',

        get todayIsoDate() {
            return new Date().toISOString().slice(0, 10);
        },

        get mandatoryFees() {
            return this.fees.filter(f => f.requirement_level === 'mandatory');
        },

        get unpaidMandatoryFees() {
            return this.mandatoryFees.filter(fee => !fee.payments || fee.payments.length === 0);
        },

        get optionalFees() {
            return this.fees.filter(f => f.requirement_level !== 'mandatory');
        },

        get allMandatoryFeesPaid() {
            return this.mandatoryFees.every(fee => fee.payments && fee.payments.length > 0);
        },

        get activePromissoryNote() {
            return this.promissoryNote;
        },

        get selectedPromissoryFees() {
            const selectedIds = new Set(this.selectedPromissoryFeeIds.map(value => String(value)));
            return this.unpaidMandatoryFees.filter(fee => selectedIds.has(String(fee.id)));
        },

        get selectedPromissoryTotal() {
            return this.selectedPromissoryFees.reduce((total, fee) => total + parseFloat(fee.amount || 0), 0);
        },

        get dueDateValidationError() {
            if (!this.promissoryPreviewDueDate) return '';
            if (this.semesterStartDate && this.promissoryPreviewDueDate < this.semesterStartDate) {
                return `Due date must be on or after semester start (${this.semesterStartDate})`;
            }
            if (this.semesterEndDate && this.promissoryPreviewDueDate > this.semesterEndDate) {
                return `Due date must be on or before semester end (${this.semesterEndDate})`;
            }
            return '';
        },

        canSubmitPromissoryNote() {
            return !this.dueDateValidationError;
        },

        openPromissoryPreview() {
            this.selectedPromissoryFeeIds = this.unpaidMandatoryFees.map(fee => String(fee.id));
            const fallbackDueDate = this.promissoryPreviewDueDate || this.todayIsoDate;
            this.promissoryPreviewDueDate = this.promissoryPreviewDueDateMax && fallbackDueDate > this.promissoryPreviewDueDateMax
                ? this.promissoryPreviewDueDateMax
                : fallbackDueDate;
            this.promissoryPreviewNotes = this.promissoryPreviewNotes || '';
            this.showPaymentModal = false;
            this.showPromissoryPreviewModal = true;
        },

        closePromissoryPreview() {
            this.showPromissoryPreviewModal = false;
        },

        submitPromissoryNote(e) {
            if (!this.selectedPromissoryFeeIds.length) {
                alert('Select at least one fee to include in the promissory note.');
                return;
            }

            if (!this.promissoryPreviewDueDate) {
                alert('Select a due date for the promissory note.');
                return;
            }

            if (this.dueDateValidationError) {
                alert(this.dueDateValidationError);
                return;
            }

            const confirmed = confirm('Finalize and create this promissory note?');
            if (!confirmed) {
                return;
            }

            e.target.submit();
        },

        submitClearForm(e) {
            if (!this.canClearEnrollment) {
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
            this.issuePromissoryNoteUrl = `/college/students/${id}/promissory-notes`;
            this.optionalFeeShown = false;
            this.showPaymentModal = true;
            this.showPromissoryPreviewModal = false;
            fetch(`/college/students/${id}/fees`)
                .then(res => res.json())
                .then(data => {
                    this.fees = data.fees || [];
                    this.financialStatus = data.financial_status || null;
                    this.promissoryNote = data.promissory_note || null;
                    this.canIssuePromissoryNote = Boolean(data.can_issue_promissory_note);
                    this.canClearEnrollment = Boolean(data.can_clear);
                    this.promissoryPreviewDueDate = data.preview_defaults?.due_date || this.todayIsoDate;
                    this.promissoryPreviewDueDateMin = data.preview_defaults?.due_date_min || '';
                    this.promissoryPreviewDueDateMax = data.preview_defaults?.due_date_max || '';
                    this.semesterStartDate = data.preview_defaults?.semester_start_date || '';
                    this.semesterEndDate = data.preview_defaults?.semester_end_date || '';
                    this.promissoryPreviewNotes = data.preview_defaults?.notes || '';
                });
        },
        close() {
            this.showPaymentModal = false;
            this.showPromissoryPreviewModal = false;
        }
    }
}

</script>
@endsection
