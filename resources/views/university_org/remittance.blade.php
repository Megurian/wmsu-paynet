@extends('layouts.dashboard')

@section('title','Remittance')
@section('page-title','Remittance Management')

@section('content')


<div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-6 mb-8">
    <div class="bg-white p-5 rounded-xl shadow hover:shadow-md transition">
        <div class="text-sm text-gray-500">Total Collected by Offices</div>
        <div class="text-2xl font-bold text-indigo-600">₱ {{ number_format($totalCollected,2) }}</div>
    </div>

    <div class="bg-white p-5 rounded-xl shadow hover:shadow-md transition">
        <div class="text-sm text-gray-500">Total Expected Remittance</div>
        <div class="text-2xl font-bold text-blue-600">₱ {{ number_format($totalExpected,2) }}</div>
    </div>

    <div class="bg-white p-5 rounded-xl shadow hover:shadow-md transition">
        <div class="text-sm text-gray-500">Total Remitted</div>
        <div class="text-2xl font-bold text-green-600">₱ {{ number_format($totalRemitted,2) }}</div>
    </div>

    <div class="bg-white p-5 rounded-xl shadow hover:shadow-md transition">
        <div class="text-sm text-gray-500">Total Remaining Unremitted</div>
        <div class="text-2xl font-bold text-red-600">₱ {{ number_format($remaining,2) }}</div>
    </div>
</div>

