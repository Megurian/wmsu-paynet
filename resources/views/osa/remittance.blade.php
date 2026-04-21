@extends('layouts.dashboard')

@section('title','OSA Remittance')
@section('page-title','OSA Remittance Management')

@section('content')

<div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-6 mb-8">

    <div class="bg-white p-5 rounded-xl shadow hover:shadow-md transition">
        <div class="text-sm text-gray-500">Total Collected</div>
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
        <div class="text-sm text-gray-500">Remaining</div>
        <div class="text-2xl font-bold text-red-600">₱ {{ number_format($remaining,2) }}</div>
    </div>

</div>

<div class="bg-white rounded-xl shadow p-6 mb-10">
    <h3 class="text-lg font-semibold mb-5 border-b pb-2">Filter Remittance by Period</h3>

    <form method="GET" action="{{ route('osa.remittance') }}" class="grid grid-cols-1 md:grid-cols-3 gap-5 items-end">
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


{{-- Confirm Remittance --}}
<div class="bg-white rounded-xl shadow p-6 mb-10">

    <h3 class="text-lg font-semibold mb-5 border-b pb-2">
        Confirm Remittance
    </h3>

<form method="POST" action="{{ route('osa.remittance.confirm') }}" class="grid grid-cols-1 md:grid-cols-3 gap-5 items-end">

        @csrf
        <input type="hidden" name="school_year_id" id="school_year_id" value="{{ $selectedSchoolYear?->id }}">
        <input type="hidden" name="semester_id" id="semester_id" value="{{ $selectedSemester?->id }}">

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
                Select Organization
            </label>

        <select id="from_organization_id" name="organization_id" required  class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500" onchange="updateRemaining()">
            <option value="">-- Choose Organization --</option>
            @foreach($remittanceData as $row)
                <option value="{{ $row['organization']->id }}" data-remaining="{{ $row['remaining'] }}">
                    {{ $row['organization']->name }} (Remaining: ₱{{ number_format($row['remaining'],2) }})
                </option>
            @endforeach
        </select>

        </div>

        <div>

            <label class="block text-sm font-medium text-gray-700 mb-1">
                Amount
            </label>

            <input id="amount" type="number" step="0.01" name="amount" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">

        </div>

        <div>

            <button type="submit" class="w-full bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700 transition">

                Confirm Remittance

            </button>

        </div>

    </form>

</div>


<script>
    function updateRemaining() {
        const select = document.getElementById('from_organization_id');
        const amountInput = document.getElementById('amount');
        const selectedOption = select.options[select.selectedIndex];
        const remaining = parseFloat(selectedOption.dataset.remaining ?? 0);

        amountInput.max = remaining;
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


{{-- Remittance by Organization --}}
<div class="bg-white rounded-xl shadow p-6 mb-10">

    <h3 class="text-lg font-semibold mb-5 border-b pb-2">
        Remittance by Organization
    </h3>

    <div class="overflow-x-auto">

        <table class="min-w-full text-sm table-auto border-collapse">

            <thead class="bg-gray-100">

                <tr>

                    <th class="p-3 text-left">Organization</th>
                    <th class="p-3">Fee</th>
                    <th class="p-3 text-center">Students Paid</th>
                    <th class="p-3 text-center">Collected</th>
                    <th class="p-3 text-center">Remittance Amount</th>
                    <th class="p-3 text-center">Remitted</th>
                    <th class="p-3 text-center">Remaining</th>
                    <th class="p-3 text-center">Status</th>

                </tr>

            </thead>

            <tbody class="divide-y">

                @foreach($remittanceData as $row)

                @foreach($row['feeDetails'] as $feeRow)

                <tr class="hover:bg-gray-50">

                    <td class="p-3 font-medium">
                        {{ $row['organization']->name }}
                    </td>

                    <td class="p-3">
                        {{ $feeRow['fee']->fee_name }}
                    </td>

                    <td class="p-3 text-center">
                        {{ $feeRow['studentsPaid'] }}
                    </td>

                    <td class="p-3 text-center">
                        ₱ {{ number_format($feeRow['totalCollected'],2) }}
                    </td>

                    <td class="p-3 text-center text-blue-600 font-semibold">
                        ₱ {{ number_format($feeRow['remittanceAmount'],2) }}
                    </td>

                    @if($loop->first)

                    <td class="p-3 text-center text-green-600 font-semibold" rowspan="{{ count($row['feeDetails']) }}">

                        ₱ {{ number_format($row['remitted'],2) }}

                    </td>

                    <td class="p-3 text-center text-red-500" rowspan="{{ count($row['feeDetails']) }}">

                        ₱ {{ number_format($row['remaining'],2) }}

                    </td>

                    <td class="p-3 text-center" rowspan="{{ count($row['feeDetails']) }}">

                        <span class="px-2 py-1 rounded text-xs font-semibold

@if($row['status']=='Completed')
bg-green-100 text-green-700
@elseif($row['status']=='Partial')
bg-yellow-100 text-yellow-700
@else
bg-red-100 text-red-700
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

    <h3 class="text-lg font-semibold mb-5 border-b pb-2">
        Remittance History
    </h3>

    <div class="overflow-x-auto max-h-[350px]">

        <table class="min-w-full text-sm table-auto border-collapse">

            <thead class="bg-gray-100 sticky top-0">

                <tr>

                    <th class="p-3 text-left">Organization</th>
                    <th class="p-3 text-center">Amount</th>
                    <th class="p-3 text-center">Date</th>
                    <th class="p-3 text-left">Confirmed By</th>

                </tr>

            </thead>

            <tbody class="divide-y">

                @foreach($history as $item)

                <tr class="hover:bg-gray-50">

                    <td class="p-3">
                        {{ $item->fromOrganization->name }}
                    </td>

                    <td class="p-3 text-center text-green-600 font-semibold">
                        ₱ {{ number_format($item->amount,2) }}
                    </td>

                    <td class="p-3 text-center">
                        {{ $item->created_at->format('M d Y') }}
                    </td>

                    <td class="p-3">
                        {{ $item->confirmer->name ?? 'System' }}
                    </td>

                </tr>

                @endforeach

            </tbody>

        </table>

    </div>

</div>

@endsection
