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
            <table class="min-w-full text-sm border border-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="p-2 border-b text-left">Logo</th>
                        <th class="p-2 border-b text-left">Organization</th>
                        <th class="p-2 border-b text-left">Org Code</th>
                        <th class="p-2 border-b text-left">College</th>
                        <th class="p-2 border-b text-left">Total Payment Collected</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach($childOrgs as $child)
                    <tr class="hover:bg-gray-50">

                        <td class="p-2 border-b">
                            @if($child->logo)
                            <img src="{{ asset('storage/'.$child->logo) }}" class="w-10 h-10 object-contain border rounded">
                            @else
                            <span class="text-gray-400">N/A</span>
                            @endif
                        </td>

                        <td class="p-2 border-b font-medium">
                            {{ $child->name }}
                        </td>

                        <td class="p-2 border-b">
                            {{ $child->org_code }}
                        </td>

                        <td class="p-2 border-b">
                            {{ $child->college->name ?? 'N/A' }}
                        </td>

                        <td class="p-2 border-b font-medium">
                            ₱ {{ number_format($child->totalPayments ?? 0, 2) }}
                        </td>

                    </tr>
                    @endforeach
                </tbody>

            </table>
        </div>
    </div>
    @endif



    @if($fees->isNotEmpty())
    <div class="space-y-4">
        <h3 class="text-lg font-semibold mb-2">Fees</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-1 gap-4">
            @foreach($fees as $fee)
            <div class="p-4 bg-white rounded shadow hover:shadow-md transition">
                <h4 class="text-md font-semibold mb-2">{{ $fee->fee_name }}</h4>
                <p><strong>Purpose:</strong> {{ $fee->purpose }}</p>
                <p><strong>Amount:</strong> ₱ {{ number_format($fee->amount, 2) }}</p>
                <p><strong>Scope:</strong> {{ ucfirst($fee->fee_scope) }}</p>
                <p><strong>Payments Count:</strong> {{ $fee->payment_count ?? 0 }}</p>

                <div class="mt-4 overflow-x-auto max-h-64">
                    <table class="min-w-full text-sm border border-gray-200">
                        <thead class="bg-gray-50 sticky top-0">
                            <tr>
                                <th class="p-2 border-b text-left">Student ID</th>
                                <th class="p-2 border-b text-left">Student Name</th>
                                <th class="p-2 border-b text-left">Status</th>
                                <th class="p-2 border-b text-left">Amount Paid</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($fee->studentPayments as $sp)
                            <tr class="hover:bg-gray-50 {{ $sp['status'] === 'Pending' ? 'bg-yellow-50' : 'bg-green-50' }}">
                                <td class="p-2 border-b">{{ $sp['student_id'] }}</td>
                                <td class="p-2 border-b">{{ $sp['student_name'] }}</td>
                                <td class="p-2 border-b">{{ $sp['status'] }}</td>
                                <td class="p-2 border-b">₱ {{ number_format($sp['amount_paid'], 2) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @else
    <p class="text-gray-500">No fees found for this organization.</p>
    @endif


</div>
@endsection
