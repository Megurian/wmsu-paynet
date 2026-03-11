{{-- @extends('layouts.dashboard')

@section('title', 'OSA Remittance Monitoring')
@section('page-title', 'OSA Remittance Monitoring')

@section('content')

<div class="grid grid-cols-4 gap-6 mb-8">

    <div class="bg-white p-4 rounded-xl shadow">
        <h4 class="text-gray-500 text-sm">Expected Remittance</h4>
        <p class="text-2xl font-bold text-blue-600">
            ₱ {{ number_format($totalExpected,2) }}
        </p>
    </div>

    <div class="bg-white p-4 rounded-xl shadow">
        <h4 class="text-gray-500 text-sm">Total Remitted</h4>
        <p class="text-2xl font-bold text-green-600">
            ₱ {{ number_format($totalRemitted,2) }}
        </p>
    </div>

    <div class="bg-white p-4 rounded-xl shadow">
        <h4 class="text-gray-500 text-sm">Remaining</h4>
        <p class="text-2xl font-bold text-red-600">
            ₱ {{ number_format($remainingTotal,2) }}
        </p>
    </div>

    <div class="bg-white p-4 rounded-xl shadow">
        <h4 class="text-gray-500 text-sm">Pending Organizations</h4>
        <p class="text-2xl font-bold text-orange-500">
            {{ $pendingOrgs }}
        </p>
    </div>

</div>



<div class="bg-white rounded-xl shadow p-6 mb-8">

    <h3 class="text-lg font-semibold mb-4">
        Expected Remittance
    </h3>

    <div class="overflow-y-auto max-h-[400px]">

        <table class="w-full text-sm">

            <thead class="bg-gray-100">
                <tr>

                    <th class="p-3 text-left">Mother Organization</th>
                    <th class="p-3 text-left">Fee</th>
                    <th class="p-3 text-right">Collected</th>
                    <th class="p-3 text-right">Remitted</th>
                    <th class="p-3 text-right">Remaining</th>
                    <th class="p-3 text-center">Last Remittance</th>
                    <th class="p-3 text-center">Status</th>

                </tr>
            </thead>

            <tbody class="divide-y">

                @foreach($expectedData as $row)

                <tr>

                    <td class="p-3 flex items-center gap-2">

                        @if($row['organization']->logo)
                        <img src="{{ asset('storage/'.$row['organization']->logo) }}" class="w-6 h-6 rounded-full">
                        @endif

                        {{ $row['organization']->name }}

                    </td>

                    <td class="p-3">
                      {{ optional($record->fee)->fee_name ?? 'N/A' }}
                    </td>

                    <td class="p-3 text-right">
                        ₱ {{ number_format($row['collected'],2) }}
                    </td>

                    <td class="p-3 text-right text-green-600">
                        ₱ {{ number_format($row['remitted'],2) }}
                    </td>

                    <td class="p-3 text-right text-red-500">
                        ₱ {{ number_format($row['remaining'],2) }}
                    </td>

                    <td class="p-3 text-center">
                        {{ $row['last_remittance'] ? $row['last_remittance']->format('M d Y') : '-' }}
                    </td>

                    <td class="p-3 text-center">

                        @if($row['status']=='completed')
                        <span class="px-2 py-1 bg-green-100 text-green-700 rounded text-xs">
                            Completed
                        </span>

                        @elseif($row['status']=='partial')
                        <span class="px-2 py-1 bg-yellow-100 text-yellow-700 rounded text-xs">
                            Partial
                        </span>

                        @else
                        <span class="px-2 py-1 bg-red-100 text-red-700 rounded text-xs">
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



<div class="bg-white rounded-xl shadow p-6">

    <h3 class="text-lg font-semibold mb-4">
        Remittance Records
    </h3>

    <div class="overflow-y-auto max-h-[350px]">

        <table class="w-full text-sm">

            <thead class="bg-gray-100">
                <tr>

                    <th class="p-3">Organization</th>
                    <th class="p-3">Fee</th>
                    <th class="p-3 text-right">Amount</th>
                    <th class="p-3 text-center">Date</th>
                    <th class="p-3">Confirmed By</th>

                </tr>
            </thead>

            <tbody class="divide-y">

                @foreach($records as $record)

                <tr>

                    <td class="p-3">
                        {{ optional($record->fromOrganization)->name ?? 'Unknown Org' }}
                    </td>

                    <td class="p-3">
                        {{ $record->fee->fee_name }}
                    </td>

                    <td class="p-3 text-right font-semibold text-indigo-600">
                        ₱ {{ number_format($record->amount,2) }}
                    </td>

                    <td class="p-3 text-center">
                        {{ $record->created_at->format('M d Y') }}
                    </td>

                    <td class="p-3">
                        {{ $record->confirmer->name ?? 'OSA' }}
                    </td>

                </tr>

                @endforeach

            </tbody>

        </table>

    </div>

</div>

@endsection --}}
