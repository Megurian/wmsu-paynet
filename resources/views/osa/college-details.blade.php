@extends('layouts.dashboard')

@section('title', $college->name . ' Details')
@section('page-title', $college->name . ' Overview')

@section('content')
<h2 class="text-2xl font-bold mb-4">{{ $college->name }} Overview</h2>

<p class="mb-2"><strong>College Code:</strong> {{ $college->college_code }}</p>

<h2 class="text-2xl font-bold mb-4">{{ $college->name }} - Admins</h2>

@if($college->admins->count())
    <ul class="list-disc pl-5">
        @foreach($college->admins as $admin)
            <li>{{ $admin->name }} ({{ $admin->email }})</li>
        @endforeach
    </ul>
@else
    <p class="text-gray-500 italic">No admins found for this college yet.</p>
@endif


<h3 class="text-lg font-semibold mt-4">Organizations</h3>
<p class="text-gray-500 italic">Placeholder for college organizations</p>
@endsection
