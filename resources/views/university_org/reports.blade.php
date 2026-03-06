@extends('layouts.dashboard')

@section('title', 'Payment Collection Report')
@section('page-title', 'Payment Collection Report')

@section('content')
<div class="space-y-6">
    @if($motherOrg)
        <div class="p-4 bg-gray-100 rounded shadow">
            <h2 class="text-lg font-semibold">University Organization:</h2>
            <div class="flex items-center mt-2 space-x-4">
                @if($motherOrg->logo)
                    <img src="{{ asset('storage/' . $motherOrg->logo) }}" alt="Logo" class="w-16 h-16 object-contain rounded border">
                @endif
                <div>
                    <p class="font-medium">{{ $motherOrg->name }}</p>
                    <p class="text-sm text-gray-600">{{ $motherOrg->org_code }}</p>
                </div>
            </div>
        </div>

        <div>
            <h3 class="text-md font-semibold mb-2">Child Organizations</h3>

            @if($childOrgs->isEmpty())
                <p class="text-gray-500">No child organizations found.</p>
            @else
                <div class="space-y-6">
                    @foreach($childOrgs as $org)
                        <div class="p-4 border rounded space-y-3">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-4">
                                    @if($org->logo)
                                        <img src="{{ asset('storage/' . $org->logo) }}" alt="Logo" class="w-12 h-12 object-contain rounded border">
                                    @endif
                                    <div>
                                        <p class="font-medium">{{ $org->name }}</p>
                                        <p class="text-sm text-gray-600">Code: {{ $org->org_code }}</p>
                                        <p class="text-sm text-gray-600">College: {{ $org->college?->name ?? 'N/A' }}</p>
                                    </div>
                                </div>
                                <div class="text-sm text-gray-500">
                                    Admin: {{ $org->orgAdmin?->first_name ?? 'N/A' }} {{ $org->orgAdmin?->last_name ?? '' }}
                                </div>
                            </div>

                            {{-- Fees List --}}
                            <div class="mt-2 pl-16">
                                <h4 class="text-sm font-semibold mb-1">Fees:</h4>
                                @if($org->fees->isEmpty())
                                    <p class="text-gray-500 text-sm">No fees available.</p>
                                @else
                                    <ul class="space-y-1">
                                        @foreach($org->fees as $fee)
                                            <li class="p-2 border rounded bg-gray-50 flex justify-between items-center">
                                                <div>
                                                    <p class="font-medium">{{ $fee->fee_name }}</p>
                                                    <p class="text-sm text-gray-600">{{ $fee->requirement_level }} | PHP {{ number_format($fee->amount, 2) }}</p>
                                                </div>
                                                <div class="text-sm text-gray-500">
                                                    Status: {{ ucfirst($fee->status ?? 'pending') }}
                                                </div>
                                            </li>
                                        @endforeach
                                    </ul>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    @else
        <p class="text-red-500">You do not belong to a university-level organization.</p>
    @endif
</div>
@endsection