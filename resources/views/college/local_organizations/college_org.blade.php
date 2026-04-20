@extends('layouts.dashboard')

@section('title', 'College Organizations')
@section('page-title', 'College Organizations')

@section('content')

<div class="mb-6 flex justify-end">
    <a href="{{ route('college.local_organizations.create') }}" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-blue-700 transition font-medium text-sm">
        Create New Organization
    </a>
</div>

@if(session('success'))
<div class="mb-4 p-4 rounded-lg bg-green-100 text-green-800">{{ session('success') }}</div>
@endif

@if(session('error'))
<div class="mb-4 p-4 rounded-lg bg-red-100 text-red-800">{{ session('error') }}</div>
@endif

@if($orgs->count())
<div class="space-y-6">
    @foreach($orgs as $org)
    <div class="bg-white shadow-md rounded-xl p-6 transition hover:shadow-lg">

        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">

            <div class="flex items-center gap-4 flex-1">
                @if($org->logo)
                <img src="{{ asset('storage/' . $org->logo) }}" alt="{{ $org->name }} Logo" class="w-16 h-16 rounded-full object-cover border border-gray-200">
                @else
                <div class="w-16 h-16 rounded-full bg-gray-100 flex items-center justify-center text-gray-400 border border-gray-200">
                    No Logo
                </div>
                @endif

                <div>
                    <h3 class="text-xl font-semibold text-gray-800">{{ $org->name }}</h3>
                    <p class="text-sm text-gray-500 mt-1">
                        Organization Code: <span class="font-medium text-gray-700">{{ $org->org_code }}</span>
                    </p>

                    <div class="mt-3 flex flex-wrap gap-2 items-center">
                        @if(is_null($org->mother_organization_id))
                        <span class="px-3 py-1 bg-blue-100 text-blue-700 text-xs rounded-full font-medium">
                            College Organization
                        </span>
                        @else
                        <span class="px-3 py-1 bg-purple-100 text-purple-700 text-xs rounded-full font-medium">
                            Office
                        </span>
                        <span class="text-sm text-gray-600">
                            <span class="font-medium">{{ $org->motherOrganization->name ?? 'N/A' }}</span>
                        </span>
                        @endif

                        @if($org->status)
                        @if($org->status === 'pending')
                        <span class="px-3 py-1 bg-yellow-100 text-yellow-700 text-xs rounded-full font-medium">
                            Pending Approval
                        </span>
                        @elseif($org->status === 'approved')
                        <span class="px-3 py-1 bg-green-100 text-green-700 text-xs rounded-full font-medium">
                            Approved
                        </span>
                        @elseif($org->status === 'rejected')
                        <span class="px-3 py-1 bg-red-100 text-red-700 text-xs rounded-full font-medium">
                            Rejected
                        </span>
                        @endif
                        @endif
                    </div>
                </div>
            </div>

            <div class="relative" x-data="{ menuOpen: false, modalOpen: false }">
                
                <button @click="menuOpen = !menuOpen" class="p-2 rounded-full hover:bg-gray-100 focus:outline-none">
                    <svg class="w-6 h-6 text-gray-600" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M6 10a2 2 0 11-4 0 2 2 0 014 0zm6 0a2 2 0 11-4 0 2 2 0 014 0zm6 0a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                </button>

                <div x-show="menuOpen" @click.away="menuOpen = false" class="absolute right-0 mt-2 w-48 bg-white border border-gray-200 rounded-md shadow-lg z-50">
                    @if($org->status === 'pending')
                    <button @click="modalOpen = true; menuOpen = false" class="w-full text-left block px-4 py-2 text-gray-700 hover:bg-gray-100">
                        View Submission
                    </button>
                    @else
                    <a href="{{ route('college.local_organizations.show', $org->id) }}" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">
                        View Details
                    </a>
                    @endif
                </div>

                <div x-show="modalOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50" style="display:none;">
                    <div class="bg-white rounded-lg shadow-xl w-11/12 md:w-5/6 lg:w-4/5 p-6 relative max-h-[90vh] overflow-y-auto">

                        <h2 class="text-2xl font-bold mb-4">Submission Preview</h2>

                        @if($org->status === 'pending')
                        <div class="mb-6 p-3 bg-gray-50 border-l-4 border-gray-400 text-gray-800 rounded">
                            This organization submission is <strong>pending dean approval</strong> and cannot be edited.
                            If there is an error, you may cancel the submission below.
                        </div>
                        @endif

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-4">
                                <h3 class="text-lg font-semibold">Organization Details</h3>
                                <div class="flex flex-col items-center gap-2">
                                    @if($org->logo)
                                    <img src="{{ asset('storage/' . $org->logo) }}" alt="{{ $org->name }} Logo" class="w-32 h-32 rounded-full object-cover border border-gray-300">
                                    @else
                                    <div class="w-32 h-32 rounded-full bg-gray-100 flex items-center justify-center text-gray-400 border border-gray-300">
                                        No Logo
                                    </div>
                                    @endif
                                </div>

                                <div class="space-y-2">
                                    <div class="border border-gray-300 rounded p-3">
                                        <p class="text-gray-600 font-medium">Name</p>
                                        <p class="text-gray-800">{{ $org->name }}</p>
                                    </div>
                                    <div class="border border-gray-300 rounded p-3">
                                        <p class="text-gray-600 font-medium">Organization Code</p>
                                        <p class="text-gray-800">{{ $org->org_code }}</p>
                                    </div>
                                    <div class="border border-gray-300 rounded p-3">
                                        <p class="text-gray-600 font-medium">Status</p>
                                        <p class="text-gray-800">{{ ucfirst($org->status) }}</p>
                                    </div>
                                </div>
                            </div>

                            @php
                            $admin = $org->users->first();
                            @endphp
                            <div class="space-y-4">
                                <h3 class="text-lg font-semibold">Initial Admin Details</h3>

                                @if($admin)
                                <div class="space-y-2">
                                    <div class="border border-gray-300 rounded p-3">
                                        <p class="text-gray-600 font-medium">Full Name</p>
                                        <p class="text-gray-800">{{ $admin->first_name }} {{ $admin->middle_name }} {{ $admin->last_name }} {{ $admin->suffix }}</p>
                                    </div>
                                    <div class="border border-gray-300 rounded p-3">
                                        <p class="text-gray-600 font-medium">Email</p>
                                        <p class="text-gray-800">{{ $admin->email }}</p>
                                    </div>
                                    <div class="border border-gray-300 rounded p-3">
                                        <p class="text-gray-600 font-medium">Role</p>
                                        <p class="text-gray-800"> {{ ucwords(str_replace('_', ' ', is_array($admin->role) ? implode(', ', $admin->role) : ($admin->role ?? ''))) }}</p>
                                    </div>
                                </div>
                                @else
                                <p class="text-gray-400">No admin info available.</p>
                                @endif
                            </div>

                        </div>

                        <div class="flex justify-end gap-3 mt-6">
                            <button @click="modalOpen = false" class="px-4 py-2 bg-gray-300 text-gray-700 rounded hover:bg-gray-400">
                                Close
                            </button>

                            @if($org->status === 'pending')
                            <form action="{{ route('college.local_organizations.cancel_submission', $org->id) }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700">
                                    Cancel Submission
                                </button>
                            </form>
                            @endif
                        </div>

                    </div>
                </div>
            </div>

        </div>
    </div>
    @endforeach
</div>
@else
<div class="text-center py-12">
    <h3 class="text-lg font-semibold text-gray-600">No organizations found.</h3>
    <p class="text-gray-400 mt-2">
        There are currently no organizations to display.
    </p>
</div>
@endif

@endsection
