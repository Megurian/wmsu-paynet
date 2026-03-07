@extends('layouts.dashboard')

@section('title', 'Payment Collection Report')
@section('page-title', 'Payment Collection Report')

@section('content')
<div class="space-y-6">

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

    <div class="p-4 bg-gray-50 rounded shadow flex flex-wrap gap-4">
        <p class="text-sm text-gray-700"><span class="font-semibold">School Year:</span> {{ \Carbon\Carbon::parse($selectedSY->sy_start)->year }} - {{ \Carbon\Carbon::parse($selectedSY->sy_end)->year }}</p>
        <p class="text-sm text-gray-700"><span class="font-semibold">Semester:</span> {{ ucfirst($selectedSem->name) }}</p>
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
                <p class="text-sm text-gray-600">Total Child Organizations</p>
                <p class="text-2xl font-bold">{{ $totalChildOrgs }}</p>
            </div>
            <a href="{{ route('university_org.child_organizations', ['school_year_id' => $selectedSY->id, 'semester_id' => $selectedSem->id]) }}" class="text-sm text-blue-600 hover:underline mt-2">
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
                <p class="text-sm text-gray-600">Total Students Enrolled</p>
                <p class="text-2xl font-bold">{{ $totalStudentsEnrolled }}</p>
            </div>
        </div>

        <div class="p-4 bg-purple-50 rounded shadow flex flex-col justify-between">
            <div>
                <p class="text-sm text-gray-600">Total Payments Collected</p>
                <p class="text-2xl font-bold">PHP {{ number_format($totalPaymentsCollected, 2) }}</p>
            </div>
            <a href="{{ route('university_org.child_organizations', ['school_year_id' => $selectedSY->id, 'semester_id' => $selectedSem->id]) }}" class="text-sm text-purple-600 hover:underline mt-2">
                View All
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    {{-- Student Payment Status --}}
    <div class="p-3 bg-white rounded shadow text-sm">
        <h4 class="font-semibold mb-2 text-sm">Student Payment Status</h4>
        <canvas id="studentPaymentStatusChart" class="w-full h-15"></canvas> {{-- smaller height --}}
    </div>

    {{-- Recently Added Organizations --}}
    @if($recentOrgs->isNotEmpty())
    <div class="p-3 bg-white rounded shadow text-sm">
        <h4 class="font-semibold mb-2 text-sm">Recently Added Organizations</h4>
        <ul class="space-y-1 text-xs max-h-15 overflow-y-auto"> {{-- smaller height + scroll --}}
            @foreach($recentOrgs as $org)
            <li class="flex flex-col sm:flex-row sm:justify-between items-start sm:items-center p-2 border rounded">
                <div class="flex items-center space-x-2 sm:space-x-3 mb-1 sm:mb-0">
                    @if($org->logo)
                    <img src="{{ asset('storage/' . $org->logo) }}" alt="Logo" class="w-7 h-7 object-contain rounded border"> {{-- smaller --}}
                    @endif
                    <div>
                        <p class="font-medium text-xs">{{ $org->name }}</p>
                        <p class="text-gray-500 text-[10px]">Code: {{ $org->org_code }} | College: {{ $org->college?->name ?? 'N/A' }}</p>
                    </div>
                </div>
                <div class="text-gray-500 text-[10px]">
                    Students: {{ $org->total_students }} | Payments: PHP {{ number_format($org->total_payments_collected, 2) }}
                </div>
            </li>
            @endforeach
        </ul>
    </div>
    @endif
</div>

<div class="p-3 bg-white rounded shadow text-sm mt-4">
    <h4 class="font-semibold mb-2 text-sm">Daily Total Payments</h4>
    <canvas id="dailyPaymentsChart" class="w-full h-15"></canvas> 
</div>

    <div class="space-y-6">
        <h3 class="text-md font-semibold mb-2">Child Organizations</h3>
        @if($childOrgs->isEmpty())
        <p class="text-gray-500">No child organizations found.</p>
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

                                <div class="mt-2 pl-4 text-xs text-gray-600">
                                    <p class="font-semibold">Paid Students ({{ $fee->paid_students->count() }}):</p>
                                    <ul class="list-disc list-inside">
                                        @foreach($fee->paid_students as $student)
                                        <li>{{ $student->last_name }}, {{ $student->first_name }} ({{ $student->student_id }})</li>
                                        @endforeach
                                    </ul>

                                    <p class="font-semibold mt-1">Pending Students ({{ $fee->pending_students->count() }}):</p>
                                    <ul class="list-disc list-inside">
                                        @foreach($fee->pending_students as $student)
                                        <li>{{ $student->last_name }}, {{ $student->first_name }} ({{ $student->student_id }})</li>
                                        @endforeach
                                    </ul>
                                </div>
                            </li>
                            @endforeach
                        </ul>
                    @endif
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
    type: 'line',
    data: {
        labels: @json($dailyPaymentLabels),
        datasets: [{
            label: 'Total Payments (PHP)',
            data: @json($dailyPaymentData),
            borderColor: 'rgba(54, 162, 235, 1)',
            backgroundColor: 'rgba(54, 162, 235, 0.2)',
            fill: true,
            tension: 0.3
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { display: false }, tooltip: { mode: 'index', intersect: false } },
        scales: {
            x: { title: { display: true, text: 'Date' } },
            y: { title: { display: true, text: 'PHP' } }
        }
    }
});

const studentStatusCtx = document.getElementById('studentPaymentStatusChart').getContext('2d');
new Chart(studentStatusCtx, {
    type: 'pie',
    data: {
        labels: ['Paid', 'Pending'],
        datasets: [{
            data: [{{ $totalPaidStudents }}, {{ $totalPendingStudents }}],
            backgroundColor: ['#4ade80', '#f87171'],
        }]
    },
    options: { responsive: true }
});
</script>
@endsection