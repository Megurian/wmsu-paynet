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
            <p><span class="font-semibold">Course:</span> {{ $studentEnrollments->first()->course?->name ?? '—' }}</p>
            <p><span class="font-semibold">Year & Section:</span> 
                {{$studentEnrollments->first()->yearLevel?->name ?? '—' }} {{ $studentEnrollments->first()->section?->name ?? '—' }}
            </p>
        </div>
        <div>
            <p><span class="font-semibold">Adviser:</span> {{ $studentEnrollments->first()->adviser?->last_name ?? '—' }}</p>

            @php
                $first = $studentEnrollments->first();
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

<div class="bg-white border border-gray-200 rounded-xl shadow-sm p-4 mt-6">
    <h3 class="text-lg font-semibold text-gray-800 mb-4">Payment History</h3>

    @if($fees->isEmpty())
        <p class="text-gray-500 text-sm">No fees found for this student.</p>
    @else
        <table class="min-w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr class="text-left text-[11px] font-semibold uppercase tracking-wide text-gray-600">
                    <th class="px-4 py-3">#</th>
                    <th class="px-5 py-3">Fee Name</th>
                    <th class="px-5 py-3">Organization</th>
                    <th class="px-5 py-3">Amount</th>
                    <th class="px-5 py-3">Paid Amount</th>
                    <th class="px-5 py-3">Status</th>
                    <th class="px-5 py-3">School Year / Semester</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 text-gray-700">
                @foreach($fees as $fee)
                    @php
                        $paid = $payments->sum(function($p) use ($fee) {
                            return $p->fees->contains($fee->id) ? $p->fees->find($fee->id)->pivot->amount_paid : 0;
                        });

                        $status = $paid >= $fee->amount ? 'Paid' : 'Unpaid';
                        $badgeColor = $status === 'Paid' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700';
                    @endphp
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-4 py-3 text-gray-500">{{ $loop->iteration }}</td>
                        <td class="px-5 py-3 font-medium">{{ $fee->fee_name }}</td>
                        <td class="px-5 py-3">{{ $fee->organization?->name ?? '—' }}</td>
                        <td class="px-5 py-3">{{ number_format($fee->amount, 2) }}</td>
                        <td class="px-5 py-3">{{ number_format($paid, 2) }}</td>
                        <td class="px-5 py-3">
                            <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $badgeColor }}">
                                {{ $status }}
                            </span>
                        </td>
                        <td class="px-5 py-3">
                            {{ $fee->schoolYear?->sy_start ? \Carbon\Carbon::parse($fee->schoolYear->sy_start)->year : '—' }}
                            –
                            {{ $fee->schoolYear?->sy_end ? \Carbon\Carbon::parse($fee->schoolYear->sy_end)->year : '—' }}
                            / {{ ucfirst($fee->semester?->name ?? '—') }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>

@endsection