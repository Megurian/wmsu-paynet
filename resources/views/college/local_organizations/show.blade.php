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
                    <span>
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

    <div x-data="{ open: true, showModal: false }" class="bg-white shadow-md rounded-xl w-full mt-4">
        <div class="flex justify-between items-center px-6 py-4 border-b">
            <h3 class="font-medium text-gray-800">Organization Officers ({{ $users->count() }})</h3>
            <button @click="showModal = true" class="px-3 py-1 bg-indigo-600 text-white text-sm rounded-lg hover:bg-indigo-700 transition">
                + Assign Officer
            </button>
        </div>

        <div x-show="open" class="px-6 py-4 space-y-2">
            @forelse($users as $user)
            <div class="p-4 border border-gray-100 rounded-lg flex justify-between items-center bg-gray-50">
                <div>
                    <p class="font-bold text-gray-800">{{ $user->first_name }} {{ $user->last_name }}</p>
                    <p class="text-xs text-gray-500">{{ $user->email }}</p>
                </div>
                <span class="px-2 py-1 text-xs bg-green-100 text-green-700 rounded-full">Active Officer</span>
            </div>
            @empty
            <p class="text-gray-400 text-center py-4">No officers assigned yet.</p>
            @endforelse
        </div>

        <div x-show="showModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 px-4" x-cloak>
            <div class="bg-white rounded-xl shadow-lg max-w-md w-full p-6">
                <h2 class="text-xl font-bold mb-4">Assign New Officer</h2>
                <form action="{{ route('college.local_organizations.assign', $org->id) }}" method="POST">
                    @csrf
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Select Student</label>
                        <select name="student_id" class="w-full border-gray-300 rounded-lg focus:ring-indigo-500 shadow-sm" required>
                            <option value="">Select an enrolled student...</option>
                            @foreach($eligibleStudents as $student)
                            <option value="{{ $student->id }}">{{ $student->last_name }}, {{ $student->first_name }} ({{ $student->email }})</option>
                            @endforeach
                        </select>
                        <p class="mt-2 text-xs text-gray-500">Only students from your college are listed here.</p>
                    </div>

                    <div class="flex justify-end space-x-3 mt-6">
                        <button type="button" @click="showModal = false" class="px-4 py-2 text-gray-600 hover:text-gray-800">Cancel</button>
                        <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg font-medium">Confirm Assignment</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>
@endsection
