@extends('layouts.dashboard')

@section('title', 'Organization Approvals')
@section('page-title', 'College Organizations Approval')

@section('content')

@if(session('success'))
<div class="alert alert-success">{{ session('success') }}</div>
@endif
@if(session('error'))
<div class="alert alert-danger">{{ session('error') }}</div>
@endif

@if($orgs->count())
<ul class="space-y-2">
    @foreach($orgs as $org)
    <li class="border p-3 rounded flex justify-between items-center">
        <div>
            <strong>{{ $org->name }}</strong> ({{ $org->org_code }})
            @if(is_null($org->mother_organization_id))
                — Status: <span class="capitalize">{{ $org->status ?? 'N/A' }}</span>
            @else
                <strong>{{ $org->motherOrganization->name ?? 'N/A' }}</strong>
            @endif
        </div>

        <div class="flex gap-2">
            @if(is_null($org->mother_organization_id) && $org->status === 'pending')
            <form method="POST" action="{{ route('college.local_organizations.approve', $org) }}">
                @csrf
                <button type="submit" class="btn btn-success">Approve</button>
            </form>

            <form method="POST" action="{{ route('college.local_organizations.reject', $org) }}">
                @csrf
                <button type="submit" class="btn btn-danger">Reject</button>
            </form>
            @elseif(!is_null($org->mother_organization_id))
                <span class="text-gray-500 italic">No actions available</span>
            @else
                <span class="text-gray-500 italic">No actions available</span>
            @endif
        </div>
    </li>
    @endforeach
</ul>
@else
<p>No organizations found.</p>
@endif

@endsection