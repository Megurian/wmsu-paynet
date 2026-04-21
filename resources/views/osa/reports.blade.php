@extends('layouts.dashboard')

@section('title', 'OSA Reports')
@section('page-title', 'OSA Reports')

@section('content')
<div class="max-w-7xl mx-auto space-y-8">

    <!-- Page Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-800">OSA Reports</h1>
            <p class="text-sm text-gray-500 mt-1">
                Overview of colleges, mother organizations, local organizations, and inherited OSA fees.
            </p>
        </div>
    </div>

    <!-- Filters Section -->
    <div class="bg-white border border-gray-200 rounded-xl p-5 shadow-sm">
        <form method="GET" action="{{ route('osa.reports') }}" class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">

            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">School Year</label>
                <select name="school_year_id" class="w-full h-10 border border-gray-300 rounded-lg px-3 text-sm focus:ring-2 focus:ring-red-500 focus:border-red-500" onchange="this.form.submit()">
                    @foreach($schoolYears as $sy)
                    <option value="{{ $sy->id }}" {{ $sy->id == $selectedSYId ? 'selected' : '' }}>
                        {{ \Carbon\Carbon::parse($sy->sy_start)->year }} - {{ \Carbon\Carbon::parse($sy->sy_end)->year }}
                    </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Semester</label>
                <select name="semester_id" class="w-full h-10 border border-gray-300 rounded-lg px-3 text-sm focus:ring-2 focus:ring-red-500 focus:border-red-500" onchange="this.form.submit()">
                    @foreach($semesters as $sem)
                    <option value="{{ $sem->id }}" {{ $sem->id == $selectedSemId ? 'selected' : '' }}>
                        {{ ucfirst($sem->name) }}
                    </option>
                    @endforeach
                </select>
            </div>

            <div class="flex gap-2">
                <a href="{{ route('osa.reports') }}" class="flex-1 h-10 border border-gray-300 text-sm rounded-lg flex items-center justify-center text-gray-600 hover:bg-gray-100 transition">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">

        <div class="bg-white border border-gray-200 rounded-2xl p-5 shadow-sm hover:shadow-md transition cursor-pointer" title="Total number of colleges in the university">
            <p class="text-xs text-gray-500 uppercase tracking-wide">Total Colleges</p>
            <p class="text-2xl font-semibold text-gray-800 mt-2">{{ $colleges->count() }}</p>
        </div>

        <div class="bg-white border border-gray-200 rounded-2xl p-5 shadow-sm hover:shadow-md transition cursor-pointer" title="Total number of university-wide mother organizations">
            <p class="text-xs text-gray-500 uppercase tracking-wide">Mother Organizations</p>
            <p class="text-2xl font-semibold text-green-600 mt-2">{{ $motherOrgsCount ?? 0 }}</p>
        </div>

        <div class="bg-white border border-gray-200 rounded-2xl p-5 shadow-sm hover:shadow-md transition cursor-pointer" title="Total number of local college organizations">
            <p class="text-xs text-gray-500 uppercase tracking-wide">Local Orgs</p>
            <p class="text-2xl font-semibold text-yellow-600 mt-2">{{ $localOrgsCount ?? 0 }}</p>
        </div>

        <div class="bg-white border border-gray-200 rounded-2xl p-5 shadow-sm hover:shadow-md transition cursor-pointer" title="Total number of active fees">
            <p class="text-xs text-gray-500 uppercase tracking-wide">Total Active Fees</p>
            <p class="text-2xl font-semibold text-purple-600 mt-2">
                {{ $totalActiveFees ?? 0 }}
            </p>
        </div>
    </div>

    <!-- Colleges Table -->
    <div class="bg-white border border-gray-200 rounded-2xl shadow-sm overflow-hidden">
        <div class="px-5 py-3 border-b border-gray-100">
            <h2 class="text-sm font-semibold text-gray-700 uppercase tracking-wide">Colleges Overview</h2>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full table-fixed text-sm text-gray-700">
                <thead class="bg-gray-50 text-xs uppercase text-gray-500">
                    <tr>
                        <th class="px-6 py-3 text-left w-24">Logo</th>
                        <th class="px-6 py-3 text-left w-1/4">College Name</th>
                        <th class="px-6 py-3 text-left w-1/6">College Code</th>
                        <th class="px-6 py-3 text-left">Local Orgs</th>
                        <th class="px-6 py-3 text-left">Child Orgs</th>
                        <th class="px-6 py-3 text-left w-29">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($colleges as $college)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-6 py-4">
                            @if($college->logo)
                            <img src="{{ asset('storage/' . $college->logo) }}" class="w-10 h-10 object-contain rounded border">
                            @else
                            <span class="text-gray-400">N/A</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 font-medium">{{ $college->name }}</td>
                        <td class="px-6 py-4">{{ $college->college_code }}</td>
                        <td class="px-6 py-4">{{ $college->local_orgs_count }}</td>
                        <td class="px-6 py-4">{{ $college->child_orgs_count }}</td>
                        <td class="px-6 py-4">
                            <a href="{{ route('osa.reports.college.details', ['college' => $college->id, 'school_year_id' => $selectedSYId, 'semester_id' => $selectedSemId]) }}" class="px-3 py-1 bg-blue-600 text-white text-sm rounded hover:bg-blue-700 transition">
                                View Details
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-10 text-center text-gray-500">No colleges found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Mother Organizations Table -->
    <div class="bg-white border border-gray-200 rounded-2xl shadow-sm overflow-hidden">
        <div class="px-5 py-3 border-b border-gray-100">
            <h2 class="text-sm font-semibold text-gray-700 uppercase tracking-wide">University-wide Organizations Overview</h2>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full table-fixed text-sm text-gray-700">
                <thead class="bg-gray-50 text-xs uppercase text-gray-500">
                    <tr>
                        <th class="px-6 py-3 text-left w-24">Logo</th>
                        <th class="px-6 py-3 text-left w-1/4">Mother Org Name</th>
                        <th class="px-6 py-3 text-left w-1/6">Org Code</th>
                        <th class="px-6 py-3 text-left w-1/6">Child Orgs</th>
                        <th class="px-6 py-3 text-left">Total Payment Collected</th>
                        <th class="px-6 py-3 text-left w-29">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($motherOrgs as $org)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-6 py-4">
                            @if($org->logo)
                            <img src="{{ asset('storage/' . $org->logo) }}" class="w-10 h-10 object-contain rounded border">
                            @else
                            <span class="text-gray-400">N/A</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 font-medium">{{ $org->name }}</td>
                        <td class="px-6 py-4">{{ $org->org_code }}</td>
                        <td class="px-6 py-4">{{ $org->child_organizations_count ?? 0 }}</td>
                        <td class="px-6 py-4">₱ {{ number_format($org->totalPayments ?? 0, 2) }}</td>
                        <td class="px-6 py-4">
                            <a href="{{ route('osa.reports.organization.details', ['organization' => $org->id, 'school_year_id' => $selectedSYId, 'semester_id' => $selectedSemId]) }}" class="px-3 py-1 bg-blue-600 text-white text-sm rounded hover:bg-blue-700 transition">
                                View Details
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-10 text-center text-gray-500">No organizations found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Local Organizations Table -->
    <div class="bg-white border border-gray-200 rounded-2xl shadow-sm overflow-hidden">
        <div class="px-5 py-3 border-b border-gray-100">
            <h2 class="text-sm font-semibold text-gray-700 uppercase tracking-wide">College Organizations Overview</h2>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full table-fixed text-sm text-gray-700">
                <thead class="bg-gray-50 text-xs uppercase text-gray-500">
                    <tr>
                        <th class="px-6 py-3 text-left w-24">Logo</th>
                        <th class="px-6 py-3 text-left w-1/4">Organization</th>
                        <th class="px-6 py-3 text-left w-1/6">Org Code</th>
                        <th class="px-6 py-3 text-left w-1/6">College</th>
                        <th class="px-6 py-3 text-left">Total Payment Collected</th>
                        <th class="px-6 py-3 text-left w-29">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($localOrgs as $org)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-6 py-4">
                            @if($org->logo)
                            <img src="{{ asset('storage/' . $org->logo) }}" class="w-10 h-10 object-contain rounded border">
                            @else
                            <span class="text-gray-400">N/A</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 font-medium">{{ $org->name }}</td>
                        <td class="px-6 py-4">{{ $org->org_code }}</td>
                        <td class="px-6 py-4">{{ $org->college->college_code ?? 'N/A' }}</td>
                        <td class="px-6 py-4">₱ {{ number_format($org->totalPayments ?? 0, 2) }}</td>
                        <td class="px-6 py-4">
                            <a href="{{ route('osa.reports.organization.details', ['organization' => $org->id, 'school_year_id' => $selectedSYId, 'semester_id' => $selectedSemId]) }}" class="px-3 py-1 bg-blue-600 text-white text-sm rounded hover:bg-blue-700 transition">
                                View Details
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-10 text-center text-gray-500">No organizations found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Inherited OSA Fees -->
    <div class="bg-white border border-gray-200 rounded-2xl shadow-sm overflow-hidden">
        <div class="px-5 py-3 border-b border-gray-100">
            <h2 class="text-sm font-semibold text-gray-700 uppercase tracking-wide">Inherited OSA Fees</h2>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full text-sm text-gray-700">
                <thead class="bg-gray-50 text-xs uppercase text-gray-500">
                    <tr>
                        <th class="px-6 py-3 text-left">Fee Name</th>
                        <th class="px-6 py-3 text-left">Total Payment Collected</th>
                        <th class="px-6 py-3 text-left">Inherited By Organization(s)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($inheritedOsaFees as $fee)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-6 py-4 font-medium">{{ $fee->fee_name }}</td>
                        <td class="px-6 py-4">₱ {{ number_format($fee->totalPayments ?? 0, 2) }}</td>
                        <td class="px-6 py-4">{{ $fee->inheritedBy ?: 'N/A' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="3" class="px-6 py-10 text-center text-gray-500">No inherited OSA fees found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection