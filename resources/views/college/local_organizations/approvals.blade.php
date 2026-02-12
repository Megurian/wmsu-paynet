@extends('layouts.dashboard')

@section('title', 'Organization Approvals')
@section('page-title', 'Pending College Organizations')

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
            <strong>{{ $org->name }}</strong> ({{ $org->org_code }}) — Status: 
            <span class="capitalize">{{ $org->status }}</span>
        </div>
        <div class="flex gap-2">
            <form method="POST" action="{{ route('college.local_organizations.approve', $org) }}">
                @csrf
                <button type="submit" class="btn btn-success">Approve</button>
            </form>

            <form method="POST" action="{{ route('college.local_organizations.reject', $org) }}">
                @csrf
                <button type="submit" class="btn btn-danger">Reject</button>
            </form>
        </div>
    </li>
    @endforeach
</ul>
@else
<p>No pending organizations for approval.</p>
@endif

@endsection