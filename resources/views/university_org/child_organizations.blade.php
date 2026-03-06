@extends('layouts.dashboard')

@section('title', 'Child Organizations')
@section('page-title', 'All Child Organizations')

@section('content')

<div class="space-y-4">

    <div class="flex items-center justify-between">
        <h2 class="text-lg font-semibold">Child Organizations</h2>

        <a href="{{ route('university_org.reports', [
        'school_year_id' => $selectedSY->id,
        'semester_id' => $selectedSem->id
    ]) }}" class="text-sm text-blue-600 hover:underline">
            ← Back to Report
        </a>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-5 gap-4">

        <div class="p-4 bg-blue-100 rounded shadow">
            <p class="text-sm text-gray-600">Total Child Organizations</p>
            <p class="text-2xl font-bold">{{ $totalChildOrgs }}</p>
        </div>

        <div class="p-4 bg-green-100 rounded shadow">
            <p class="text-sm text-gray-600">Active Fees</p>
            <p class="text-2xl font-bold">{{ $totalActiveFees }}</p>
        </div>

        <div class="p-4 bg-yellow-100 rounded shadow">
            <p class="text-sm text-gray-600">Students Covered</p>
            <p class="text-2xl font-bold">{{ number_format($totalStudentsCovered) }}</p>
        </div>

        <div class="p-4 bg-purple-100 rounded shadow">
            <p class="text-sm text-gray-600">Total Payments Collected</p>
            <p class="text-2xl font-bold">
                PHP {{ number_format($totalPaymentsCollected, 2) }}
            </p>
        </div>

        <div class="p-4 bg-indigo-100 rounded shadow">
            <p class="text-sm text-gray-600">Payment Completion</p>
            <p class="text-2xl font-bold">
                {{ $paymentCompletionRate }}%
            </p>
        </div>

    </div>

    @if($childOrgs->isEmpty())
    <p class="text-gray-500">No child organizations found.</p>
    @else

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">

        @foreach($childOrgs as $org)

        <div class="p-4 border rounded bg-white shadow-sm flex items-center space-x-3">

            @if($org->logo)
            <img src="{{ asset('storage/'.$org->logo) }}" class="w-12 h-12 object-contain border rounded">
            @endif

            <div class="text-sm flex-1">

                <p class="font-semibold">{{ $org->name }}</p>

                <p class="text-gray-500 text-xs">
                    Code: {{ $org->org_code }}
                </p>

                <p class="text-gray-500 text-xs">
                    College: {{ $org->college?->name ?? 'N/A' }}
                </p>

                <p class="text-gray-500 text-xs mb-2">
                    Admin: {{ $org->orgAdmin?->first_name ?? 'N/A' }}
                    {{ $org->orgAdmin?->last_name ?? '' }}
                </p>

                <div class="grid grid-cols-3 gap-2 text-xs mt-2">

                    <div class="bg-blue-50 p-2 rounded text-center">
                        <p class="text-gray-500">Students</p>
                        <p class="font-semibold">{{ $org->total_students }}</p>
                    </div>

                    <div class="bg-green-50 p-2 rounded text-center">
                        <p class="text-gray-500">Collected</p>
                        <p class="font-semibold">
                            ₱{{ number_format($org->total_payments, 2) }}
                        </p>
                    </div>

                    <div class="bg-red-50 p-2 rounded text-center">
                        <p class="text-gray-500">Pending</p>
                        <p class="font-semibold">{{ $org->pending_students }}</p>
                    </div>

                    <div class="flex justify-between mt-3">
    <a href="{{ route('university_org.child_org_fees', [
        'org_id' => $org->id,
        'school_year_id' => $selectedSY->id,
        'semester_id' => $selectedSem->id
    ]) }}"
       class="px-3 py-1 bg-blue-600 text-white text-xs rounded hover:bg-blue-700">
        View Fees
    </a>
</div>

                </div>

            </div>

        </div>

        @endforeach

    </div>

    @endif

</div>

@endsection
