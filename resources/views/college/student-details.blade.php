@extends('layouts.dashboard')

@section('title', 'Student Details')
@section('page-title', 'Student Details')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <div class="flex justify-between items-center">
        <div>
            <h2 class="text-2xl font-bold">
                {{ $student->last_name }}, {{ $student->first_name }}
                {{ $student->middle_name }}
                {{ $student->suffix }}
            </h2>
            <p class="text-sm text-gray-600">Student ID: {{ $student->student_id }}</p>
        </div>

        <a href="{{ route('college.students') }}"
           class="text-blue-600 hover:underline text-sm">
            ← Back to Directory
        </a>
    </div>

    {{-- Personal Information --}}
    <div class="bg-white rounded-xl shadow border p-5">
        <h3 class="font-semibold text-gray-800 mb-4">Personal Information</h3>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
            <div><span class="font-medium">Email:</span> {{ $student->email ?? '-' }}</div>
            <div><span class="font-medium">Contact:</span> {{ $student->contact ?? '-' }}</div>
            <div><span class="font-medium">Religion:</span> {{ $student->religion ?? '-' }}</div>
        </div>
    </div>

    {{-- School Information --}}
    <div class="bg-white rounded-xl shadow border p-5">
        <h3 class="font-semibold text-gray-800 mb-4">School Information</h3>

        @if($enrollment)
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                <div><span class="font-medium">Course:</span> {{ $enrollment->course?->name }}</div>
                <div><span class="font-medium">Year Level:</span> {{ $enrollment->yearLevel?->name }}</div>
                <div><span class="font-medium">Section:</span> {{ $enrollment->section?->name }}</div>
                <div><span class="font-medium">Status:</span>
                    <span class="font-semibold text-indigo-600">
                        {{ $enrollment->status }}
                    </span>
                </div>
            </div>
        @else
            <p class="text-gray-500 italic">No active enrollment found.</p>
        @endif
    </div>

</div>
@endsection
