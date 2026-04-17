@extends('layouts.dashboard')

@section('title', 'Manage College')
@section('page-title', 'College Management')

@section('content')
@php
    $activeTab = request()->get('tab', 'college'); 
@endphp

<div class="flex space-x-4 border-b border-gray-300 mb-4">
    <a href="{{ route('college.users.index', ['tab' => 'college']) }}"
       class="px-4 py-2 font-medium {{ $activeTab === 'college' ? 'border-b-2 border-red-600 text-red-600' : 'text-gray-600 hover:text-red-600' }}">
       College Info
    </a>
    <a href="{{ route('college.users.index', ['tab' => 'employees']) }}"
       class="px-4 py-2 font-medium {{ $activeTab === 'employees' ? 'border-b-2 border-red-600 text-red-600' : 'text-gray-600 hover:text-red-600' }}">
      Staff Management
    </a>
    <a href="{{ route('college.users.index', ['tab' => 'accounts']) }}"
       class="px-4 py-2 font-medium {{ $activeTab === 'accounts' ? 'border-b-2 border-red-600 text-red-600' : 'text-gray-600 hover:text-red-600' }}">
       Account Management
    </a>
</div>

<!-- College Info Tab -->
@if($activeTab === 'college')
    <div class="space-y-6">
        <div class="bg-white p-8 rounded shadow">
            <h2 class="text-2xl font-bold text-gray-800 mb-6 text-center md:text-left">College Information</h2>

            <div class="flex flex-col md:flex-row md:items-center md:space-x-12 space-y-6 md:space-y-0">
                <form id="logoForm" action="{{ route('college.info.updateLogo') }}" method="POST" enctype="multipart/form-data" class="relative flex-shrink-0 text-center md:text-left">
                    @csrf
                    @method('PUT')
                    <label class="block text-gray-700 font-medium mb-2">College Logo</label>

                    <label for="college_logo" class="cursor-pointer relative inline-block">
                        @if($currentCollege?->logo)
                            <img src="{{ asset('storage/' . $currentCollege->logo) }}" 
                                 alt="Logo" 
                                 class="h-48 w-48 object-cover rounded border border-gray-300 mx-auto md:mx-0">
                        @else
                            <div class="h-48 w-48 flex items-center justify-center rounded border border-gray-300 bg-gray-100 text-gray-500 mx-auto md:mx-0">
                                No Logo
                            </div>
                        @endif
                        <div class="absolute bottom-2 right-2 bg-red-800 text-white rounded-full p-2 hover:bg-red-700">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536M9 11l6-6 3 3-6 6H9v-3z" />
                            </svg>
                        </div>
                    </label>

                    <input type="file" name="college_logo" id="college_logo" class="hidden" onchange="confirmLogoUpdate(this)">
                </form>
                <form id="nameForm" action="{{ route('college.info.updateName') }}" method="POST" class="flex-1" onsubmit="return confirmNameUpdate()">
                    @csrf
                    @method('PUT')
                    <label for="college_name" class="block text-gray-700 font-medium mb-2">College Name</label>

                    <div class="flex items-center space-x-2">
                        <input type="text" name="college_name" id="college_name"
                               value="{{ $currentCollege->name ?? '' }}"
                               class="flex-1 border rounded px-3 py-3 focus:outline-none focus:ring focus:ring-red-700 text-lg"
                               placeholder="Enter College Name">
                        <button type="button"
                            onclick="openConfirmModal({
                                title: 'Update College Name',
                                message: 'Do you want to proceed?',
                                confirmText: 'Update',
                                onConfirm: () => this.closest('form').submit()
                            })" class="px-6 py-3 bg-red-800 text-white rounded hover:bg-red-700 whitespace-nowrap">
                            Update
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Courses, Year Levels, Sections --}}
        <div class="grid md:grid-cols-3 gap-6">

            {{-- Courses --}}
            <div class="bg-white rounded-lg shadow p-6 border border-gray-100">
                <h3 class="text-lg font-semibold text-gray-800 mb-3">Courses</h3>
                <p class="text-gray-600 text-sm mb-4">Add courses offered by your college.</p>

                <form method="POST" action="{{ route('college.courses.store') }}" class="flex gap-2 mb-4">
                    @csrf
                    <input type="text" name="name" required class="flex-1 border rounded-lg px-3 py-2 text-sm focus:ring-1 focus:ring-red-500" placeholder="e.g. BS Computer Science">
                    <button type="button"
                            onclick="openConfirmModal({
                                title: 'Course',
                                message: 'Do you want to proceed?',
                                confirmText: 'Confirm',
                                onConfirm: () => this.closest('form').submit()
                            })" class="bg-red-800 text-white px-4 py-2 rounded-lg hover:bg-red-700 transition">Add</button>
                </form>

                <ul class="text-gray-800 space-y-2 max-h-48 overflow-y-auto">
                    @forelse($courses as $course)
                        <li class="flex justify-between items-center border-b py-1">
                            <span>{{ $course->name }}</span>
                            <form action="{{ route('college.courses.destroy', $course->id) }}" method="POST" >
                                @csrf
                                @method('DELETE')
                                <button type="button"
                                   onclick="openConfirmModal({
                                    title: 'Delete Course',
                                    message: 'Do you want to proceed?',
                                    confirmText: 'Confirm',
                                    onConfirm: () => this.closest('form').submit()
                                })"class="text-red-600 hover:text-red-800 text-sm">Delete</button>
                            </form>
                        </li>
                    @empty
                        <li class="text-gray-400 italic">No courses yet</li>
                    @endforelse
                </ul>
            </div>

            {{-- Year Levels --}}
            <div class="bg-white rounded-lg shadow p-6 border border-gray-100">
                <h3 class="text-lg font-semibold text-gray-800 mb-3">Year Levels</h3>
                <p class="text-gray-600 text-sm mb-4">Specify year levels for your college.</p>

                <form method="POST" action="{{ route('college.years.store') }}" class="flex gap-2 mb-4">
                    @csrf
                    <input type="text" name="name" required class="flex-1 border rounded-lg px-3 py-2 text-sm focus:ring-1 focus:ring-red-500" placeholder="e.g. 1st Year">
                    <button  type="button"
                            onclick="openConfirmModal({
                            title: 'Year Level',
                            message: 'Do you want to proceed?',
                            confirmText: 'Confirm',
                            onConfirm: () => this.closest('form').submit()
                        })"class="bg-red-800 text-white px-4 py-2 rounded-lg hover:bg-red-700 transition">Add</button>
                </form>

                <ul class="text-gray-800 space-y-2 max-h-48 overflow-y-auto">
                    @forelse($years as $year)
                        <li class="flex justify-between items-center border-b py-1">
                            <span>{{ $year->name }}</span>
                            <form action="{{ route('college.years.destroy', $year->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this year level?');">
                                @csrf
                                @method('DELETE')
                                <button type="button"
                                   onclick="openConfirmModal({
                                    title: 'Delete Year Level',
                                    message: 'Do you want to proceed?',
                                    confirmText: 'Confirm',
                                    onConfirm: () => this.closest('form').submit()
                                })"class="text-red-600 hover:text-red-800 text-sm">Delete</button>
                            </form>
                        </li>
                    @empty
                        <li class="text-gray-400 italic">No year levels yet</li>
                    @endforelse
                </ul>
            </div>

            {{-- Sections --}}
            <div class="bg-white rounded-lg shadow p-6 border border-gray-100">
                <h3 class="text-lg font-semibold text-gray-800 mb-3">Sections</h3>
                <p class="text-gray-600 text-sm mb-4">Include sections available within your college.</p>

                <form method="POST" action="{{ route('college.sections.store') }}" class="flex gap-2 mb-4">
                    @csrf
                    <input type="text" name="name" required class="flex-1 border rounded-lg px-3 py-2 text-sm focus:ring-1 focus:ring-red-500" placeholder="Section (e.g. A)">
                    <button type="button"
                                   onclick="openConfirmModal({
                                    title: 'Sections',
                                    message: 'Do you want to proceed?',
                                    confirmText: 'Confirm',
                                    onConfirm: () => this.closest('form').submit()
                                })"class="bg-red-800 text-white px-4 py-2 rounded-lg hover:bg-red-700 transition">Add</button>
                </form>

                <ul class="text-gray-800 space-y-2 max-h-48 overflow-y-auto">
                    @forelse($sections as $section)
                        <li class="flex justify-between items-center border-b py-1">
                            <span>{{ $section->name }}</span>
                            <form action="{{ route('college.sections.destroy', $section->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this section?');">
                                @csrf
                                @method('DELETE')
                                <button type="button"
                                   onclick="openConfirmModal({
                                    title: 'Delete Section',
                                    message: 'Do you want to proceed?',
                                    confirmText: 'Confirm',
                                    onConfirm: () => this.closest('form').submit()
                                })"class="text-red-600 hover:text-red-800 text-sm">Delete</button>
                            </form>
                        </li>
                    @empty
                        <li class="text-gray-400 italic">No sections yet</li>
                    @endforelse
                </ul>
            </div>

        </div>
    </div>
