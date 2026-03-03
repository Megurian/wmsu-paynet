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
        <!-- Your search, course, year, section inputs -->

        <div>
            <select id="filter-sy" name="school_year_id" class="w-full border border-gray-300 rounded-md py-2 px-3 focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500">
                <option value="">School Year</option>
                @foreach($schoolYears as $sy)
                <option value="{{ $sy->id }}" {{ request('school_year_id') == $sy->id ? 'selected' : '' }}>
                    {{ \Carbon\Carbon::parse($sy->sy_start)->year }} - {{ \Carbon\Carbon::parse($sy->sy_end)->year }}
                </option>
                @endforeach
            </select>
        </div>

        <!-- Semester Dropdown -->
        <div>
            <select id="filter-sem" name="semester_id" class="w-full border border-gray-300 rounded-md py-2 px-3 focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500">
                <option value="">Semester</option>
                @foreach($semesters as $sem)
                <option value="{{ $sem->id }}" {{ request('semester_id') == $sem->id ? 'selected' : '' }}>
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

<!-- Records Section -->
<div class="mb-8">
    <div class="mt-4 bg-white p-4 rounded-lg shadow-sm border border-gray-200">
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
            <!-- Search Bar -->
            <div class="relative">
                <input type="text" id="search" placeholder="Search..." class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" />
                    </svg>
                </div>
            </div>

            <!-- Course Dropdown -->
            <div>
                <select class="w-full border border-gray-300 rounded-md py-2 px-3 focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500">
                    <option value="">Course</option>
                    <option value="CS">Computer Science</option>
                    <option value="IT">Information Technology</option>
                    <option value="ACT">Associate in Computer Technology</option>
                    <option value="IS">Information Systems</option>
                </select>
            </div>

            <!-- Year Level Dropdown -->
            <div>
                <select class="w-full border border-gray-300 rounded-md py-2 px-3 focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500">
                    <option value="">Year Level</option>
                    <option value="1">1st Year</option>
                    <option value="2">2nd Year</option>
                    <option value="3">3rd Year</option>
                    <option value="4">4th Year</option>
                    <option value="5">5th Year</option>
                </select>
            </div>

            <!-- Section Dropdown -->
            <div>
                <select class="w-full border border-gray-300 rounded-md py-2 px-3 focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500">
                    <option value="">Section</option>
                    <option value="A">A</option>
                    <option value="B">B</option>
                    <option value="C">C</option>
                    <option value="D">D</option>
                </select>
            </div>

            <!-- Date Picker -->
            <div>
                <input type="date" class="w-full border border-gray-300 rounded-md py-2 px-3 focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500">
            </div>
        </div>
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
                    <td class="py-2 px-4 border-b">
                        {{ $payment->student->student_id }}
                    </td>

                    <td class="py-2 px-4 border-b">
                        {{ $payment->student->last_name }},
                        {{ $payment->student->first_name }}
                    </td>

                    <td class="py-2 px-4 border-b">
                        @foreach($payment->fees as $fee)
                        <div>{{ $fee->fee_name }}</div>
                        @endforeach
                    </td>

                    <td class="py-2 px-4 border-b font-semibold text-green-600">
                        ₱{{ number_format($payment->amount_due, 2) }}
                    </td>

                    <td class="py-2 px-4 border-b">
                        {{ $payment->enrollment->course->name ?? '-' }}
                    </td>

                    <td class="py-2 px-4 border-b">
                        {{ $payment->enrollment->yearLevel->name ?? '-' }}
                    </td>

                    <td class="py-2 px-4 border-b">
                        {{ $payment->enrollment->section->name ?? '-' }}
                    </td>

                    <td class="py-2 px-4 border-b">
                        {{ $payment->created_at->format('M d, Y') }}
                    </td>

                    <td class="py-2 px-4 border-b">
                        {{-- receipt link removed; receipts will be emailed --}}
                        <span class="text-gray-500 text-sm">emailed</span>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="text-center py-6 text-gray-500">
                        No payment records found.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
