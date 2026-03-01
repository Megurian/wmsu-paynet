@extends('layouts.dashboard')

@section('title', 'Student History Info')
@section('page-title', 'Student History Info')

@section('content')

<div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6 mb-6">
    <h2 class="text-lg font-semibold text-gray-800 mb-4">Student Info</h2>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <p><span class="font-semibold">Student ID:</span> {{ $studentInfo->student_id }}</p>
            <p><span class="font-semibold">Name:</span> {{ strtoupper($studentInfo->last_name) }}, {{ strtoupper($studentInfo->first_name) }} {{ strtoupper($studentInfo->middle_name) }} {{ strtoupper($studentInfo->suffix) }}</p>
            <p><span class="font-semibold">Course:</span> {{ $student->first()->course?->name ?? '—' }}</p>
            <p><span class="font-semibold">Year & Section:</span> {{ $student->first()->yearLevel?->name ?? '—' }} {{ $student->first()->section?->name ?? '—' }}</p>
        </div>
        <div>
            <p><span class="font-semibold">Adviser:</span> {{ $student->first()->adviser?->name ?? '—' }}</p>
            <p><span class="font-semibold">Assessor:</span> {{ $student->first()->assessor?->name ?? '—' }}</p>
        </div>
    </div>
</div>



@endsection