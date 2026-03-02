@extends('layouts.dashboard')

@section('title', 'Report & History')
@section('page-title', 'Report & History')

@section('content')
<div x-data="{ openFilter: false }">
    {{--
<div class="mb-6">
    <h2 class="text-2xl font-semibold text-gray-800">Student History</h2>
    <p class="text-sm text-gray-500 mt-1">
        View historical enrollment records filtered by school year and semester.
    </p>
</div> --}}

    <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-4 mb-6">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-6">
            @if($selectedSchoolYear)
            <div class="md:w-1/2">
                <div class="flex items-start gap-3 bg-gray-50 border border-gray-200 rounded-lg p-4">
                    <div>
                        <p class="text-sm font-semibold text-gray-800">
                            AY {{ \Carbon\Carbon::parse($selectedSchoolYear->sy_start)->year }}
                            –
                            {{ \Carbon\Carbon::parse($selectedSchoolYear->sy_end)->year }}
                            • {{ ucfirst($selectedSem) }} Semester
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
                        @foreach(['1st', '2nd', 'summer'] as $semName)
                        <option value="{{ $semName }}" @selected($selectedSem==$semName)>
                            {{ ucfirst($semName) }} Semester
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

    <div x-show="openFilter" x-transition.opacity class="fixed inset-0 bg-black/30 z-40" @click="openFilter = false">
    </div>

    <div x-show="openFilter" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="translate-x-full" x-transition:enter-end="translate-x-0" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="translate-x-0" x-transition:leave-end="translate-x-full" class="fixed right-0 top-0 h-full w-80 bg-white shadow-xl z-50 overflow-y-auto">

        <div class="p-6 border-b border-gray-200 flex justify-between items-center">
            <h2 class="text-lg font-semibold text-gray-800">Filters</h2>
            <button @click="openFilter = false" class="text-gray-500 hover:text-gray-700 text-xl">&times;</button>
        </div>

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
    </div>
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">

        @if($tab === 'enrollments')

        @if($students->isEmpty())
        <div class="p-8 text-center">
            <p class="text-gray-500 text-sm">
                No student history found for the selected filters.
            </p>
            <p class="text-xs text-gray-400 mt-1">
                Try adjusting the school year or semester.
            </p>
        </div>
        @else
        <table class="min-w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr class="text-left text-[11px] font-semibold uppercase tracking-wide text-gray-600">
                    <th class="px-4 py-3 text-center">#</th>
                    <th class="px-5 py-3">Student ID</th>
                    <th class="px-5 py-3">Name</th>
                    <th class="px-5 py-3">Course</th>
                    <th class="px-5 py-3">Year & Section</th>
                    <th class="px-5 py-3">Adviser</th>
                    <th class="px-5 py-3">Status</th>
                    <th class="px-5 py-3">
                        @if($tab === 'enrollments')
                            <button @click="openFilter = true" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium
                                bg-gray-100 hover:bg-gray-200 text-gray-700
                                rounded-lg transition">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2l-6 7v5l-4 2v-7L3 6V4z" />
                                </svg>
                                Filters
                            </button>
                        @endif
                    </th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 text-gray-700">
                @foreach($students as $student)
                <tr class="hover:bg-gray-50 transition">
                    <td class="px-4 py-3 text-center text-gray-500">{{ $loop->iteration }}</td>
                    <td class="px-5 py-3 font-medium">{{ $student->student->student_id }}</td>
                    <td class="px-5 py-3">
                        {{ strtoupper($student->student->last_name) }},
                        {{ strtoupper($student->student->first_name) }}
                        {{ strtoupper($student->student->middle_name) }}.
                        {{ strtoupper($student->student->suffix) }}
                    </td>
                    <td class="px-5 py-3">{{ $student->course?->name ?? '—' }}</td>
                    <td class="px-5 py-3">{{ $student->yearLevel?->name ?? '—' }} {{ $student->section?->name ?? '—' }}</td>
                    <td class="px-5 py-3">
                        {{ $student->adviser?->first_name ?? '—' }}
                        {{ $student->adviser?->middle_name ?? '' }}
                        {{ $student->adviser?->last_name ?? '—' }}
                    </td>
                    <td class="px-5 py-3">
                        @php
                        if($student->assessed_at) {
                        $status = 'Assessed';
                        $badgeColor = 'bg-green-100 text-green-700';
                        } elseif($student->validated_at) {
                        $status = 'To be Assessed';
                        $badgeColor = 'bg-yellow-100 text-yellow-700';
                        } elseif($student->advised_at) {
                        $status = 'Pending Payment';
                        $badgeColor = 'bg-blue-100 text-blue-700';
                        } else {
                        $status = 'Not Enrolled';
                        $badgeColor = 'bg-gray-100 text-gray-500';
                        }
                        @endphp
                        <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $badgeColor }}">
                            {{ $status }}
                        </span>
                    </td>
                    <td class="px-5 py-3">
                        <a href="{{ route('college.students.history', $student->student->id) }}" class="inline-block px-3 py-1 text-xs font-semibold text-white bg-blue-600 rounded hover:bg-blue-700 transition">
                            View
                        </a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif

        @elseif($tab === 'payments')
        @if($payments->isEmpty())
        <div class="p-8 text-center">
            <p class="text-gray-500 text-sm">No payment records found for the selected filters.</p>
        </div>
        @else
        <table class="min-w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr class="text-left text-[11px] font-semibold uppercase tracking-wide text-gray-600">
                    <th class="px-4 py-3">#</th>
                    <th class="px-5 py-3">Organization</th>
                    <th class="px-5 py-3">Student</th>
                    <th class="px-5 py-3">Fee Name</th>
                    <th class="px-5 py-3">Amount Paid</th>
                    <th class="px-5 py-3">Date Paid</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 text-gray-700">
                @foreach($payments as $payment)
                @foreach($payment->fees as $fee)
                <tr class="hover:bg-gray-50 transition">
                    <td class="px-4 py-3 text-gray-500">{{ $loop->parent->iteration }}</td>

                    {{-- Organization with logo --}}
                    <td class="px-5 py-3 flex items-center gap-2">
                        @if($payment->organization?->logo)
                        <img src="{{ asset('storage/' . $payment->organization->logo) }}" alt="{{ $payment->organization->name }}" class="w-6 h-6 rounded-full object-cover">
                        @endif
                        <span>{{ $payment->organization?->name ?? '—' }}</span>
                    </td>

                    <td class="px-5 py-3">
                        {{ strtoupper($payment->student->last_name) }},
                        {{ strtoupper($payment->student->first_name) }}
                    </td>
                    <td class="px-5 py-3">{{ $fee->fee_name }}</td>
                    <td class="px-5 py-3">{{ number_format($fee->pivot->amount_paid, 2) }}</td>
                    <td class="px-5 py-3">{{ $payment->created_at->format('F d, Y H:i') }}</td>
                </tr>
                @endforeach
                @endforeach
            </tbody>
        </table>
        @endif
        @endif


    </div>
</div>
@endsection
