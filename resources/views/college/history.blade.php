@extends('layouts.dashboard')

@section('title', 'Report & History')
@section('page-title', 'Report & History')

@section('content')
@php
    $formatSemesterName = static function (?string $name): string {
        $name = trim((string) $name);
        $normalized = strtolower($name);

        if ($normalized === 'summer' || $normalized === 'summer semester') {
            return 'SUMMER';
        }

        if (preg_match('/^(\d+)(st|nd|rd|th)?(?:\s+semester)?$/i', $normalized, $matches)) {
            $number = (int) $matches[1];
            $suffix = in_array($number % 100, [11, 12, 13], true)
                ? 'th'
                : match ($number % 10) {
                    1 => 'st',
                    2 => 'nd',
                    3 => 'rd',
                    default => 'th',
                };

            return $number . $suffix . ' SEMESTER';
        }

        return $name !== '' ? strtoupper($name) : '';
    };
@endphp
<div x-data="{ openFilter: false }">
    {{--
<div class="mb-6">
    <h2 class="text-2xl font-semibold text-gray-800">Student History</h2>
    <p class="text-sm text-gray-500 mt-1">
        View historical enrollment records filtered by school year and semester.
    </p>
</div> --}}

    <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-4 mb-4">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-6">
            @if($selectedSchoolYear)
            <div class="md:w-1/2">
                <div class="flex items-start gap-3 bg-gray-50 border border-gray-200 rounded-lg p-4">
                    <div>
                        <p class="text-sm font-semibold text-gray-800">
                            AY {{ \Carbon\Carbon::parse($selectedSchoolYear->sy_start)->year }}
                            –
                            {{ \Carbon\Carbon::parse($selectedSchoolYear->sy_end)->year }}
                            • {{ $formatSemesterName($selectedSem) }}
                        </p>

                        <p class="text-xs text-gray-500 mt-1">
                            {{ \Carbon\Carbon::parse($selectedSchoolYear->sy_start)->format('F d, Y') }}
                            –
                            {{ \Carbon\Carbon::parse($selectedSchoolYear->sy_end)->format('F d, Y') }}
                        </p>
                    </div>
                </div>
            </div>
            @endif

            <form method="GET" class="grid grid-cols-1 sm:grid-cols-3 gap-4 md:w-1/2 items-end">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">
                        School Year
                    </label>
                    <select name="school_year" onchange="this.form.submit()" class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm
                           focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition">
                        <option value="">All School Years</option>
                        @foreach($schoolYears as $sy)
                        <option value="{{ $sy->id }}" @selected($selectedSY==$sy->id)>
                            {{ \Carbon\Carbon::parse($sy->sy_start)->year }}
                            –
                            {{ \Carbon\Carbon::parse($sy->sy_end)->year }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">
                        Semester
                    </label>
                    <select name="semester" onchange="this.form.submit()" class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm
                           focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition">
                        @foreach(['1st SEMESTER', '2nd SEMESTER', 'SUMMER'] as $semName)
                        <option value="{{ $semName }}" @selected($selectedSem==$semName)>
                            {{ $semName }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="flex sm:justify-end">
                    <a href="{{ route(request()->route()->getName()) }}" class="text-sm text-gray-500 hover:text-gray-700 transition">
                        Reset
                    </a>
                </div>
            </form>
        </div>


        @php
        $tab = request('tab', 'enrollments');
        @endphp
        <div class="mt-4 border-b border-gray-200">
            <nav class="-px flex space-x-4" aria-label="Tabs">
                <a href="{{ route('college.history', array_merge(request()->query(), ['tab' => 'enrollments'])) }}" class="px-3 py-2 font-medium text-sm rounded-t-lg
                      @if(request('tab', 'enrollments') === 'enrollments')
                          bg-blue-100 text-blue-700
                      @else
                          text-gray-500 hover:text-gray-700
                      @endif
                      ">
                    Enrollment Records
                </a>

                <a href="{{ route('college.history', array_merge(request()->query(), ['tab' => 'payments'])) }}" class="px-3 py-2 font-medium text-sm rounded-t-lg
                      @if(request('tab') === 'payments')
                          bg-blue-100 text-blue-700
                      @else
                          text-gray-500 hover:text-gray-700
                      @endif
                      ">
                    Payment Records
                </a>
            </nav>
        </div>
    </div>

    <div x-cloak x-show="openFilter" x-transition.opacity class="fixed inset-0 bg-black/30 z-40" @click="openFilter = false" style="display: none;">
    </div>

    <div x-cloak x-show="openFilter" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="translate-x-full" x-transition:enter-end="translate-x-0" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="translate-x-0" x-transition:leave-end="translate-x-full" class="fixed right-0 top-0 h-full w-80 bg-white shadow-xl z-50 overflow-y-auto" style="display: none;">

        <div class="p-6 border-b border-gray-200 flex justify-between items-center">
            <h2 class="text-lg font-semibold text-gray-800">Filters</h2>
            <button @click="openFilter = false" class="text-gray-500 hover:text-gray-700 text-xl">&times;</button>
        </div>

        @if($tab === 'enrollments')
        <form method="GET" class="p-6 space-y-5">

            <input type="hidden" name="tab" value="enrollments">
            <input type="hidden" name="school_year" value="{{ $selectedSY }}">
            <input type="hidden" name="semester" value="{{ $selectedSem }}">

            <!-- Course -->
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Course</label>
                <select name="course" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    <option value="">All Courses</option>
                    @foreach($courses as $course)
                    <option value="{{ $course->id }}" @selected($selectedCourse==$course->id)>
                        {{ $course->name }}
                    </option>
                    @endforeach
                </select>
            </div>

            <!-- Year -->
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Year</label>
                <select name="year" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    <option value="">All Years</option>
                    @foreach($years as $year)
                    <option value="{{ $year->id }}" @selected($selectedYear==$year->id)>
                        {{ $year->name }}
                    </option>
                    @endforeach
                </select>
            </div>

            <!-- Section -->
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Section</label>
                <select name="section" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    <option value="">All Sections</option>
                    @foreach($sections as $section)
                    <option value="{{ $section->id }}" @selected($selectedSection==$section->id)>
                        {{ $section->name }}
                    </option>
                    @endforeach
                </select>
            </div>

            <!-- Adviser -->
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Adviser</label>
                <select name="adviser" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    <option value="">All Advisers</option>
                    @foreach($advisers as $adviser)
                    <option value="{{ $adviser->id }}" @selected($selectedAdviser==$adviser->id)>
                        {{ $adviser->first_name }} {{ $adviser->last_name }}
                    </option>
                    @endforeach
                </select>
            </div>

            <!-- Status -->
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Status</label>
                <select name="status" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    <option value="">All Status</option>
                    <option value="assessed" @selected($selectedStatus=='assessed' )>Assessed</option>
                    <option value="to_assess" @selected($selectedStatus=='to_assess' )>To be Assessed</option>
                    <option value="pending_payment" @selected($selectedStatus=='pending_payment' )>Pending Payment</option>
                    <option value="not_enrolled" @selected($selectedStatus=='not_enrolled' )>Not Enrolled</option>
                </select>
            </div>

            <div class="pt-4 flex gap-3">
                <button type="submit" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium py-2 rounded-lg transition">
                    Apply Filters
                </button>

                <a href="{{ route('college.history', ['tab'=>'enrollments']) }}" class="flex-1 text-center text-sm text-gray-500 hover:text-gray-700 py-2">
                    Reset
                </a>
            </div>
        </form>
        @endif

        @if($tab === 'payments')
        <div x-data="paymentsFilter()" x-init="init()" class="space-y-5">
            <form method="GET" class="p-6 space-y-5" id="payments-filter-form">
                <input type="hidden" name="tab" value="payments">
                <input type="hidden" name="school_year" value="{{ $selectedSY }}">
                <input type="hidden" name="semester" value="{{ $selectedSem }}">

                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Organization</label>
                    <select name="organization" id="organization-select" @change="onOrgChange($event.target.value)" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        <option value="">Select Organization</option>
                        <option value="college_only" @selected($selectedOrganization=='college_only' )>College Only</option>
                        @foreach($organizations as $org)
                        <option value="{{ $org->id }}" @selected($selectedOrganization==$org->id)>{{ $org->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div id="fee-filter" class="{{ $selectedOrganization ? '' : 'hidden' }}">
                    <label class="block text-xs font-medium text-gray-600 mb-1">Fee</label>
                    <select name="fee" id="fee-select" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        <option value="">All Fees</option>
                        @foreach($fees as $fee)
                        <option value="{{ $fee->id }}" @selected(request('fee')==$fee->id)>
                            {{ $fee->fee_name }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Date Range</label>
                    <div class="flex gap-2">
                        <input type="date" name="from_date" value="{{ request('from_date') }}" class="w-1/2 rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        <input type="date" name="to_date" value="{{ request('to_date') }}" class="w-1/2 rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Requirement Level</label>
                    <select name="requirement_level" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        <option value="">All</option>
                        <option value="mandatory" @selected(request('requirement_level')=='mandatory' )>Mandatory</option>
                        <option value="optional" @selected(request('requirement_level')=='optional' )>Optional</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Fee Recurrence</label>
                    <select name="recurrence" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        <option value="">All</option>
                        <option value="one_time" @selected(request('recurrence')=='one_time' )>One-time</option>
                        <option value="semestrial" @selected(request('recurrence')=='recurring' )>Semestrial</option>
                        <option value="annual" @selected(request('recurrence')=='recurring' )>Annual</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Payment Status</label>
                    <select name="payment_status" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        <option value="">All</option>
                        <option value="paid" @selected(request('payment_status')=='paid' )>
                            Paid
                        </option>
                        <option value="unpaid" @selected(request('payment_status')=='unpaid' )>
                            Unpaid
                        </option>
                    </select>
                </div>

                <div class="pt-4 flex gap-3">
                    <button type="submit" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium py-2 rounded-lg transition">
                        Apply Filters
                    </button>
                    <a href="{{ route('college.history', ['tab'=>'payments']) }}" class="flex-1 text-center text-sm text-gray-500 hover:text-gray-700 py-2">
                        Reset
                    </a>
                </div>
            </form>
        </div>
        @endif

         <button type="button" @click="$dispatch('open-report-modal')" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium py-2 rounded-lg transition">
                Generate Report
            </button>

         <!-- Report Format Modal -->
        <div x-data="{ open:false }" x-on:open-report-modal.window="open=true">

            <!-- Overlay -->
            <div x-cloak x-show="open" x-transition.opacity class="fixed inset-0 bg-black/40 z-50" @click="open=false" style="display: none;">
            </div>

            <!-- Modal -->
            <div x-cloak x-show="open" x-transition class="fixed inset-0 flex items-center justify-center z-50" style="display: none;">

                <div class="bg-white w-96 rounded-xl shadow-xl p-6 space-y-5">
                    <div>
                        <h2 class="text-lg font-semibold text-gray-800">
                            Generate Report
                        </h2>
                        <p class="text-sm text-gray-500 mt-1">
                            Choose report format for the current filtered data.
                        </p>
                    </div>

                    <div class="space-y-3">

                        <!-- PDF -->
                        <a href="{{ route('college.history.report', array_merge(request()->query(), ['format'=>'pdf'])) }}" class="block w-full text-center bg-red-600 hover:bg-red-700 text-white py-2 rounded-lg text-sm font-medium transition">
                            Download as PDF
                        </a>

                        <!-- Excel -->
                        <a href="{{ route('college.history.report', array_merge(request()->query(), ['format'=>'excel'])) }}" class="block w-full text-center bg-green-600 hover:bg-green-700 text-white py-2 rounded-lg text-sm font-medium transition">
                            Download as Excel
                        </a>
                    </div>

                    <div class="text-right">
                        <button @click="open=false" class="text-sm text-gray-500 hover:text-gray-700">
                            Cancel
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <div class="bg-white border border-gray-200 shadow-sm p-4 overflow-hidden mt-2">
        @if($tab === 'enrollments')
        @include('college.enrollments')
        @elseif($tab === 'payments')
        @include('college.payments')
        @endif
    </div>
</div>


<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('paymentsFilter', () => ({
            feeFilter: null
            , feeSelect: null
            , orgSelect: null,

            init() {
                this.feeFilter = document.getElementById('fee-filter');
                this.feeSelect = document.getElementById('fee-select');
                this.orgSelect = document.getElementById('organization-select');

                // show feeFilter if organization already selected
                if (this.orgSelect.value) {
                    this.feeFilter.classList.remove('hidden');
                }
            },

            async onOrgChange(orgId) {
                if (!orgId) {
                    this.feeFilter.classList.add('hidden');
                    this.feeSelect.innerHTML = '<option value="">All Fees</option>';
                    return;
                }

                this.feeFilter.classList.remove('hidden');

                const url = `/college/history/fees?organization=${orgId}&school_year={{ $selectedSY ?? '' }}&semester={{ $selectedSem ?? '' }}`;
                const res = await fetch(url);
                const data = await res.json();

                this.feeSelect.innerHTML = '<option value="">All Fees</option>';
                data.forEach(fee => {
                    const option = document.createElement('option');
                    option.value = fee.id;
                    option.textContent = fee.fee_name;
                    this.feeSelect.appendChild(option);
                });
            }
        }))
    })

</script>
@endsection
