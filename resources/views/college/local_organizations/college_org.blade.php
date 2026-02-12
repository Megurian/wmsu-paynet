@extends('layouts.dashboard')

@section('title', 'College Organizations')
@section('page-title', 'College Organizations')

@section('content')
<div class="mb-4">
    <a href="{{ route('college.local_organizations.create') }}" class="btn btn-primary">Create New Organization</a>
</div>

@if(session('success'))
<div class="alert alert-success">{{ session('success') }}</div>
@endif

<ul class="space-y-2">
    @forelse($orgs as $org)
        <li class="border p-3 rounded flex justify-between items-center">
            <div>
                <strong>{{ $org->name }}</strong> ({{ $org->org_code }})
                @if($org->status)
                     — Status: <span class="capitalize">{{ $org->status }}</span>
                @endif
            </div>
        </li>
    @empty
        <li>No organizations found.</li>
    @endforelse
</ul>
@endsection