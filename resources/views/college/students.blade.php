@extends('layouts.dashboard')

@section('title', 'Student Directory')
@section('page-title', 'Student Directory')

@section('content')
<div class="flex justify-between items-center mb-6">
    <div>
        <h2 class="text-2xl font-bold">Student Directory</h2>
        <p class="text-sm text-gray-600">Manage students under your college</p>
    </div>

    <a href="{{ route('college.academics') }}"
       class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition">
        Manage Courses / Years / Sections
    </a>
</div>

{{-- Students table  --}}
@endsection
