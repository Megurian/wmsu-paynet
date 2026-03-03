@extends('layouts.dashboard')

@section('title', 'Payment Collection Report')
@section('page-title', $motherOrg->name . ' Payment Collection Report')

@section('content')
<div class="mb-6 p-4 border rounded bg-gray-100">
    <form method="GET" action="{{ route('university_org.reports') }}" class="flex flex-wrap gap-4 items-end">
        <div>
            <label class="block text-sm font-medium mb-1">School Year</label>
            <select name="school_year_id" class="border-gray-300 rounded p-2">
                @foreach(\App\Models\SchoolYear::all() as $sy)
                <option value="{{ $sy->id }}" {{ (request('school_year_id', $schoolYearId) == $sy->id) ? 'selected' : '' }}>
                    {{ \Carbon\Carbon::parse($sy->sy_start)->year }} - {{ \Carbon\Carbon::parse($sy->sy_end)->year }}
                </option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">Semester</label>
            <select name="semester_id" class="border-gray-300 rounded p-2">
                @foreach(\App\Models\Semester::all() as $sem)
                <option value="{{ $sem->id }}" {{ $semesterId == $sem->id ? 'selected' : '' }}>
                    {{ $sem->name }}
                </option>
                @endforeach
            </select>
        </div>
        <div>
            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                Filter
            </button>
        </div>
    </form>
</div>

<h2 class="text-2xl font-bold mb-4">{{ $motherOrg->name }} Payment Collection Report</h2>
@foreach($orgReports as $orgReport)
<div class="mb-6 border p-4 rounded bg-gray-50">
    <h3 class="font-semibold">
        {{ $orgReport['organization']->name }}
        ({{ $orgReport['organization']->org_code }})
    </h3>

    <p class="text-sm text-gray-600">
        Total Students: {{ $orgReport['total_students'] }} |
        Paid: {{ $orgReport['paid_count'] }} |
        Pending: {{ $orgReport['pending_count'] }} |
        Completion: {{ $orgReport['paid_percentage'] }}%
    </p>

    <table class="w-full border mt-3">
        <thead class="bg-gray-200">
            <tr>
                <th>#</th>
                <th>Student</th>
                <th>Total Paid</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($orgReport['students'] as $index => $studentReport)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>
                    {{ $studentReport['student']->last_name }},
                    {{ $studentReport['student']->first_name }}
                </td>
                <td>₱ {{ number_format($studentReport['total_paid'], 2) }}</td>
                <td>
                    @if($studentReport['status'] === 'PAID')
                    <span class="text-green-600 font-semibold">PAID</span>
                    @else
                    <span class="text-red-600 font-semibold">PENDING</span>
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="4" class="text-center">No enrolled students found.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endforeach
@endsection
