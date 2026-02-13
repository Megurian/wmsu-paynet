@extends('layouts.dashboard')

@section('title', 'College Organizations')
@section('page-title', 'College Organizations')

@section('content')

<div class="mb-6 flex justify-end">
    <a href="{{ route('college.local_organizations.create') }}" 
       class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-blue-700 transition font-medium text-sm">
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

            <div class="flex-1">
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

            <div class="flex gap-3">
                @if(is_null($org->mother_organization_id))
                    {{-- <a href="{{ route('college.local_organizations.edit', $org) }}" 
                       class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition text-sm font-medium">
                        Edit
                    </a> --}}
                @else
                    <span class="text-gray-400 italic text-sm">No actions available</span>
                @endif
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