@endif

<!-- Account Management Tab -->
@if($activeTab === 'accounts')
    <div x-data="assignCourse()" class="space-y-6">

        <div class="bg-white p-8 rounded shadow flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold text-gray-800">Account Management</h2>
                <p class="text-gray-600 text-sm mt-1">
                    Manage coordinators, advisers, and assessors assigned to this college.
                </p>
            </div>

            <a href="{{ route('college.users.create') }}"
               class="inline-flex items-center px-5 py-2.5 bg-red-800 text-white rounded-lg hover:bg-red-700 transition text-sm font-medium">
                Add Account
            </a>
        </div>

        @if($users->count())
            <div class="space-y-4">
                @foreach($users as $user)
                    <div class="bg-white rounded-xl shadow p-6 hover:shadow-md transition">

                        <h3 class="text-lg font-semibold text-gray-800">
                            {{ $user->first_name }}
                            {{ $user->middle_name ? substr($user->middle_name, 0, 1).'.' : '' }}
                            {{ $user->last_name }}
                            {{ $user->suffix }}
                        </h3>

                        <p class="text-sm text-gray-500 mt-1">
                            {{ $user->email }}
                        </p>

                        <div class="flex flex-col md:flex-row md:items-center md:justify-between mt-4 gap-3">

                            <div class="flex flex-wrap gap-2">
                                <span class="bg-red-100 text-red-800 text-xs font-medium px-3 py-1 rounded-full capitalize">
                                    {{ str_replace('_', ' ', $user->role) }}
                                </span>

                                @if($user->role === 'adviser' && $user->course)
                                    <span class="bg-blue-100 text-blue-800 text-xs font-medium px-3 py-1 rounded-full">
                                        {{ $user->course->name }}
                                    </span>
                                @endif
                            </div>

                            <div class="flex items-center gap-4">

                                @if($user->role === 'adviser')
                                    <button 
                                        @click="openAssignModal({{ $user->id }}, '{{ $user->first_name }} {{ $user->last_name }}', {{ $user->course_id ?? 'null' }})"
                                        class="text-sm font-medium text-green-600 hover:text-green-800">
                                        Assign
                                    </button>
                                @endif

                                <form action="{{ route('college.users.destroy', $user) }}" method="POST" onsubmit="return confirm('Delete this user?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button"
                                        onclick="openConfirmModal({
                                            title: 'Delete User',
                                            message: 'Do you want to proceed?',
                                            confirmText: 'Confirm',
                                            onConfirm: () => this.closest('form').submit()
                                        })" class="text-sm font-medium text-red-600 hover:text-red-800">
                                        Delete
                                    </button>
                                </form>

                            </div>

                        </div>

                    </div>
                @endforeach
            </div>
        @else
            <div class="bg-white p-8 rounded-xl shadow text-center text-gray-500 italic">
                No users have been added yet.
            </div>
        @endif
        <div x-cloak x-show="showModal" class="fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center z-50">
            <div @click.away="closeModal()" class="bg-white rounded-xl shadow-lg w-full max-w-md p-6 relative">
                <button @click="closeModal()" class="absolute top-2 right-3 text-gray-500 hover:text-gray-800 text-xl">&times;</button>

                <h3 class="text-lg font-semibold mb-4">Assign Course/Batch</h3>
                <p class="text-sm text-gray-700 mb-4">Assign a course to <span class="font-semibold" x-text="userName"></span>.</p>

                <form :action="`/college/users/${userId}/assign-course`" method="POST">
                    @csrf
                    <select name="course_id" class="w-full border rounded px-3 py-2 text-sm mb-4" x-model="courseId">
                        <option value="">Select Course</option>
                        @foreach($courses as $course)
                            <option :value="{{ $course->id }}">{{ $course->name }}</option>
                        @endforeach
                    </select>

                    <div class="flex justify-end gap-2">
                        <button type="button" @click="closeModal()" class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400">Cancel</button>
                        <button type="submit" class="px-4 py-2 bg-red-800 text-white rounded hover:bg-red-700">Assign</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endif

@if($activeTab === 'employees')
    @include('college.employees')
@endif

<script>
    function assignCourse() {
        return {
            showModal: false,
            userId: null,
            userName: '',
            courseId: null,

            openAssignModal(id, name, courseId) {
                this.userId = id;
                this.userName = name;
                this.courseId = courseId ? parseInt(courseId) : null;
                this.showModal = true;
            },

            closeModal() {
                this.showModal = false;
                this.userId = null;
                this.userName = '';
                this.courseId = null;
            }
        }
    }

    function confirmLogoUpdate(input) {
        if (!input.files || input.files.length === 0) return;

        const fileName = input.files[0].name;

        openConfirmModal({
            title: 'Update Logo',
            message: `Upload "${fileName}" as new logo?`,
            confirmText: 'Upload',
            onConfirm: () => {
                input.form.submit();
            }
        });
    }

    function confirmNameUpdate() {
        return confirm("Are you sure you want to update the college name?");
    }
</script>
@endsection