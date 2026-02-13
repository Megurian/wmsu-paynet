@extends('layouts.dashboard')

@section('title', 'Organization Details')
@section('page-title', 'Organization Details')

@section('content')

<div class="mb-6">
    <a href="{{ route('college.local_organizations') }}"
       class="text-sm text-gray-600 hover:text-gray-800">
        ← Back to Organizations
    </a>
</div>

{{-- ORGANIZATION INFO --}}
<div class="bg-white shadow-md rounded-xl p-6 mb-8">
@if($organization)
    <div class="flex flex-col md:flex-row md:items-center gap-6">

       @if(optional($organization)->logo)
            <img src="{{ asset('storage/'.$organization->logo) }}"
                 class="w-24 h-24 rounded-lg object-cover border">
        @endif

        <div>
            <h2 class="text-2xl font-bold text-gray-800">
                {{ $organization->name }}
            </h2>

            <p class="text-gray-500 mt-1">
                Organization Code:
                <span class="font-medium text-gray-700">
                    {{ $organization->org_code }}
                </span>
            </p>

            <div class="mt-3 flex gap-2 flex-wrap">
                @if($organization->status === 'approved')
                    <span class="px-3 py-1 bg-green-100 text-green-700 text-xs rounded-full font-medium">
                        Approved
                    </span>
                @elseif($organization->status === 'pending')
                    <span class="px-3 py-1 bg-yellow-100 text-yellow-700 text-xs rounded-full font-medium">
                        Pending
                    </span>
                @elseif($organization->status === 'rejected')
                    <span class="px-3 py-1 bg-red-100 text-red-700 text-xs rounded-full font-medium">
                        Rejected
                    </span>
                @endif
            </div>
        </div>
    </div>
@endif
</div>

{{-- FEES SECTION --}}
<div class="mb-10">
    <h3 class="text-lg font-semibold text-gray-800 mb-4">
        Organization Fees
    </h3>

    @if($fees->count())
        <div class="space-y-4">
            @foreach($fees as $fee)
                <div class="bg-white shadow rounded-lg p-4">
                    <div class="flex justify-between items-center">
                        <div>
                            <h4 class="font-semibold text-gray-800">
                                {{ $fee->fee_name }}
                            </h4>
                            <p class="text-sm text-gray-500">
                                {{ $fee->purpose }}
                            </p>
                        </div>

                        <div class="text-right">
                            <p class="font-semibold text-gray-700">
                                ₱{{ number_format($fee->amount, 2) }}
                            </p>

                            <span class="text-xs px-2 py-1 rounded-full
                                {{ $fee->requirement_level === 'mandatory'
                                    ? 'bg-red-100 text-red-700'
                                    : 'bg-blue-100 text-blue-700' }}">
                                {{ ucfirst($fee->requirement_level) }}
                            </span>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <p class="text-gray-500">No fees created for this organization.</p>
    @endif
</div>

{{-- USERS SECTION --}}
<div>
    <h3 class="text-lg font-semibold text-gray-800 mb-4">
        User Accounts
    </h3>

    @if($users->count())
        <div class="space-y-4">
            @foreach($users as $user)
                <div class="bg-white shadow rounded-lg p-4 flex justify-between items-center">
                    <div>
                        <p class="font-medium text-gray-800">
                            {{ $user->last_name }},
                            {{ $user->first_name }}
                            {{ $user->middle_name }}
                        </p>
                        <p class="text-sm text-gray-500">
                            {{ $user->email }}
                        </p>
                    </div>

                    <span class="text-xs px-3 py-1 bg-gray-100 text-gray-700 rounded-full">
                        {{ ucfirst($user->role) }}
                    </span>
                </div>
            @endforeach
        </div>
    @else
        <p class="text-gray-500">No users assigned to this organization.</p>
    @endif
</div>

@endsection