@extends('layouts.dashboard')

@section('title', 'College Fees')
@section('page-title', 'College Fees')

@section('content')
<div class="flex justify-between items-center mb-6">
    <h2 class="text-2xl font-bold text-gray-800">Fees List</h2>
    <a href="{{ route('college.fees.create') }}"
       class="bg-red-700 hover:bg-red-800 text-white px-4 py-2 rounded-lg shadow">
        + Add Fee
    </a>
</div>

<div class="bg-white shadow rounded-lg p-4">
    @if($fees->isEmpty())
        <p class="text-gray-500 text-center py-6">No fees found. Click "Add Fee" to create one.</p>
    @else
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">#</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fee Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($fees as $index => $fee)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">{{ $index + 1 }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">{{ $fee->fee_name }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">₱ {{ number_format($fee->amount, 2) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($fee->status === 'approved')
                                    <span class="inline-block bg-green-100 text-green-800 text-xs font-semibold px-2 py-1 rounded-full">
                                        Approved
                                    </span>
                                @elseif($fee->status === 'pending')
                                    <span class="inline-block bg-yellow-100 text-yellow-800 text-xs font-semibold px-2 py-1 rounded-full">
                                        Pending Approval
                                    </span>
                                @elseif($fee->status === 'rejected')
                                    <span class="inline-block bg-red-100 text-red-800 text-xs font-semibold px-2 py-1 rounded-full">
                                        Rejected
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap space-x-2">
                                {{-- Actions (optional) --}}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
@endsection