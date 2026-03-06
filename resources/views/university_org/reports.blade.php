@extends('layouts.dashboard')

@section('title', 'Payment Collection Report')
@section('page-title', 'Payment Collection Report')

@section('content')
<div class="space-y-6">

    <div class="p-4 bg-gray-100 rounded mb-4 flex items-center space-x-4">
        <form method="GET" class="flex space-x-2 items-end">
            <div>
                <label class="block text-sm font-medium text-gray-700">School Year</label>
                <select name="school_year_id" class="border-gray-300 rounded">
                    @foreach($schoolYears as $sy)
                    <option value="{{ $sy->id }}" {{ $selectedSY && $selectedSY->id == $sy->id ? 'selected' : '' }}>
                        {{ \Carbon\Carbon::parse($sy->sy_start)->year }} - {{ \Carbon\Carbon::parse($sy->sy_end)->year }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Semester</label>
                <select name="semester_id" class="border-gray-300 rounded">
                    @foreach($semesters as $sem)
                    <option value="{{ $sem->id }}" {{ $selectedSem && $selectedSem->id == $sem->id ? 'selected' : '' }}>
                        {{ ucfirst($sem->name) }}
                    </option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded text-sm">Filter</button>
        </form>
    </div>


    @if($motherOrg)
    <div class="p-4 bg-gray-100 rounded shadow">
        <h2 class="text-lg font-semibold">University Organization:</h2>
        <div class="flex items-center mt-2 space-x-4">
            @if($motherOrg->logo)
            <img src="{{ asset('storage/' . $motherOrg->logo) }}" alt="Logo" class="w-16 h-16 object-contain rounded border">
            @endif
            <div>
                <p class="font-medium">{{ $motherOrg->name }}</p>
                <p class="text-sm text-gray-600">{{ $motherOrg->org_code }}</p>
            </div>
        </div>
    </div>

    {{-- Summary Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <div class="p-4 bg-blue-100 rounded shadow flex flex-col justify-between">
            <div>
                <p class="text-sm text-gray-600">Total Child Organizations</p>
                <p class="text-2xl font-bold">{{ $totalChildOrgs }}</p>
            </div>
            <a href="#childOrgsSection" class="text-blue-700 text-sm mt-2 hover:underline">View All</a>
        </div>

        <div class="p-4 bg-green-100 rounded shadow flex flex-col justify-between">
            <div>
                <p class="text-sm text-gray-600">Active Fees</p>
                <p class="text-2xl font-bold">{{ $totalActiveFees }}</p>
            </div>
            <a href="#feesSection" class="text-green-700 text-sm mt-2 hover:underline">View All</a>
        </div>

        <div class="p-4 bg-yellow-100 rounded shadow flex flex-col justify-between">
            <div>
                <p class="text-sm text-gray-600">Total Students Enrolled</p>
                <p class="text-2xl font-bold">{{ $totalStudentsEnrolled }}</p>
            </div>
            <a href="#studentsSection" class="text-yellow-700 text-sm mt-2 hover:underline">View All</a>
        </div>

        <div class="p-4 bg-purple-100 rounded shadow flex flex-col justify-between">
            <div>
                <p class="text-sm text-gray-600">Total Payments Collected</p>
                <p class="text-2xl font-bold">PHP {{ number_format($totalPaymentsCollected, 2) }}</p>
            </div>
            <a href="#paymentsSection" class="text-purple-700 text-sm mt-2 hover:underline">View All</a>
        </div>
    </div>

    @if($recentOrgs->isNotEmpty())
    <div class="p-4 bg-blue-50 rounded shadow mb-6">
        <h3 class="text-md font-semibold mb-2">Recently Added Organizations</h3>
        <ul class="space-y-1">
            @foreach($recentOrgs as $org)
            <li class="flex items-center space-x-2 p-2 bg-white rounded border">
                @if($org->logo)
                <img src="{{ asset('storage/' . $org->logo) }}" alt="Logo" class="w-8 h-8 object-contain rounded border">
                @endif
                <div class="text-sm text-gray-700">
                    <p class="font-medium">{{ $org->name }}</p>
                    <p class="text-xs text-gray-500">Code: {{ $org->org_code }} | College: {{ $org->college?->name ?? 'N/A' }}</p>
                </div>
            </li>
            @endforeach
        </ul>
    </div>
    @endif

    <div>
        <h3 class="text-md font-semibold mb-2">Child Organizations</h3>
        @if($childOrgs->isEmpty())
        <p class="text-gray-500">No child organizations found.</p>
        @else
        <div class="space-y-6">
            @foreach($childOrgs as $org)
            <div class="p-4 border rounded space-y-3">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-4">
                        @if($org->logo)
                        <img src="{{ asset('storage/' . $org->logo) }}" alt="Logo" class="w-12 h-12 object-contain rounded border">
                        @endif
                        <div>
                            <p class="font-medium">{{ $org->name }}</p>
                            <p class="text-sm text-gray-600">Code: {{ $org->org_code }}</p>
                            <p class="text-sm text-gray-600">College: {{ $org->college?->name ?? 'N/A' }}</p>
                        </div>
                    </div>
                    <div class="text-sm text-gray-500">
                        Admin: {{ $org->orgAdmin?->first_name ?? 'N/A' }} {{ $org->orgAdmin?->last_name ?? '' }}
                    </div>
                </div>


                {{-- Fees List --}}
                <div class="mt-2 pl-16">
                    <h4 class="text-sm font-semibold mb-1">Fees:</h4>
                    @if($org->fees->isEmpty())
                    <p class="text-gray-500 text-sm">No fees available.</p>
                    @else
                    <ul class="space-y-1">
                        @foreach($org->fees as $fee)
                        <li class="p-2 border rounded bg-gray-50 flex flex-col space-y-2">
                            <div class="flex justify-between items-center">
                                <div>
                                    <p class="font-medium">{{ $fee->fee_name }}</p>
                                    <p class="text-sm text-gray-600">{{ $fee->requirement_level }} | PHP {{ number_format($fee->amount, 2) }}</p>
                                </div>
                                <div class="text-sm text-gray-500">
                                    Status: {{ ucfirst($fee->status ?? 'pending') }}
                                </div>
                            </div>

                            <div class="mt-2 pl-4">
                                <p class="text-xs font-semibold text-gray-700">Paid Students ({{ $fee->paid_students->count() }}):</p>
                                <ul class="list-disc list-inside text-xs text-gray-600">
                                    @foreach($fee->paid_students as $student)
                                    <li>{{ $student->last_name }}, {{ $student->first_name }} ({{ $student->student_id }})</li>
                                    @endforeach
                                </ul>

                                <p class="text-xs font-semibold text-gray-700 mt-1">Pending Students ({{ $fee->pending_students->count() }}):</p>
                                <ul class="list-disc list-inside text-xs text-gray-600">
                                    @foreach($fee->pending_students as $student)
                                    <li>{{ $student->last_name }}, {{ $student->first_name }} ({{ $student->student_id }})</li>
                                    @endforeach
                                </ul>
                            </div>
                        </li>
                        @endforeach
                    </ul>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
        @endif
    </div>
    @else
    <p class="text-red-500">You do not belong to a university-level organization.</p>
    @endif
</div>
@endsection
