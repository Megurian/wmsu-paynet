@extends('layouts.dashboard')

@section('title', 'Student History Info')
@section('page-title', 'Student History Info')

@section('content')

<div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6 mb-6">
    <h2 class="text-lg font-semibold text-gray-800 mb-4">Student Info</h2>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <p><span class="font-semibold">Student ID:</span> {{ $studentInfo->student_id }}</p>
            <p><span class="font-semibold">Name:</span> 
                {{ strtoupper($studentInfo->last_name) }}, 
                {{ strtoupper($studentInfo->first_name) }} 
                {{ strtoupper($studentInfo->middle_name) }} 
                {{ strtoupper($studentInfo->suffix) }}
            </p>
            <p><span class="font-semibold">Course:</span> {{ $student->first()->course?->name ?? '—' }}</p>
            <p><span class="font-semibold">Year & Section:</span> 
                {{ $student->first()->yearLevel?->name ?? '—' }} {{ $student->first()->section?->name ?? '—' }}
            </p>
        </div>
        <div>
            <p><span class="font-semibold">Adviser:</span> {{ $student->first()->adviser?->last_name ?? '—' }}</p>

            @php
                $first = $student->first();
                if($first->assessed_at) {
                    $status = 'Assessed';
                    $badgeColor = 'bg-green-100 text-green-700';
                } elseif($first->validated_at) {
                    $status = 'To be Assessed';
                    $badgeColor = 'bg-yellow-100 text-yellow-700';
                } elseif($first->advised_at) {
                    $status = 'Pending Payment';
                    $badgeColor = 'bg-blue-100 text-blue-700';
                } else {
                    $status = 'Not Enrolled';
                    $badgeColor = 'bg-gray-100 text-gray-500';
                }
            @endphp

            <p>
                <span class="font-semibold">Status:</span> 
                <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $badgeColor }}">
                    {{ $status }}
                </span>
            </p>

            <p><span class="font-semibold">Advised At:</span> {{ $first->advised_at?->format('F d, Y H:i') ?? '—' }}</p>
            <p><span class="font-semibold">Assessed At:</span> {{ $first->assessed_at?->format('F d, Y H:i') ?? 'To be Assessed' }}</p>
        </div>
    </div>
</div>

@endsection