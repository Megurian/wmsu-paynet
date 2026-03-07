@extends('layouts.dashboard')

@section('title', 'Organization Details')
@section('page-title', $org->name)

@section('content')
<div class="space-y-6">

    <div>
        <a href="{{ url()->previous() }}" class="text-sm text-gray-600 hover:text-gray-900">← Back</a>
    </div>

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

    @if($childOrgs->isNotEmpty())
    <div class="p-4 bg-white rounded shadow">
        <h3 class="text-lg font-semibold mb-4">Child Organizations</h3>
        <div class="overflow-x-auto">
            <table class="min-w-full border text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="p-2 border-b text-left">Logo</th>
                        <th class="p-2 border-b text-left">Organization</th>
                        <th class="p-2 border-b text-left">Org Code</th>
                        <th class="p-2 border-b text-left">Total Payment Collected</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($childOrgs as $child)
                    <tr class="hover:bg-gray-50">
                        <td class="p-2">
                            @if($child->logo)
                                <img src="{{ asset('storage/'.$child->logo) }}" class="w-10 h-10 border rounded">
                            @else
                                <span class="text-gray-400">N/A</span>
                            @endif
                        </td>
                        <td class="p-2 font-medium">{{ $child->name }}</td>
                        <td class="p-2">{{ $child->org_code }}</td>
                        <td class="p-2">₱ {{ number_format($child->totalPayments ?? 0, 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

</div>
@endsection