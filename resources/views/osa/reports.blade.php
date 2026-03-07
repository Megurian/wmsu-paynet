@extends('layouts.dashboard')

@section('title', 'OSA Reports')
@section('page-title', 'OSA Reports')

@section('content')
<div class="space-y-6">

    <div class="p-4 bg-white rounded shadow">
        <h3 class="text-lg font-semibold mb-4">Colleges Overview</h3>

        <div class="mb-4 flex flex-wrap items-center space-x-4">
            <form method="GET" class="flex space-x-2 items-end">
                <div>
                    <label class="block text-sm font-medium text-gray-700">School Year</label>
                    <select name="school_year_id" class="border rounded p-1 text-sm" onchange="this.form.submit()">
                        @foreach($schoolYears as $sy)
                        <option value="{{ $sy->id }}" {{ $sy->id == $selectedSYId ? 'selected' : '' }}>
                            {{ $sy->sy_start }} - {{ $sy->sy_end }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Semester</label>
                    <select name="semester_id" class="border rounded p-1 text-sm" onchange="this.form.submit()">
                        @foreach($semesters as $sem)
                        <option value="{{ $sem->id }}" {{ $sem->id == $selectedSemId ? 'selected' : '' }}>
                            {{ ucfirst($sem->name) }}
                        </option>
                        @endforeach
                    </select>
                </div>
            </form>
        </div>

        <p class="text-gray-600 text-sm mb-2">
            Showing results for:
            <strong>{{ $schoolYears->firstWhere('id', $selectedSYId)->sy_start ?? 'N/A' }} - {{ $schoolYears->firstWhere('id', $selectedSYId)->sy_end ?? 'N/A' }}</strong>,
            <strong>{{ $semesters->firstWhere('id', $selectedSemId)->name ?? 'N/A' }}</strong> semester
        </p>

        @if($colleges->isEmpty())
        <p class="text-gray-500">No colleges found.</p>
        @else
        <div class="overflow-x-auto">
            <table class="min-w-full border border-gray-200 text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="border-b p-2 text-left">Logo</th>
                        <th class="border-b p-2 text-left">College Name</th>
                        <th class="border-b p-2 text-left">College Code</th>
                        <th class="border-b p-2 text-left">Number of Admins</th>
                        <th class="border-b p-2 text-left">Local Orgs</th>
                        <th class="border-b p-2 text-left">Child Orgs</th>
                        <th class="border-b p-2 text-left">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($colleges as $college)
                    <tr class="hover:bg-gray-50">
                        <td class="p-2">
                            @if($college->logo)
                            <img src="{{ asset('storage/' . $college->logo) }}" class="w-10 h-10 object-contain rounded border">
                            @else
                            <span class="text-gray-400">N/A</span>
                            @endif
                        </td>
                        <td class="p-2 font-medium">{{ $college->name }}</td>
                        <td class="p-2">{{ $college->college_code }}</td>
                        <td class="p-2">{{ $college->local_orgs_count }}</td>
                        <td class="p-2">{{ $college->child_orgs_count }}</td>
                        <td class="p-2">
                            <a href="" class="text-blue-600 hover:underline text-xs">View Details</a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>

    <div class="p-4 bg-white rounded shadow mt-8">
        <h3 class="text-lg font-semibold mb-4">University-wide Mother Organizations</h3>

        @if($motherOrgs->isEmpty())
        <p class="text-gray-500">No mother organizations found.</p>
        @else
        <div class="overflow-x-auto">
            <table class="min-w-full border border-gray-200 text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="border-b p-2 text-left">Logo</th>
                        <th class="border-b p-2 text-left">Mother Org Name</th>
                        <th class="border-b p-2 text-left">Org Code</th>
                        <th class="border-b p-2 text-left">Child Organizations</th>
                        <th class="border-b p-2 text-left">Total Payment Collected</th>
                        <th class="border-b p-2 text-left">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($motherOrgs as $org)
                    <tr class="hover:bg-gray-50">
                        <td class="p-2">
                            @if($org->logo)
                            <img src="{{ asset('storage/' . $org->logo) }}" class="w-10 h-10 object-contain rounded border">
                            @else
                            <span class="text-gray-400">N/A</span>
                            @endif
                        </td>
                        <td class="p-2 font-medium">{{ $org->name }}</td>
                        <td class="p-2">{{ $org->org_code }}</td>
                        <td class="p-2">{{ $org->child_organizations_count ?? 0 }}</td>
                        <td class="p-2">₱ {{ number_format($org->totalPayments ?? 0, 2) }}</td>
                        {{-- <td class="p-2">
                            <a href="{{ route('osa.organization.details', $org->id) }}" class="text-blue-600 hover:underline text-xs">View Details</a>
                        </td> --}}
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>

    <div class="p-4 bg-white rounded shadow mt-8">
        <h3 class="text-lg font-semibold mb-4">Local College Organizations</h3>

        @if($localOrgs->isEmpty())
        <p class="text-gray-500">No local organizations found.</p>
        @else
        <div class="overflow-x-auto">
            <table class="min-w-full border border-gray-200 text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="border-b p-2 text-left">Logo</th>
                        <th class="border-b p-2 text-left">Organization</th>
                        <th class="border-b p-2 text-left">Org Code</th>
                        <th class="border-b p-2 text-left">College</th>
                        <th class="border-b p-2 text-left">Total Payment Collected</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($localOrgs as $org)
                    <tr class="hover:bg-gray-50">
                        <td class="p-2">
                            @if($org->logo)
                            <img src="{{ asset('storage/' . $org->logo) }}" class="w-10 h-10 object-contain rounded border">
                            @else
                            <span class="text-gray-400">N/A</span>
                            @endif
                        </td>

                        <td class="p-2 font-medium">{{ $org->name }}</td>
                        <td class="p-2">{{ $org->org_code }}</td>
                        <td class="p-2">{{ $org->college->name ?? 'N/A' }}</td>
                        <td class="p-2">₱ {{ number_format($org->totalPayments ?? 0, 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>
</div>
@endsection
