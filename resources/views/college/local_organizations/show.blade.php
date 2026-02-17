@extends('layouts.dashboard')

@section('title', 'Organization Details')
@section('page-title', 'Organization Details')

@section('content')
<div class="bg-white shadow-md rounded-xl p-6 max-w-3xl mx-auto space-y-6">

    <div class="flex justify-center mb-4">
        @if($org->logo)
            <img src="{{ asset('storage/' . $org->logo) }}" alt="{{ $org->name }} Logo" class="w-32 h-32 rounded-full object-cover border border-gray-200">
        @else
            <div class="w-32 h-32 rounded-full bg-gray-100 flex items-center justify-center text-gray-400 border border-gray-200">
                No Logo
            </div>
        @endif
    </div>

    <h2 class="text-2xl font-bold text-gray-800 text-center">{{ $org->name }}</h2>
    <p class="text-gray-500 text-center mt-1">
        Organization Code: <span class="font-medium text-gray-700">{{ $org->org_code }}</span>
    </p>

    <div class="mt-4 space-y-2">
        <div>
            <span class="font-medium text-gray-700">Type:</span>
            @if(is_null($org->mother_organization_id))
                College Organization
            @else
                Office ({{ $org->motherOrganization->name ?? 'N/A' }})
            @endif
        </div>

        <div>
            <span class="font-medium text-gray-700">Status:</span>
            @if($org->status === 'pending')
                Pending Approval
            @elseif($org->status === 'approved')
                Approved
            @elseif($org->status === 'rejected')
                Rejected
            @endif
        </div>
    </div>

    <div class="mt-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-2">Approved Fees</h3>
        @if($fees->count())
            <ul class="space-y-2">
                @foreach($fees as $fee)
                    <li class="p-4 border border-gray-200 rounded-lg">
                        <div class="flex justify-between items-center">
                            <span class="font-medium">{{ $fee->fee_name }}</span>
                            <span class="text-sm text-gray-500">{{ \Carbon\Carbon::parse($fee->date)->format('M d, Y') }}</span>
                        </div>
                        <div class="mt-1 text-gray-600 text-sm">
                            Amount: ₱{{ number_format($fee->amount, 2) }}<br>
                            Requirement: {{ $fee->requirement ?? 'N/A' }}<br>
                            Description: {{ $fee->description ?? 'N/A' }}
                        </div>
                    </li>
                @endforeach
            </ul>
        @else
            <p class="text-gray-400">No approved fees for this organization.</p>
        @endif
    </div>

    <div class="mt-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-2">User Accounts</h3>
        @if($users->count())
            <ul class="space-y-2">
                @foreach($users as $user)
                    <li class="p-4 border border-gray-200 rounded-lg">
                        <div class="flex justify-between items-center">
                            <span class="font-medium">{{ $user->first_name }} {{ $user->last_name }} {{ $user->suffix }}</span>
                            <span class="text-sm text-gray-500">{{ $user->email }}</span>
                        </div>
                        <div class="mt-1 text-gray-600 text-sm">
                            Role: {{ ucwords(str_replace('_', ' ', $user->role)) }}<br>
                            College ID: {{ $user->college_id ?? 'N/A' }}
                        </div>
                    </li>
                @endforeach
            </ul>
        @else
            <p class="text-gray-400">No users found for this organization.</p>
        @endif
    </div>

    <div class="mt-6 text-center">
        <a href="{{ route('college.local_organizations') }}" 
           class="px-4 py-2 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300 transition font-medium">
           Back to Organizations
        </a>
    </div>
</div>
@endsection