@extends('layouts.dashboard')

@section('title', 'Records')
@section('page-title', 'Records')

@section('content')

<div class="max-w-7xl mx-auto space-y-8">

    <div class="flex flex-col md:flex-row md:items-center md:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-800">Payment Records</h1>
            <p class="text-sm text-gray-500 mt-1">
                Welcome back, {{ Auth::user()->name }}. Monitor and manage organization payments.
            </p>
        </div>
    </div>


    <!-- QUICK FILTER BAR -->
    <div class="bg-white border border-gray-200 rounded-xl p-5 shadow-sm">
        <form method="GET" action="{{ route('college_org.records') }}" class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">

            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">School Year</label>
                <select name="school_year_id" class="w-full h-10 border border-gray-300 rounded-lg px-3 text-sm focus:ring-2 focus:ring-red-500 focus:border-red-500">
                    <option value="">All School Years</option>
                    @foreach($schoolYears as $sy)
                    <option value="{{ $sy->id }}" {{ (request('school_year_id', $schoolYearId) == $sy->id) ? 'selected' : '' }}>
                        {{ \Carbon\Carbon::parse($sy->sy_start)->year }} - {{ \Carbon\Carbon::parse($sy->sy_end)->year }}
                    </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Semester</label>
                <select name="semester_id" class="w-full h-10 border border-gray-300 rounded-lg px-3 text-sm focus:ring-2 focus:ring-red-500 focus:border-red-500">
                    <option value="">All Semesters</option>
                    @foreach($semesters as $sem)
                    <option value="{{ $sem->id }}" {{ (request('semester_id', $semesterId) == $sem->id) ? 'selected' : '' }}>
                        {{ ucfirst($sem->name) }}
                    </option>
                    @endforeach
                </select>
            </div>

            <div class="flex gap-2">
                <button type="submit" class="flex-1 h-10 bg-red-500 text-white text-sm rounded-lg hover:bg-red-600 transition">
                    Apply
                </button>

                <a href="{{ route('college_org.records') }}" class="flex-1 h-10 border border-gray-300 text-sm rounded-lg flex items-center justify-center text-gray-600 hover:bg-gray-100 transition">
                    Reset
                </a>
            </div>

        </form>
    </div>


    <!-- SUMMARY CARDS -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-5">

        <div class="bg-white border border-gray-200 rounded-2xl p-5 shadow-sm">
            <p class="text-xs text-gray-500 uppercase tracking-wide">Transactions</p>
            <p class="text-2xl font-semibold text-gray-800 mt-2">
                {{ $totalTransactions }}
            </p>
        </div>

        <div class="bg-white border border-gray-200 rounded-2xl p-5 shadow-sm">
            <p class="text-xs text-gray-500 uppercase tracking-wide">Total Collected</p>
            <p class="text-2xl font-semibold text-green-600 mt-2">
                ₱{{ number_format($totalCollected, 2) }}
            </p>
        </div>

        <div class="bg-white border border-gray-200 rounded-2xl p-5 shadow-sm">
            <p class="text-xs text-gray-500 uppercase tracking-wide">Mandatory</p>
            <p class="text-2xl font-semibold text-red-500 mt-2">
                ₱{{ number_format($mandatoryCollected, 2) }}
            </p>
        </div>

        <div class="bg-white border border-gray-200 rounded-2xl p-5 shadow-sm">
            <p class="text-xs text-gray-500 uppercase tracking-wide">Optional</p>
            <p class="text-2xl font-semibold text-yellow-500 mt-2">
                ₱{{ number_format($optionalCollected, 2) }}
            </p>
        </div>

        <div class="bg-white border border-gray-200 rounded-2xl p-5 shadow-sm">
            <p class="text-xs text-gray-500 uppercase tracking-wide">Today</p>
            <p class="text-2xl font-semibold text-gray-800 mt-2">
                ₱{{ number_format($todayCollections, 2) }}
            </p>
        </div>

    </div>

    <div class="bg-white border border-gray-200 rounded-2xl shadow-sm overflow-hidden">
        <div class=" flex justify-between items-center px-4 py-3 border-b border-gray-100">
            <h2 class="text-sm font-semibold text-gray-700 uppercase tracking-wide">
                Payment List
            </h2>

            <div x-data="{ open: false }">
                <div class="mb-4 flex justify-end">

                    <button @click="open = true" class="bg-gray-300 inline-flex items-center gap-2 px-4 py-2 text-sm font-medium bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg transition">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2l-6 7v5l-4 2v-7L3 6V4z" /> </svg> Filters
                    </button>
                </div>
                <div x-show="open" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-50" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-50" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-black bg-opacity-50 z-40" @click="open = false"></div>

                <div x-show="open" x-transition:enter="transform transition ease-in-out duration-300" x-transition:enter-start="translate-x-full" x-transition:enter-end="translate-x-0" x-transition:leave="transform transition ease-in-out duration-300" x-transition:leave-start="translate-x-0" x-transition:leave-end="translate-x-full" class="fixed right-0 top-0 h-full w-96 bg-white shadow-lg z-50 p-6 overflow-y-auto">

                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-xl font-semibold">Filter Payments</h3>
                        <button @click="open = false" class="text-gray-500 hover:text-gray-700 text-lg font-bold">&times;</button>
                    </div>

                    <form method="GET" action="{{ route('college_org.records') }}" class="space-y-4">
                        <div>
                            <label class="block text-gray-700 mb-1">Fee</label>
                            <select name="fee_id" class="w-full border border-gray-300 rounded-md py-2 px-3 focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500">
                                <option value="">All Fees</option>
                                @foreach($fees as $fee)
                                <option value="{{ $fee->id }}" {{ request('fee_id') == $fee->id ? 'selected' : '' }}>
                                    {{ $fee->fee_name }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-gray-700 mb-1">Fee Recurrence</label>
                            <select name="fee_recurrence" class="w-full border border-gray-300 rounded-md py-2 px-3 focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500">
                                <option value="">Any</option>
                                <option value="one_time" {{ request('recurrence') == 'one_time' ? 'selected' : '' }}>One Time</option>
                                <option value="semestrial" {{ request('recurrence') == 'semestrial' ? 'selected' : '' }}>Semestrial</option>
                                <option value="annual" {{ request('recurrence') == 'annual' ? 'selected' : '' }}>Annual</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-gray-700 mb-1">Requirement Level</label>
                            <select name="requirement_level" class="w-full border border-gray-300 rounded-md py-2 px-3 focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500">
                                <option value="">All</option>
                                <option value="mandatory" {{ request('requirement_level') == 'mandatory' ? 'selected' : '' }}>Mandatory</option>
                                <option value="optional" {{ request('requirement_level') == 'optional' ? 'selected' : '' }}>Optional</option>
                            </select>
                        </div>

                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="block text-gray-700 mb-1">From</label>
                                <input type="date" name="date_from" value="{{ request('date_from') }}" class="w-full border border-gray-300 rounded-md py-2 px-3 focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500">
                            </div>
                            <div>
                                <label class="block text-gray-700 mb-1">To</label>
                                <input type="date" name="date_to" value="{{ request('date_to') }}" class="w-full border border-gray-300 rounded-md py-2 px-3 focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500">
                            </div>
                        </div>

                        <div>
                            <label class="block text-gray-700 mb-1">Course</label>
                            <select name="course_id" class="w-full border border-gray-300 rounded-md py-2 px-3 focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500">
                                <option value="">All Courses</option>
                                @foreach($courses as $course)
                                <option value="{{ $course->id }}" {{ request('course_id') == $course->id ? 'selected' : '' }}>
                                    {{ $course->name }}
                                </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="block text-gray-700 mb-1">Year Level</label>
                                <select name="year_level_id" class="w-full border border-gray-300 rounded-md py-2 px-3 focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500">
                                    <option value="">All Years</option>
                                    @foreach($yearLevels as $year)
                                    <option value="{{ $year->id }}" {{ request('year_level_id') == $year->id ? 'selected' : '' }}>
                                        {{ $year->name }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-gray-700 mb-1">Section</label>
                                <select name="section_id" class="w-full border border-gray-300 rounded-md py-2 px-3 focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500">
                                    <option value="">All Sections</option>
                                    @foreach($sections as $section)
                                    <option value="{{ $section->id }}" {{ request('section_id') == $section->id ? 'selected' : '' }}>
                                        {{ $section->name }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="flex justify-end space-x-2 mt-4">
                            <button type="submit" class="px-4 py-2 bg-red-500 text-white rounded-md hover:bg-red-600">Apply Filters</button>
                            <button type="button" onclick="window.location='{{ route('college_org.records') }}'" class="px-4 py-2 border rounded-md text-gray-700 hover:bg-gray-100">
                                Reset
                            </button>
                        </div>
                         <button type="button" @click="$dispatch('open-report-modal')" class="w-full px-4 py-2 bg-gray-800 text-white rounded-lg text-sm hover:bg-gray-900 transition">
                                Generate Report
                            </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full text-sm text-gray-700">
                <thead class="bg-gray-50 text-xs uppercase text-gray-500">
                    <tr>
                        <th class="px-6 py-3 text-left">Student</th>
                        <th class="px-6 py-3 text-left">Fee</th>
                        <th class="px-6 py-3 text-left">Amount</th>
                        <th class="px-6 py-3 text-left">Course</th>
                        <th class="px-6 py-3 text-left">Year</th>
                        <th class="px-6 py-3 text-left">Section</th>
                        <th class="px-6 py-3 text-left">Date</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-100">
                    @forelse($payments as $payment)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-6 py-4">
                            <div class="font-medium text-gray-800">
                                {{ $payment->student->last_name }}, {{ $payment->student->first_name }}
                            </div>
                            <div class="text-xs text-gray-500">
                                {{ $payment->student->student_id }}
                            </div>
                        </td>

                        <td class="px-6 py-4">
                            @foreach($payment->fees as $fee)
                            <div>{{ $fee->fee_name }}</div>
                            @endforeach
                        </td>

                        <td class="px-6 py-4 font-semibold text-green-600">
                            ₱{{ number_format($payment->amount_due, 2) }}
                        </td>

                        <td class="px-6 py-4">{{ $payment->enrollment->course->name ?? '-' }}</td>
                        <td class="px-6 py-4">{{ $payment->enrollment->yearLevel->name ?? '-' }}</td>
                        <td class="px-6 py-4">{{ $payment->enrollment->section->name ?? '-' }}</td>
                        <td class="px-6 py-4 text-gray-500">
                            {{ $payment->created_at->format('M d, Y') }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-10 text-center text-gray-500">
                            No payment records found.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

<div x-data="{ open: false }" x-on:open-report-modal.window="open = true">
    <div x-show="open" class="fixed inset-0 bg-black bg-opacity-40 z-50" x-transition @click="open = false">
    </div>

    <div x-show="open" x-transition class="fixed inset-0 flex items-center justify-center z-50">

        <div class="bg-white w-96 rounded-2xl shadow-xl p-6 space-y-6">

            <div class="text-center">
                <h3 class="text-lg font-semibold text-gray-800">
                    Generate Payment Report
                </h3>
                <p class="text-sm text-gray-500 mt-1">
                    Choose report format
                </p>
            </div>

            <div class="space-y-3">

                <!-- PDF -->
                <a href="{{ route('college_org.generate_report', array_merge(request()->all(), ['format' => 'pdf'])) }}" target="_blank" class="block w-full text-center px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 transition">
                    Generate as PDF
                </a>

                <!-- Excel -->
                <a href="{{ route('college_org.generate_report', array_merge(request()->all(), ['format' => 'excel'])) }}" class="block w-full text-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition">
                    Generate as Excel
                </a>

            </div>

            <button @click="open = false" class="w-full text-sm text-gray-500 hover:text-gray-700">
                Cancel
            </button>

        </div>
    </div>
</div>

@endsection