<div class="bg-white rounded-xl shadow p-6 mb-10">
    <h3 class="text-lg font-semibold mb-5 border-b pb-2">Filter Remittance by Period</h3>

    <form method="GET" action="{{ route('university_org.remittance') }}" class="grid grid-cols-1 md:grid-cols-3 gap-5 items-end">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">School Year</label>
            <select id="filter_school_year_id" name="school_year_id" required
                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                onchange="updateSemesterOptions()">
                @foreach($schoolYears as $sy)
                    <option value="{{ $sy->id }}" @selected($selectedSchoolYear && $selectedSchoolYear->id === $sy->id)>
                        {{ $sy->sy_start->format('Y') }} - {{ $sy->sy_end->format('Y') }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Semester</label>
            <select id="filter_semester_id" name="semester_id" required data-selected="{{ $selectedSemester?->id }}"
                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                @if($selectedSchoolYear)
                    @foreach($selectedSchoolYear->semesters as $semester)
                        <option value="{{ $semester->id }}" @selected($selectedSemester && $selectedSemester->id === $semester->id)>
                            {{ $semester->name }}
                        </option>
                    @endforeach
                @endif
            </select>
        </div>

        <div>
            <button type="submit"
                class="w-full bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700 transition">
                Show Period
            </button>
        </div>
    </form>
</div>

<div class="bg-white rounded-xl shadow p-6 mb-10">
    <h3 class="text-lg font-semibold mb-5 border-b pb-2">Remit Funds</h3>

    <form method="POST" action="{{ route('university_org.remittance.confirm') }}" class="grid grid-cols-1 md:grid-cols-3 gap-5 items-end">
        @csrf
        <input type="hidden" name="school_year_id" id="school_year_id" value="{{ $selectedSchoolYear?->id }}">
        <input type="hidden" name="semester_id" id="semester_id" value="{{ $selectedSemester?->id }}">

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Select Office</label>
            <select name="from_organization_id" id="from_organization_id" required
                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                onchange="updateRemaining()">
                <option value="">-- Choose Office --</option>
                @foreach($remittanceData as $row)
                <option value="{{ $row['organization']->id }}" data-remaining="{{ $row['remaining'] }}" data-fee-id="{{ $row['defaultFeeId'] }}">
                    {{ $row['organization']->name }} (Remaining: ₱ {{ number_format($row['remaining'],2) }})
                </option>
                @endforeach
            </select>
            {{-- <p class="text-xs text-gray-500 mt-1" id="remaining-info">Select an office to see remaining balance</p> --}}
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Amount</label>
            <input type="number" step="0.01" name="amount" id="amount" required placeholder="Enter amount"
                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
            <input type="hidden" name="fee_id" id="fee_id" value="">
        </div>

        <div>
            <button type="submit"
                class="w-full bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700 transition">
                Confirm Remittance
            </button>
        </div>
    </form>
</div>

<script>
function updateRemaining() {
    const select = document.getElementById('from_organization_id');
    const amountInput = document.getElementById('amount');
    const info = document.getElementById('remaining-info');

    const selectedOption = select.options[select.selectedIndex];
    const remaining = parseFloat(selectedOption?.dataset?.remaining ?? 0);
    const feeId = selectedOption?.dataset?.feeId;

    amountInput.max = remaining;
    if (info) {
        info.textContent = `Maximum allowable amount: ₱ ${remaining.toFixed(2)}`;
    }

    const feeIdInput = document.getElementById('fee_id');
    if (feeIdInput) {
        feeIdInput.value = feeId ?? '';
    }
}

function updateSemesterOptions() {
    const yearSelect = document.getElementById('filter_school_year_id');
    const semesterSelect = document.getElementById('filter_semester_id');
    const selectedSemesterId = semesterSelect.dataset.selected;
    const semesters = window.remittanceSemesters?.[yearSelect.value] ?? [];

    semesterSelect.innerHTML = '';

    semesters.forEach(semester => {
        const option = document.createElement('option');
        option.value = semester.id;
        option.textContent = semester.name;
        if (selectedSemesterId && selectedSemesterId.toString() === semester.id.toString()) {
            option.selected = true;
        }
        semesterSelect.appendChild(option);
    });

    const hiddenYear = document.getElementById('school_year_id');
    const hiddenSemester = document.getElementById('semester_id');
    if (hiddenYear) {
        hiddenYear.value = yearSelect.value;
    }
    if (hiddenSemester) {
        hiddenSemester.value = semesterSelect.value;
    }
}

window.remittanceSemesters = @json($schoolYears->mapWithKeys(function ($sy) {
    return [$sy->id => $sy->semesters->map(function ($semester) {
        return ['id' => $semester->id, 'name' => $semester->name];
    })->toArray()];
})->toArray());

window.addEventListener('DOMContentLoaded', function () {
    updateSemesterOptions();
    updateRemaining();
});
</script>

{{-- Remittance by Office --}}
<div class="bg-white rounded-xl shadow p-6 mb-10">
    <h3 class="text-lg font-semibold mb-5 border-b pb-2">Remittance by Office</h3>
    <div class="overflow-x-auto">
        <table class="min-w-full text-sm table-auto border-collapse">
            <thead class="bg-gray-100">
                <tr>
                    <th class="p-3 text-left font-medium">Office</th>
                    <th class="p-3 font-medium">Fee</th>
                    <th class="p-3 font-medium text-center">Students Paid</th>
                    <th class="p-3 font-medium text-center">Collected</th>
                    <th class="p-3 font-medium text-center">Remitted</th>
                    <th class="p-3 font-medium text-center">Remaining</th>
                    <th class="p-3 font-medium text-center">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @foreach($remittanceData as $row)
                    @foreach($row['feeDetails'] as $feeRow)
                    <tr class="hover:bg-gray-50">
                        <td class="p-3 font-medium">{{ $row['organization']->name }}</td>
                        <td class="p-3">{{ $feeRow['fee']->fee_name }}</td>
                        <td class="p-3 text-center">{{ $feeRow['studentsPaid'] }}</td>
                        <td class="p-3 text-center">₱ {{ number_format($feeRow['totalCollected'],2) }}</td>

                        @if($loop->first)
                        <td class="p-3 text-center text-green-600 font-semibold" rowspan="{{ count($row['feeDetails']) }}">
                            ₱ {{ number_format($row['remitted'],2) }}
                        </td>
                        <td class="p-3 text-center text-red-500" rowspan="{{ count($row['feeDetails']) }}">
                            ₱ {{ number_format($row['remaining'],2) }}
                        </td>
                        <td class="p-3 text-center" rowspan="{{ count($row['feeDetails']) }}">
                            <span class="px-2 py-1 rounded text-xs font-semibold
                                @if($row['status']=='Completed') bg-green-100 text-green-700
                                @elseif($row['status']=='Partial') bg-yellow-100 text-yellow-700
                                @else bg-red-100 text-red-700
                                @endif">
                                {{ $row['status'] }}
                            </span>
                        </td>
                        @endif
                    </tr>
                    @endforeach
                @endforeach
            </tbody>
        </table>
    </div>
</div>

{{-- Remittance History --}}
<div class="bg-white rounded-xl shadow p-6">
    <h3 class="text-lg font-semibold mb-5 border-b pb-2">Remittance History</h3>
    <div class="overflow-x-auto max-h-[350px]">
        <table class="min-w-full text-sm table-auto border-collapse">
            <thead class="bg-gray-100 sticky top-0">
                <tr>
                    <th class="p-3 font-medium text-left">Office</th>
                    <th class="p-3 font-medium text-center">Amount Remitted</th>
                    <th class="p-3 font-medium text-center">Date</th>
                    <th class="p-3 font-medium text-left">Confirmed By</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @foreach($history as $item)
                <tr class="hover:bg-gray-50">
                    <td class="p-3">{{ $item->fromOrganization->name }}</td>
                    <td class="p-3 text-center font-semibold text-green-600">₱ {{ number_format($item->amount,2) }}</td>
                    <td class="p-3 text-center">{{ $item->created_at->format('M d Y') }}</td>
                    <td class="p-3">{{ $item->confirmer->name ?? 'System' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

@endsection