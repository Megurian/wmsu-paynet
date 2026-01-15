@extends('layouts.dashboard')

@section('title', 'Academic Structure')
@section('page-title', 'Academic Structure')

@section('content')
<div class="max-w-5xl mx-auto space-y-8">

    <div>
        <a href="{{ route('college.students') }}" 
           class="inline-block mb-2 px-4 py-2 bg-red-800 text-white rounded-lg shadow hover:bg-red-700 transition">
            &larr; Back
        </a>
    </div>

    <div class="bg-white-50 p-5 rounded-lg shadow-md border border-gray-200">
        <h2 class="text-2xl font-bold text-red-800 mb-1">Academic Structure Management</h2>
        <p class="text-red-700 text-sm">Manage <strong>Courses</strong>, <strong>Year Levels</strong>, and <strong>Sections</strong> for your college. Changes will be reflected.</p>
    </div>

    <div class="grid md:grid-cols-3 gap-6">

        {{-- COURSES --}}
        <div class="bg-white rounded-lg shadow p-5 border border-gray-100">
            <h3 class="text-lg font-semibold text-gray-800 mb-2">Courses</h3>
            <p class="text-gray-600 text-sm mb-3">Add courses offered by your college. </p>

            <form method="POST" action="{{ route('college.courses.store') }}" class="flex gap-2 mb-4">
                @csrf
                <input type="text" name="name" required class="flex-1 border rounded-lg px-3 py-2 text-sm focus:ring-1 focus:ring-red-500" placeholder="e.g. BS Computer Science">
                <button class="bg-red-800 text-white px-4 py-2 rounded-lg hover:bg-red-700 transition">Add</button>
            </form>

            <ul class="text-gray-800 space-y-2 max-h-48 overflow-y-auto">
                @forelse($courses as $course)
                    <li class="flex justify-between items-center border-b py-1">
                        <span>{{ $course->name }}</span>
                        <form action="{{ route('college.courses.destroy', $course->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this course?');">
                            @csrf
                            @method('DELETE')
                            <button class="text-red-600 hover:text-red-800 text-sm">Delete</button>
                        </form>
                    </li>
                @empty
                    <li class="text-gray-400 italic">No courses yet</li>
                @endforelse
            </ul>
        </div>

        {{-- YEAR LEVELS --}}
        <div class="bg-white rounded-lg shadow p-5 border border-gray-100">
            <h3 class="text-lg font-semibold text-gray-800 mb-2">Year Levels</h3>
            <p class="text-gray-600 text-sm mb-3">Specify year levels for your college</p>

            <form method="POST" action="{{ route('college.years.store') }}" class="flex gap-2 mb-4">
                @csrf
                <input type="text" name="name" required class="flex-1 border rounded-lg px-3 py-2 text-sm focus:ring-1 focus:ring-red-500" placeholder="e.g. 1st Year">
                <button class="bg-red-800 text-white px-4 py-2 rounded-lg hover:bg-red-700 transition">Add</button>
            </form>

            <ul class="text-gray-800 space-y-2 max-h-48 overflow-y-auto">
                @forelse($years as $year)
                    <li class="flex justify-between items-center border-b py-1">
                        <span>{{ $year->name }}</span>
                        <form action="{{ route('college.years.destroy', $year->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this year level?');">
                            @csrf
                            @method('DELETE')
                            <button class="text-red-600 hover:text-red-800 text-sm">Delete</button>
                        </form>
                    </li>
                @empty
                    <li class="text-gray-400 italic">No year levels yet</li>
                @endforelse
            </ul>
        </div>

        {{-- SECTIONS --}}
        <div class="bg-white rounded-lg shadow p-5 border border-gray-100">
            <h3 class="text-lg font-semibold text-gray-800 mb-2">Sections</h3>
            <p class="text-gray-600 text-sm mb-3">Include sections available within your college</p>

            <form method="POST" action="{{ route('college.sections.store') }}" class="flex gap-2 mb-4">
                @csrf
                <input type="text" name="name" required class="flex-1 border rounded-lg px-3 py-2 text-sm focus:ring-1 focus:ring-red-500" placeholder="Section (e.g. A)">
                <button class="bg-red-800 text-white px-4 py-2 rounded-lg hover:bg-red-700 transition">Add</button>
            </form>

            <ul class="text-gray-800 space-y-2 max-h-48 overflow-y-auto">
                @forelse($sections as $section)
                    <li class="flex justify-between items-center border-b py-1">
                        <span>{{ $section->name }}</span>
                        <form action="{{ route('college.sections.destroy', $section->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this section?');">
                            @csrf
                            @method('DELETE')
                            <button class="text-red-600 hover:text-red-800 text-sm">Delete</button>
                        </form>
                    </li>
                @empty
                    <li class="text-gray-400 italic">No sections yet</li>
                @endforelse
            </ul>
        </div>

    </div>
</div>
@endsection
