@extends('layouts.dashboard')

@section('title', 'Organization Details')
@section('page-title', 'Organization Details')

@section('content')
<div class="max-w-4xl mx-auto space-y-4 mt-2">
    <div class="text-left">
        <a href="{{ route('college.local_organizations') }}" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300 transition font-medium">
            Back to Organizations
        </a>
    </div>

    <div class="bg-white shadow-md rounded-xl p-6 flex items-center space-x-6">
        @if($org->logo)
        <img src="{{ asset('storage/' . $org->logo) }}" alt="{{ $org->name }} Logo" class="w-32 h-32 rounded-full object-cover border border-gray-200">
        @else
        <div class="w-32 h-32 rounded-full bg-gray-100 flex items-center justify-center text-gray-400 border border-gray-200">
            No Logo
        </div>
        @endif

        <div class="flex-1 space-y-3">
            <div class="flex justify-between items-center ">
                <h2 class="text-2xl font-bold text-gray-800">{{ $org->name }}</h2>
                <span class="px-3 py-1 text-xs font-medium rounded-full 
                    @if(is_null($org->mother_organization_id)) bg-blue-100 text-blue-700
                    @else bg-purple-100 text-purple-700 @endif">
                    @if(is_null($org->mother_organization_id)) College Organization
                    @else Office @endif
                </span>
            </div>
            <span class="text-purple-700 font-bold ">
                @if(!is_null($org->mother_organization_id))
                {{ $org->motherOrganization->name ?? 'N/A' }}
                @endif
            </span>
            <p class="text-gray-500">
                <span class="font-medium text-gray-700">{{ $org->org_code }}</span>
            </p>
            <div class="flex flex-wrap gap-2 mt-2 items-center">
                <span class="px-3 py-1 text-xs font-medium rounded-full
                    @if($org->status === 'pending') bg-yellow-100 text-yellow-700
                    @elseif($org->status === 'approved') bg-green-100 text-green-700
                    @elseif($org->status === 'rejected') bg-red-100 text-red-700
                    @endif">
                    @if($org->status === 'pending') Pending Approval
                    @elseif($org->status === 'approved' && $org->approved_at) 
                    <span >
                        Approved on {{ \Carbon\Carbon::parse($org->approved_at)->format('M d, Y') }}
                    </span>
                    @elseif($org->status === 'rejected') Rejected
                    @endif
                </span>
            </div>
        </div>
    </div>

    <div x-data="{ open: true }" class="bg-white shadow-md rounded-xl w-full">
        <button @click="open = !open" class="w-full flex justify-between items-center px-6 py-4 font-medium text-gray-800 hover:bg-gray-100 rounded-t-xl focus:outline-none">
            <span>Approved Fees ({{ $fees->count() }})</span>
            <svg :class="{ 'rotate-180': open }" class="w-5 h-5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
        </button>
        <div x-show="open" class="px-6 pb-4 space-y-2">
            @if($fees->count())
            @foreach($fees as $fee)
            <div class="p-4 border border-gray-200 rounded-lg">
                <div class="flex justify-between items-center">
                    <span class="font-medium">{{ $fee->fee_name }}</span>
                    <span class="text-sm text-gray-500">{{ \Carbon\Carbon::parse($fee->date)->format('M d, Y') }}</span>
                </div>
                <div class="mt-1 text-gray-600 text-sm">
                    Amount: ₱{{ number_format($fee->amount, 2) }}<br>
                    Requirement: {{ $fee->requirement_level ?? 'N/A' }}<br>
                    Description: {{ $fee->description ?? 'N/A' }}
                </div>
            </div>
            @endforeach
            @else
            <p class="text-gray-400">No approved fees for this organization.</p>
            @endif
        </div>
    </div>

    <div x-data="{ open: true }" class="bg-white shadow-md rounded-xl w-full">
        <button @click="open = !open" class="w-full flex justify-between items-center px-6 py-4 font-medium text-gray-800 hover:bg-gray-100 rounded-t-xl focus:outline-none">
            <span>User Accounts ({{ $users->count() }})</span>
            <svg :class="{ 'rotate-180': open }" class="w-5 h-5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
        </button>
        <div x-show="open" class="px-6 pb-4 space-y-2">
            @if($users->count())
            @foreach($users as $user)
            <div class="p-4 border border-gray-200 rounded-lg">
                <div class="flex justify-between items-center">
                    <span class="font-medium">{{ $user->first_name }} {{ $user->last_name }} {{ $user->suffix }}</span>
                    <span class="text-sm text-gray-500">{{ $user->email }}</span>
                </div>
                <div class="mt-1 text-gray-600 text-sm">
                    {{ ucwords(str_replace('_', ' ', $user->role)) }}<br>
                </div>
            </div>
            @endforeach
            @else
            <p class="text-gray-400">No users found for this organization.</p>
            @endif
        </div>
    </div>

</div>
@endsection
