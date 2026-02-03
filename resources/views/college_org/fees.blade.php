@extends('layouts.dashboard')

@section('title', 'Fees')
@section('page-title', 'College Org Fees')


@section('content')
<div class="mb-8">
    <h2 class="text-3xl font-bold text-gray-800">{{ Auth::user()->name }} Setup of Fees</h2>
    <p class="text-sm text-gray-500 mt-1">
        Welcome, {{ Auth::user()->name }}. Here you can manage the fees associated with different colleges within the university.
    </p>
    <br>
    <a href="{{ route('college_org.fees.create') }}" class="inline-flex items-center px-4 py-2 bg-red-700 text-white rounded hover:bg-red-800 transition">New Local Fee</a>
</div>

    <!-- Fees Section -->
    <div class="bg-white rounded shadow p-6">
    <h3 class="text-xl font-semibold mb-4">Fees</h3>
    <p class="text-gray-500 italic">Fees information for this organization will appear here.</p>

    <table class="w-full text-left border-collapse mt-4">
        <thead>
            <tr class="bg-gray-100">
                <th class="border px-4 py-2">Fee Name</th>
                <th class="border px-4 py-2">Amount</th>
                <th class="border px-4 py-2">Requirement</th>
                <th class="border px-4 py-2">Source</th>
                <th class="border px-4 py-2">Status</th>
                <th class="border px-4 py-2">Action</th>
            </tr>
        </thead>
        <tbody>
            @if(isset($fees) && $fees->count())
                @foreach($fees as $fee)
                    <tr>
                        <td class="border px-4 py-2">{{ $fee->fee_name }}</td>
                        <td class="border px-4 py-2">₱{{ number_format($fee->amount, 2) }}</td>
                        <td class="border px-4 py-2 capitalize">{{ $fee->requirement_level }}</td>
                        <td class="border px-4 py-2">
                            @php
                                $feeOrg = $fee->organization;
                                $mother = $organization->motherOrganization; // may be null
                            @endphp

                            @if($feeOrg->id === $organization->id)
                                This Organization
                            @elseif($feeOrg->org_code === 'OSA')
                                OSA
                            @elseif($mother && $feeOrg->id === $mother->id)
                                {{-- display actual mother org code dynamically --}}
                                {{ $mother->org_code }}
                            @else
                                {{ $feeOrg->org_code }}
                            @endif
                        </td>
                        <td class="border px-4 py-2">{{ ucfirst($fee->status) }}</td>
                        <td class="border px-4 py-2">
                            <a href="{{ route('college_org.fees.show', $fee->id) }}" class="inline-flex items-center px-3 py-1 bg-blue-600 text-white rounded hover:bg-blue-700">View</a>

                            @if($fee->organization->id === $organization->id && $fee->status !== 'approved')
                                <a href="{{ route('college_org.fees.edit', $fee->id) }}" class="inline-flex items-center px-3 py-1 bg-yellow-500 text-white rounded hover:bg-yellow-600 ml-2">Edit</a>
                                <form method="POST" action="{{ route('college_org.fees.destroy', $fee->id) }}" class="inline-block ml-2" onsubmit="return confirm('Delete this fee?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="px-3 py-1 bg-red-600 text-white rounded">Delete</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @endforeach
            @else
                <tr>
                    <td class="border px-4 py-2" colspan="5">No approved fees found for your organization or its mother organization.</td>
                </tr>
            @endif
        </tbody>
    </table>
</div>
@endsection