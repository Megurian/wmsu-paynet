@extends('layouts.dashboard')

@section('title', 'College Organization Details')
@section('page-title', $college->name . ' Organizations')

@section('content')
<div class="space-y-6">

    <!-- Back Button -->
    <div>
        <a href="{{ route('osa.reports', [
            'school_year_id' => $selectedSYId,
            'semester_id' => $selectedSemId
        ]) }}" class="text-sm text-gray-600 hover:text-gray-900">
            ← Back to Reports
        </a>
    </div>

    <!-- Local College Organizations -->
    <div class="p-4 bg-white rounded shadow">
        <h3 class="text-lg font-semibold mb-4">Local College Organizations</h3>

        @if($localOrgs->isEmpty())
        <p class="text-gray-500">No local organizations found.</p>
        @else
        <div class="overflow-x-auto">
            <table class="min-w-full border text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="p-2 border-b text-left">Logo</th>
                        <th class="p-2 border-b text-left">Organization</th>
                        <th class="p-2 border-b text-left">Org Code</th>
                        <th class="p-2 border-b text-left">Total Payment Collected</th>
                        <th class="p-2 border-b text-left">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($localOrgs as $org)
                    <tr class="hover:bg-gray-50">
                        <td class="p-2">
                            @if($org->logo)
                            <img src="{{ asset('storage/'.$org->logo) }}" class="w-10 h-10 border rounded">
                            @else
                            <span class="text-gray-400">N/A</span>
                            @endif
                        </td>
                        <td class="p-2 font-medium">{{ $org->name }}</td>
                        <td class="p-2">{{ $org->org_code }}</td>
                        <td class="p-2">₱ {{ number_format($org->totalPayments ?? 0, 2) }}</td>
                        <td class="p-2">
                            <a href="{{ route('osa.reports.organization.details', [
        'organization' => $org->id,
        'school_year_id' => $selectedSYId,
        'semester_id' => $selectedSemId
    ]) }}" class="px-3 py-1 bg-blue-600 text-white text-sm rounded hover:bg-blue-700">
                                View Details
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>

    <!-- Child Organizations -->
    <div class="p-4 bg-white rounded shadow">
        <h3 class="text-lg font-semibold mb-4">Child Organizations</h3>

        @if($childOrgs->isEmpty())
        <p class="text-gray-500">No child organizations found.</p>
        @else
        <div class="overflow-x-auto">
            <table class="min-w-full border text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="p-2 border-b text-left">Logo</th>
                        <th class="p-2 border-b text-left">Organization</th>
                        <th class="p-2 border-b text-left">Org Code</th>
                        <th class="p-2 border-b text-left">Total Payment Collected</th>
                        <th class="p-2 border-b text-left">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($childOrgs as $org)
                    <tr class="hover:bg-gray-50">
                        <td class="p-2">
                            @if($org->logo)
                            <img src="{{ asset('storage/'.$org->logo) }}" class="w-10 h-10 border rounded">
                            @else
                            <span class="text-gray-400">N/A</span>
                            @endif
                        </td>
                        <td class="p-2 font-medium">{{ $org->name }}</td>
                        <td class="p-2">{{ $org->org_code }}</td>
                        <td class="p-2">₱ {{ number_format($org->totalPayments ?? 0, 2) }}</td>
                        <td class="p-2">
                            <a href="{{ route('osa.reports.organization.details', [
    'organization' => $org->id,
    'school_year_id' => $selectedSYId,
    'semester_id' => $selectedSemId
]) }}" class="px-3 py-1 bg-blue-600 text-white text-sm rounded hover:bg-blue-700">
                                View Details
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>

</div>
@endsection
