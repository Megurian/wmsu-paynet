@extends('layouts.dashboard')

@section('title', 'Payment Collection Report')
@section('page-title', 'Payment Collection Report')

@section('content')
<div class="space-y-6">

    <form method="GET" action="{{ route('university_org.reports') }}">
        <div class="p-4 bg-white rounded shadow flex flex-col sm:flex-row sm:items-end sm:space-x-6 space-y-4 sm:space-y-0">
            <div class="flex-1">
                <label class="block text-sm font-medium text-gray-700">School Year</label>
                <select name="school_year_id" class="mt-1 w-full border-gray-300 rounded shadow-sm">
                    @foreach($schoolYears as $sy)
                    <option value="{{ $sy->id }}" {{ $selectedSY && $selectedSY->id == $sy->id ? 'selected' : '' }}>
                        {{ \Carbon\Carbon::parse($sy->sy_start)->year }} - {{ \Carbon\Carbon::parse($sy->sy_end)->year }}
                    </option>
                    @endforeach
                </select>
            </div>

            <div class="flex-1">
                <label class="block text-sm font-medium text-gray-700">Semester</label>
                <select name="semester_id" class="mt-1 w-full border-gray-300 rounded shadow-sm">
                    @foreach($semesters as $sem)
                    <option value="{{ $sem->id }}" {{ $selectedSem && $selectedSem->id == $sem->id ? 'selected' : '' }}>
                        {{ ucfirst($sem->name) }}
                    </option>
                    @endforeach
                </select>
            </div>

            <div>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded shadow text-sm hover:bg-blue-700">
                    Filter
                </button>
            </div>
        </div>
    </form>

    <div class="p-4 bg-gray-50 rounded shadow flex flex-wrap gap-4">
        @if($selectedSY && $selectedSem)
            <p class="text-sm text-gray-700"><span class="font-semibold">School Year:</span> {{ \Carbon\Carbon::parse($selectedSY->sy_start)->year }} - {{ \Carbon\Carbon::parse($selectedSY->sy_end)->year }}</p>
            <p class="text-sm text-gray-700"><span class="font-semibold">Semester:</span> {{ ucfirst($selectedSem->name) }}</p>
        @else
            <p class="text-sm text-gray-700"><span class="font-semibold">School Year:</span> Not selected</p>
            <p class="text-sm text-gray-700"><span class="font-semibold">Semester:</span> Not selected</p>
        @endif
    </div>

    @if($motherOrg)
    {{-- <div class="p-4 bg-white rounded shadow">
        <h2 class="text-lg font-semibold mb-3">University Organization</h2>
        <div class="flex items-center space-x-4">
            @if($motherOrg->logo)
            <img src="{{ asset('storage/' . $motherOrg->logo) }}" alt="Logo" class="w-16 h-16 object-contain rounded border">
            @endif
            <div>
                <p class="font-medium text-gray-800">{{ $motherOrg->name }}</p>
                <p class="text-sm text-gray-500">{{ $motherOrg->org_code }}</p>
            </div>
        </div>
    </div> --}}

    {{-- Summary Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
        <div class="p-4 bg-blue-50 rounded shadow flex flex-col justify-between">
            <div>
                <p class="text-sm text-gray-600">Total Offices</p>
                <p class="text-2xl font-bold">{{ $totalChildOrgs }}</p>
            </div>
            <a href="{{ route('university_org.offices', ['school_year_id' => $selectedSY->id, 'semester_id' => $selectedSem->id]) }}" class="text-sm text-blue-600 hover:underline mt-2">
                View All
            </a>
        </div>

        <div class="p-4 bg-green-50 rounded shadow flex flex-col justify-between">
            <div>
                <p class="text-sm text-gray-600">Total Students Paid</p>
                <p class="text-2xl font-bold">{{ $totalStudentsPaid }}</p>
            </div>
        </div>

        <div class="p-4 bg-yellow-50 rounded shadow flex flex-col justify-between">
            <div>
                <p class="text-sm text-gray-600">Total Students Pending Payment</p>
                <p class="text-2xl font-bold">{{ $totalPendingStudents }}</p>
            </div>
        </div>

        <div class="p-4 bg-purple-50 rounded shadow flex flex-col justify-between">
            <div>
                <p class="text-sm text-gray-600">Total Payments Collected</p>
                <p class="text-2xl font-bold">PHP {{ number_format($totalPaymentsCollected, 2) }}</p>
            </div>
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    {{-- Student Payment Status --}}
    <div class="p-3 bg-white rounded shadow text-sm">
        <h4 class="font-semibold mb-2 text-sm">Student Payment Status</h4>
        <div class="h-64">
            <canvas id="studentPaymentStatusChart" class="w-full h-full"></canvas>
        </div>
    </div>

    {{-- Daily Total Payments --}}
    <div class="p-3 bg-white rounded shadow text-sm">
        <h4 class="font-semibold mb-2 text-sm">Daily Total Payments</h4>
        <div class="h-64">
            <canvas id="dailyPaymentsChart" class="w-full h-full"></canvas>
        </div>
    </div>
</div>

    <div class="space-y-6">
        <h3 class="text-md font-semibold mb-2">Offices</h3>
        @if($childOrgs->isEmpty())
        <p class="text-gray-500">No offices found.</p>
        @else
            @foreach($childOrgs as $org)
            <div class="p-4 bg-white rounded shadow space-y-3">
                <div class="flex flex-col sm:flex-row sm:justify-between items-start sm:items-center">
                    <div class="flex items-center space-x-4">
                        @if($org->logo)
                        <img src="{{ asset('storage/' . $org->logo) }}" alt="Logo" class="w-12 h-12 object-contain rounded border">
                        @endif
                        <div>
                            <p class="font-medium">{{ $org->name }}</p>
                            <p class="text-sm text-gray-500">Code: {{ $org->org_code }}</p>
                            <p class="text-sm text-gray-500">College: {{ $org->college?->name ?? 'N/A' }}</p>
                        </div>
                    </div>
                    <div class="text-sm text-gray-500 mt-2 sm:mt-0">
                        Admin: {{ $org->orgAdmin?->first_name ?? 'N/A' }} {{ $org->orgAdmin?->last_name ?? '' }}
                    </div>
                </div>

                {{-- Fees --}}
                <div class="mt-2">
                    <h4 class="text-sm font-semibold mb-2">Fees</h4>
                    @if($org->fees->isEmpty())
                    <p class="text-gray-500 text-sm">No fees available.</p>
                    @else
                        <ul class="space-y-2">
                            @foreach($org->fees as $fee)
                            <li class="p-2 border rounded bg-gray-50">
                                <div class="flex justify-between items-center">
                                    <div>
                                        <p class="font-medium">{{ $fee->fee_name }}</p>
                                        <p class="text-sm text-gray-500">{{ $fee->requirement_level }} | PHP {{ number_format($fee->amount, 2) }}</p>
                                    </div>
                                    <div class="text-sm text-gray-500">Status: {{ ucfirst($fee->status ?? 'pending') }}</div>
                                </div>

                                <div class="mt-3 grid grid-cols-1 sm:grid-cols-3 gap-3 text-xs text-gray-600">
                                    <div class="rounded bg-gray-50 border border-gray-200 p-2">
                                        <p class="font-semibold text-gray-700">Paid</p>
                                        <p class="text-xl font-bold text-gray-800">{{ $fee->paid_students->count() }}</p>
                                    </div>
                                    <div class="rounded bg-gray-50 border border-gray-200 p-2">
                                        <p class="font-semibold text-gray-700">Pending</p>
                                        <p class="text-xl font-bold text-gray-800">{{ $fee->pending_students->count() }}</p>
                                    </div>
                                    <div class="rounded bg-gray-50 border border-gray-200 p-2">
                                        <p class="font-semibold text-gray-700">Collected</p>
                                        <p class="text-xl font-bold text-gray-800">PHP {{ number_format($fee->paid_students->count() * $fee->amount, 2) }}</p>
                                    </div>
                                </div>
                            </li>
                            @endforeach
                        </ul>
                    @endif
                </div>

                <div class="mt-3 flex justify-end">
                    <a href="{{ route('university_org.child_org_fees', ['org_id' => $org->id, 'school_year_id' => $selectedSY->id, 'semester_id' => $selectedSem->id]) }}" class="px-3 py-2 bg-blue-600 text-white text-xs font-semibold rounded hover:bg-blue-700">
                        View Details
                    </a>
                </div>
            </div>
            @endforeach
        @endif
    </div>

    @else
    <p class="text-red-500">You do not belong to a university-level organization.</p>
    @endif

</div>

{{-- Chart Scripts --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const dailyPaymentsCtx = document.getElementById('dailyPaymentsChart').getContext('2d');
new Chart(dailyPaymentsCtx, {
    type: 'bar',
    data: {
        labels: @json($dailyPaymentLabels),
        datasets: [{
            label: 'Total Payments (PHP)',
            data: @json($dailyPaymentData),
            borderColor: 'rgba(37, 99, 235, 1)',
            backgroundColor: 'rgba(37, 99, 235, 0.18)',
            borderWidth: 1.5,
            borderRadius: 8,
            maxBarThickness: 34
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { display: false },
            tooltip: {
                mode: 'index',
                intersect: false,
                callbacks: {
                    label: function(context) {
                        return `PHP ${Number(context.raw || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
                    }
                }
            }
        },
        scales: {
            x: {
                grid: { display: false },
                title: { display: true, text: 'Date' }
            },
            y: {
                beginAtZero: true,
                grid: { color: 'rgba(148, 163, 184, 0.2)' },
                title: { display: true, text: 'PHP' },
                ticks: {
                    callback: function(value) {
                        return `₱${Number(value).toLocaleString()}`;
                    }
                }
            }
        }
    }
});

const studentStatusCtx = document.getElementById('studentPaymentStatusChart').getContext('2d');
new Chart(studentStatusCtx, {
    type: 'doughnut',
    data: {
        labels: @json($officeCollectionLabels),
        datasets: [{
            data: @json($officeCollectionData),
            backgroundColor: [
                '#22c55e', '#ef4444', '#3b82f6', '#f59e0b', '#a855f7', '#14b8a6',
                '#f97316', '#0ea5e9', '#10b981', '#f43f5e'
            ],
            borderColor: '#ffffff',
            borderWidth: 3,
            hoverOffset: 6
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        cutout: '62%',
        plugins: {
            legend: {
                position: 'bottom',
                labels: {
                    usePointStyle: true,
                    pointStyle: 'circle',
                    padding: 16
                }
            },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        const total = context.dataset.data.reduce((sum, value) => sum + value, 0);
                        const value = Number(context.raw || 0);
                        const pct = total ? ((value / total) * 100).toFixed(1) : '0.0';
                        return `${context.label}: PHP ${value.toLocaleString()} (${pct}%)`;
                    }
                }
            }
        }
    }
});
</script>
@endsection