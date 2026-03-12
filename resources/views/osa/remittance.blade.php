@extends('layouts.dashboard')

@section('title', 'OSA Remittance Monitoring')
@section('page-title', 'OSA Remittance Monitoring')

@section('content')

<div class="space-y-8">

    {{-- DASHBOARD CARDS --}}

    <div class="grid grid-cols-4 gap-6">

        <div class="bg-white shadow rounded-xl p-5">
            <p class="text-sm text-gray-500">Total Expected</p>
            <h2 class="text-2xl font-bold text-gray-800">
                ₱{{ number_format($totalExpected,2) }}
            </h2>
        </div>

        <div class="bg-white shadow rounded-xl p-5">
            <p class="text-sm text-gray-500">Total Remitted</p>
            <h2 class="text-2xl font-bold text-green-600">
                ₱{{ number_format($totalRemitted,2) }}
            </h2>
        </div>

        <div class="bg-white shadow rounded-xl p-5">
            <p class="text-sm text-gray-500">Remaining Balance</p>
            <h2 class="text-2xl font-bold text-red-600">
                ₱{{ number_format($remainingTotal,2) }}
            </h2>
        </div>

        <div class="bg-white shadow rounded-xl p-5">
            <p class="text-sm text-gray-500">Pending Organizations</p>
            <h2 class="text-2xl font-bold text-yellow-600">
                {{ $pendingOrgs }}
            </h2>
        </div>

    </div>


    {{-- EXPECTED REMITTANCE TABLE --}}

    <div class="bg-white shadow rounded-xl">

        <div class="p-4 border-b font-semibold">
            Expected Remittances
        </div>

        <div class="overflow-x-auto">

            <table class="min-w-full text-sm">

                <thead class="bg-gray-100">
                    <tr>
                        <th class="p-3 text-left">Organization</th>
                        <th class="p-3 text-left">Fee</th>
                        <th class="p-3">Collected</th>
                        <th class="p-3">Remitted</th>
                        <th class="p-3">Remaining</th>
                        <th class="p-3">Last Remittance</th>
                        <th class="p-3">Status</th>
                    </tr>
                </thead>

                <tbody>

                    @foreach($expectedRemittances as $row)

                    <tr class="border-b hover:bg-gray-50">

                        <td class="p-3 flex items-center gap-2">

                            @if($row['organization']->logo)
                            <img src="{{ asset('storage/'.$row['organization']->logo) }}" class="w-8 h-8 rounded-full">
                            @endif

                            {{ $row['organization']->name }}

                        </td>

                        <td class="p-3">
                            {{ $row['fee']->fee_name }}
                        </td>

                        <td class="text-center">
                            ₱{{ number_format($row['total_collected'],2) }}
                        </td>

                        <td class="text-center text-green-600">
                            ₱{{ number_format($row['total_remitted'],2) }}
                        </td>

                        <td class="text-center text-red-600">
                            ₱{{ number_format($row['remaining'],2) }}
                        </td>

                        <td class="text-center">
                            {{ $row['last_remittance'] ? $row['last_remittance']->format('M d Y') : '-' }}
                        </td>

                        <td class="text-center">

                            @if($row['status']=='completed')
                            <span class="px-2 py-1 text-xs bg-green-100 text-green-700 rounded">
                                Completed
                            </span>

                            @elseif($row['status']=='partial')
                            <span class="px-2 py-1 text-xs bg-yellow-100 text-yellow-700 rounded">
                                Partial
                            </span>

                            @else
                            <span class="px-2 py-1 text-xs bg-red-100 text-red-700 rounded">
                                Pending
                            </span>
                            @endif

                        </td>

                    </tr>

                    @endforeach

                </tbody>
            </table>

        </div>
    </div>


    {{-- REMITTANCE RECORDS --}}

    <div class="bg-white shadow rounded-xl">

        <div class="p-4 border-b font-semibold">
            Remittance Records
        </div>

        <div class="overflow-x-auto">

            <table class="min-w-full text-sm">

                <thead class="bg-gray-100">
                    <tr>
                        <th class="p-3">Organization</th>
                        <th class="p-3">Fee</th>
                        <th class="p-3">Amount</th>
                        <th class="p-3">Date</th>
                        <th class="p-3">Confirmed By</th>
                        <th class="p-3">Status</th>
                        <th class="p-3"></th>
                    </tr>
                </thead>

                <tbody>

                    @foreach($remittances as $r)

                    <tr class="border-b">

<td class="p-3">
    {{ $r->fromOrganization?->name ?? 'Unknown Organization' }}
</td>

<td class="p-3">
    {{ $r->fee?->fee_name ?? 'Deleted Fee' }}
</td>

<td class="p-3 text-center">
    ₱{{ number_format($r->amount,2) }}
</td>

<td class="p-3 text-center">
    {{ $r->created_at->format('M d Y') }}
</td>

<td class="p-3 text-center">
    {{ $r->confirmer?->name ?? '-' }}
</td>

                    @endforeach

                </tbody>

            </table>

        </div>

    </div>

</div>

@endsection
