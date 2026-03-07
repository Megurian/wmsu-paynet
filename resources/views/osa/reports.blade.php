@extends('layouts.dashboard')

@section('title', 'OSA Reports')
@section('page-title', 'OSA Reports')

@section('content')
<div class="space-y-6">

    <div class="p-4 bg-white rounded shadow">
        <h3 class="text-lg font-semibold mb-4">Colleges Overview</h3>

        @if($colleges->isEmpty())
            <p class="text-gray-500">No colleges found.</p>
        @else
        <div class="overflow-x-auto">
            <table class="min-w-full border border-gray-200 text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="border-b p-2 text-left">Logo</th>
                        <th class="border-b p-2 text-left">College Name</th>
                        <th class="border-b p-2 text-left">College Code</th>
                        <th class="border-b p-2 text-left">Number of Admins</th>
                        <th class="border-b p-2 text-left">Local Orgs</th>
                        <th class="border-b p-2 text-left">Child Orgs</th>
                        <th class="border-b p-2 text-left">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($colleges as $college)
                    <tr class="hover:bg-gray-50">
                        <td class="p-2">
                            @if($college->logo)
                                <img src="{{ asset('storage/' . $college->logo) }}" class="w-10 h-10 object-contain rounded border">
                            @else
                                <span class="text-gray-400">N/A</span>
                            @endif
                        </td>
                        <td class="p-2 font-medium">{{ $college->name }}</td>
                        <td class="p-2">{{ $college->college_code }}</td>
                        <td class="p-2">{{ $college->users_count }}</td>
                        <td class="p-2">{{ $college->local_orgs_count }}</td>
                        <td class="p-2">{{ $college->child_orgs_count }}</td>
                        <td class="p-2">
                            <a href="{{ route('osa.college.details', $college->id) }}"
                               class="text-blue-600 hover:underline text-xs">View Details</a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>

</div>
@endsection