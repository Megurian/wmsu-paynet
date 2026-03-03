@extends('layouts.dashboard')

@section('title', 'Records')
@section('page-title', 'Records')

@section('content')
<div class="mb-8">
    <h2 class="text-3xl font-bold text-gray-800">Records</h2>
    <p class="text-sm text-gray-500 mt-1">
        Welcome, {{ Auth::user()->name }}. Here you can manage the record of your organization.
    </p>
</div>

<form method="GET" action="{{ route('college_org.records') }}">
    <div class="grid grid-cols-1 md:grid-cols-7 gap-4">
        <div>
            <select id="filter-sy" name="school_year_id" class="w-full border border-gray-300 rounded-md py-2 px-3 focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500">
                <option value="">School Year</option>
                @foreach($schoolYears as $sy)
                <option value="{{ $sy->id }}" {{ (request('school_year_id', $schoolYearId) == $sy->id) ? 'selected' : '' }}>
                    {{ \Carbon\Carbon::parse($sy->sy_start)->year }} - {{ \Carbon\Carbon::parse($sy->sy_end)->year }}
                </option>
                @endforeach
            </select>
        </div>

        <div>
            <select id="filter-sem" name="semester_id" class="w-full border border-gray-300 rounded-md py-2 px-3 focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500">
                <option value="">Semester</option>
                @foreach($semesters as $sem)
                <option value="{{ $sem->id }}" {{ (request('semester_id', $semesterId) == $sem->id) ? 'selected' : '' }}>
                    {{ ucfirst($sem->name) }}
                </option>
                @endforeach
            </select>
        </div>
        <div>
            <button type="submit" class="w-full bg-red-500 text-white py-2 px-4 rounded-md hover:bg-red-600">
                Filter
            </button>
        </div>
    </div>
</form>


<div x-data="{ open: false }">

    <div class="mb-4 flex justify-end">

        <button @click="open = true" class="bg-gray-300 inline-flex items-center gap-2 px-4 py-2 text-sm font-medium bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg transition"> <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2l-6 7v5l-4 2v-7L3 6V4z" /> </svg> Filters </button>
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
        </form>
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-5 gap-6 mb-6">
    <div class="bg-blue-50 border border-blue-200 rounded-xl p-5 shadow-sm">
        <p class="text-sm text-blue-600 font-medium">Total Transactions</p>
        <p class="text-2xl font-bold text-blue-800 mt-2">
            {{ $totalTransactions }}
        </p>
    </div>

    <div class="bg-green-50 border border-green-200 rounded-xl p-5 shadow-sm">
        <p class="text-sm text-green-600 font-medium">Total Collected</p>
        <p class="text-2xl font-bold text-green-800 mt-2">
            ₱{{ number_format($totalCollected, 2) }}
        </p>
    </div>

    <div class="bg-red-50 border border-red-200 rounded-xl p-5 shadow-sm">
        <p class="text-sm text-red-600 font-medium">Mandatory Collected</p>
        <p class="text-2xl font-bold text-red-800 mt-2">
            ₱{{ number_format($mandatoryCollected, 2) }}
        </p>
    </div>

    <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-5 shadow-sm">
        <p class="text-sm text-yellow-600 font-medium">Optional Collected</p>
        <p class="text-2xl font-bold text-yellow-800 mt-2">
            ₱{{ number_format($optionalCollected, 2) }}
        </p>
    </div>

    <div class="bg-gray-100 border border-gray-300 rounded-xl p-5 shadow-sm">
        <p class="text-sm text-gray-600 font-medium">Today's Collection</p>
        <p class="text-2xl font-bold text-gray-800 mt-2">
            ₱{{ number_format($todayCollections, 2) }}
        </p>
    </div>

</div>

<div class="bg-white rounded-lg shadow-md p-6">
    <div class="overflow-x-auto">
        <table class="min-w-full bg-white">
            <thead>
                <tr class="bg-gray-100">
                    <th class="py-2 px-4 border-b text-left">Student ID</th>
                    <th class="py-2 px-4 border-b text-left">Name</th>
                    <th class="py-2 px-4 border-b text-left">Fee</th>
                    <th class="py-2 px-4 border-b text-left">Amount</th>
                    <th class="py-2 px-4 border-b text-left">Course</th>
                    <th class="py-2 px-4 border-b text-left">Year</th>
                    <th class="py-2 px-4 border-b text-left">Section</th>
                    <th class="py-2 px-4 border-b text-left">Date</th>
                    <th class="py-2 px-4 border-b text-left">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($payments as $payment)
                <tr class="hover:bg-gray-50">
                    <td class="py-2 px-4 border-b">{{ $payment->student->student_id }}</td>
                    <td class="py-2 px-4 border-b">{{ $payment->student->last_name }}, {{ $payment->student->first_name }}</td>
                    <td class="py-2 px-4 border-b">
                        @foreach($payment->fees as $fee)
                        <div>{{ $fee->fee_name }}</div>
                        @endforeach
                    </td>
                    <td class="py-2 px-4 border-b font-semibold text-green-600">₱{{ number_format($payment->amount_due, 2) }}</td>
                    <td class="py-2 px-4 border-b">{{ $payment->enrollment->course->name ?? '-' }}</td>
                    <td class="py-2 px-4 border-b">{{ $payment->enrollment->yearLevel->name ?? '-' }}</td>
                    <td class="py-2 px-4 border-b">{{ $payment->enrollment->section->name ?? '-' }}</td>
                    <td class="py-2 px-4 border-b">{{ $payment->created_at->format('M d, Y') }}</td>
                    <td class="py-2 px-4 border-b"><span class="text-gray-500 text-sm">emailed</span></td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="text-center py-6 text-gray-500">No payment records found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
