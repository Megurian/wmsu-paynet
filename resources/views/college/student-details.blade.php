@extends('layouts.dashboard')

@section('title', 'Student Details')
@section('page-title', 'Student Details')

@section('content')
<div class="max-w-6xl mx-auto space-y-6">

    {{-- Back Button --}}
    <div>
        <a href="{{ route('college.students') }}"
           class="text-blue-600 hover:underline text-sm font-medium">
            ← Back to Directory
        </a>
    </div>

    {{-- Student Info --}}
    <div class="bg-white rounded-2xl shadow-md border p-6 space-y-4">
        <div class="flex flex-col md:flex-row md:justify-between md:items-center">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">
                    {{ $student->last_name }}, {{ $student->first_name }}
                    {{ $student->middle_name }}
                    {{ $student->suffix }}
                </h1>
                <p class="text-sm text-gray-500 mt-1">Student ID: <span class="font-medium text-gray-700">{{ $student->student_id }}</span></p>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">

            {{-- Personal Info --}}
            <div class="space-y-4">
                <div class="bg-gray-100 rounded-xl p-4 shadow-sm flex flex-col justify-center h-20">
                    <span class="text-xs font-semibold text-gray-500">Email</span>
                    <span class="text-sm text-gray-800">{{ $student->email ?? '-' }}</span>
                </div>
                <div class="bg-gray-100 rounded-xl p-4 shadow-sm flex flex-col justify-center h-20">
                    <span class="text-xs font-semibold text-gray-500">Contact</span>
                    <span class="text-sm text-gray-800">{{ $student->contact ?? '-' }}</span>
                </div>
            </div>
            <div class="space-y-4">
                <div class="bg-gray-100 rounded-xl p-4 shadow-sm flex flex-col justify-center h-20">
                    <span class="text-xs font-semibold text-gray-500">Course, Year & Section</span>
                    <span class="text-sm text-gray-800">{{ $enrollment?->course?->name ?? '-' }} {{ $enrollment?->yearLevel?->name ?? '-' }} {{ $enrollment?->section?->name ?? '-' }}</span>
                </div>
                <div class="bg-gray-100 rounded-xl p-4 shadow-sm flex flex-col justify-center h-20">
                    <span class="text-xs font-semibold text-gray-500">Religion</span>
                    <span class="text-sm text-gray-800">{{ $student->religion ?? '-' }}</span>
                </div>
            </div>

        </div>
    </div>

    {{-- Payment & Transaction Details PLACEHOLDEEERRR --}}
    <div class="bg-white rounded-2xl shadow-md border p-6 space-y-6">
        <div class="flex justify-between items-center">
            <h2 class="text-xl font-semibold text-gray-900">Payment & Transaction Details</h2>
            <span class="text-xs text-gray-400 italic">SY & Sem</span>
        </div>

        {{-- Overall Payment Status --}}
        <div class="flex flex-col md:flex-row md:justify-between md:items-center text-sm">
            <p><span class="font-semibold text-gray-800">Overall Payment Status:</span> 
                <span class="ml-1 text-yellow-600 font-semibold">Pending</span>
            </p>
            <p><span class="font-semibold text-gray-800">Last Updated:</span> <span class="ml-1 text-gray-600">—</span></p>
        </div>

        <hr class="border-gray-200">

        {{-- Organization Fees --}}
        <div>
            <h3 class="text-gray-800 font-semibold mb-3">Organization Fees</h3>
            <div class="space-y-3">
                {{-- Fee Card Example --}}
                <div class="flex justify-between items-center p-4 border rounded-xl shadow-sm hover:shadow-md transition bg-gray-50">
                    <div class="space-y-1">
                        <p class="font-medium text-gray-900">CSC Fee</p>
                        <p class="text-xs text-gray-500">University Student Council</p>
                    </div>
                    <div class="text-right space-y-1">
                        <p class="text-gray-800 font-semibold">₱ —</p>
                        <p class="text-yellow-600 font-medium text-sm">Unpaid</p>
                        <p class="text-gray-400 text-xs">—</p>
                    </div>
                </div>

                <div class="flex justify-between items-center p-4 border rounded-xl shadow-sm hover:shadow-md transition bg-gray-50">
                    <div class="space-y-1">
                        <p class="font-medium text-gray-900">MSA Fee</p>
                        <p class="text-xs text-gray-500">Muslim Students Association</p>
                    </div>
                    <div class="text-right space-y-1">
                        <p class="text-gray-800 font-semibold">₱ —</p>
                        <p class="text-yellow-600 font-medium text-sm">Unpaid</p>
                        <p class="text-gray-400 text-xs">—</p>
                    </div>
                </div>

                <div class="flex justify-between items-center p-4 border rounded-xl shadow-sm hover:shadow-md transition bg-gray-50">
                    <div class="space-y-1">
                        <p class="font-medium text-gray-900">Membership Fee</p>
                        <p class="text-xs text-gray-500">Venom Publication</p>
                    </div>
                    <div class="text-right space-y-1">
                        <p class="text-gray-800 font-semibold">₱ —</p>
                        <p class="text-gray-400 italic text-sm">Not Set</p>
                        <p class="text-gray-400 text-xs">—</p>
                    </div>
                </div>
            </div>
        </div>

        <hr class="border-gray-200">

        {{-- Transaction History --}}
        <div>
            <h3 class="text-gray-800 font-semibold mb-3">Transaction History</h3>
            <div class="space-y-3">
                {{-- Example Transaction Card --}}
                <div class="flex justify-between items-center p-4 border rounded-xl shadow-sm hover:shadow-md transition bg-gray-50">
                    <div class="space-y-1">
                        <p class="font-medium text-gray-900">Student Council Fee</p>
                        <p class="text-xs text-gray-500">Student Council Org</p>
                        <p class="text-gray-600 text-xs">Transaction ID: #123456</p>
                    </div>
                    <div class="text-right space-y-1">
                        <p class="text-gray-800 font-semibold">₱ 500</p>
                        <p class="text-green-600 font-medium text-sm">Paid</p>
                        <p class="text-gray-400 text-xs">Feb 3, 2026</p>
                    </div>
                </div>

                {{-- Empty placeholder --}}
                <div class="p-4 border rounded-xl shadow-sm text-center text-gray-400 italic">
                    No transactions recorded yet.
                </div>
            </div>
        </div>
    </div>

</div>
@endsection
