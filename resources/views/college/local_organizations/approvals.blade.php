@extends('layouts.dashboard')

@section('title', 'Organization Approvals')
@section('page-title', 'College Organization Approvals')

@section('content')

@if(session('success'))
    <div class="mb-4 p-4 rounded-lg bg-green-100 text-green-800">{{ session('success') }}</div>
@endif

@if(session('error'))
    <div class="mb-4 p-4 rounded-lg bg-red-100 text-red-800">{{ session('error') }}</div>
@endif

<div class="flex space-x-2 mb-6">
    <a href="{{ route('college.local_organizations.approvals', ['tab' => 'pending']) }}"
       class="px-4 py-2 rounded-full text-sm font-medium transition
       {{ $tab === 'pending' ? 'bg-red-800 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }}">
        Pending Approval
        <span class="ml-2 inline-block bg-white text-red-800 font-semibold text-xs px-2 py-0.5 rounded-full">
            {{ $tab === 'pending' ? $orgs->count() : '' }}
        </span>
    </a>

    <a href="{{ route('college.local_organizations.approvals', ['tab' => 'all']) }}"
       class="px-4 py-2 rounded-full text-sm font-medium transition
       {{ $tab === 'all' ? 'bg-red-800 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }}">
        All Approved
    </a>
</div>

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
                            Under: <span class="font-medium">{{ $org->motherOrganization->name ?? 'N/A' }}</span>
                        </span>
                    @endif

                    @if(is_null($org->mother_organization_id))
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
                @if(is_null($org->mother_organization_id) && $org->status === 'pending')
                    <form method="POST" action="{{ route('college.local_organizations.approve', $org) }}">
                        @csrf
                        <button type="submit"
                            class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition text-sm font-medium">
                            Approve
                        </button>
                    </form>

                    <form method="POST" action="{{ route('college.local_organizations.reject', $org) }}">
                        @csrf
                        <button type="submit"
                            class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition text-sm font-medium">
                            Reject
                        </button>
                    </form>
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
        There are currently no organizations in this tab.
    </p>
</div>
@endif

@endsection