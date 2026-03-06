@extends('layouts.dashboard')

@section('title', 'Child Organizations')
@section('page-title', 'All Child Organizations')

@section('content')

<div class="space-y-4">

    <div class="flex items-center justify-between">
        <h2 class="text-lg font-semibold">Child Organizations</h2>

        <a href="{{ route('university_org.reports') }}"
           class="text-sm text-blue-600 hover:underline">
           ← Back to Report
        </a>
    </div>

    @if($childOrgs->isEmpty())
        <p class="text-gray-500">No child organizations found.</p>
    @else

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">

            @foreach($childOrgs as $org)

            <div class="p-4 border rounded bg-white shadow-sm flex items-center space-x-3">

                @if($org->logo)
                    <img src="{{ asset('storage/'.$org->logo) }}"
                         class="w-12 h-12 object-contain border rounded">
                @endif

                <div class="text-sm">
                    <p class="font-semibold">{{ $org->name }}</p>
                    <p class="text-gray-500 text-xs">Code: {{ $org->org_code }}</p>
                    <p class="text-gray-500 text-xs">
                        College: {{ $org->college?->name ?? 'N/A' }}
                    </p>
                    <p class="text-gray-500 text-xs">
                        Admin: {{ $org->orgAdmin?->first_name ?? 'N/A' }}
                        {{ $org->orgAdmin?->last_name ?? '' }}
                    </p>
                </div>

            </div>

            @endforeach

        </div>

    @endif

</div>

@endsection