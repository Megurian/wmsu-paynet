@extends('layouts.dashboard')

@section('title', 'Organization Details')
@section('page-title', $org->name)

@section('content')
<div class="space-y-6">

    <div>
        <a href="{{ url()->previous() }}" class="text-sm text-gray-600 hover:text-gray-900">← Back</a>
    </div>

    <!-- Organization Info -->
    <div class="p-4 bg-white rounded shadow">
        <h3 class="text-lg font-semibold mb-4">Organization Info</h3>

        <div class="space-y-2">
            <p><strong>Name:</strong> {{ $org->name }}</p>
            <p><strong>Org Code:</strong> {{ $org->org_code }}</p>
            <p><strong>College:</strong> {{ $org->college->name ?? 'N/A' }}</p>
            <p><strong>Total Payments:</strong> ₱ {{ number_format($org->totalPayments ?? 0, 2) }}</p>
            @if($org->logo)
            <p><strong>Logo:</strong></p>
            <img src="{{ asset('storage/'.$org->logo) }}" class="w-20 h-20 border rounded">
            @endif
        </div>
    </div>

    @if($fees->isNotEmpty())
    <div class="p-4 bg-white rounded shadow">
        <h3 class="text-lg font-semibold mb-4">Fees</h3>
        <div class="overflow-x-auto">
            <table class="min-w-full border text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="p-2 border-b text-left">Fee Name</th>
                        <th class="p-2 border-b text-left">Purpose</th>
                        <th class="p-2 border-b text-left">Amount</th>
                        <th class="p-2 border-b text-left">Payments Count</th>
                        <th class="p-2 border-b text-left">Scope</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($fees as $fee)
                    <tr class="hover:bg-gray-50">
                        <td class="p-2 font-medium">{{ $fee->fee_name }}</td>
                        <td class="p-2">{{ $fee->purpose }}</td>
                        <td class="p-2">₱ {{ number_format($fee->amount, 2) }}</td>
                        <td class="p-2">{{ $fee->payment_count ?? 0 }}</td>
                        <td class="p-2">{{ ucfirst($fee->fee_scope) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

</div>
@endsection
