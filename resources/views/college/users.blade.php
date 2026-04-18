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

    <div class="bg-white p-6 rounded shadow">

    <h2 class="text-xl font-bold mb-4">
        ROLE ASSIGNMENT (AY 2025-2026 | 1st Sem)
    </h2>

    <form method="POST" action="{{ route('college.roles.bulkAssign') }}">
        @csrf

        <div class="mb-4">
            <input type="text"
                   placeholder="Search Employee..."
                   class="w-full border rounded px-3 py-2">
        </div>

        <div class="overflow-x-auto">
            <table class="w-full border text-sm">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="border px-3 py-2 text-left">Employee</th>
                        <th class="border px-3 py-2">Advisor</th>
                        <th class="border px-3 py-2">Coordinator</th>
                        <th class="border px-3 py-2">Assessor</th>
                        <th class="border px-3 py-2">Treasurer</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach($employees as $employee)
                        <tr class="border-b">
                            <td class="px-3 py-2 font-medium">
                                {{ $employee->first_name }} {{ $employee->last_name }}
                            </td>

                            @php
                                $roles = $employee->position ?? [];
                            @endphp

                            @foreach(['adviser','student_coordinator','assessor','treasurer'] as $role)
                                <td class="text-center">
                                    <input type="checkbox"
                                           name="roles[{{ $employee->id }}][]"
                                           value="{{ $role }}"
                                           {{ in_array($role, $roles) ? 'checked' : '' }}>
                                </td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-4 flex justify-end">
            <button type="submit"
                    class="bg-red-800 text-white px-6 py-2 rounded hover:bg-red-700">
                Save Assignments
            </button>
        </div>

    </form>
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


     function openEditEmployeeModal(id) {
        document.getElementById('editEmployeeModal').classList.remove('hidden');

        document.getElementById('editEmployeeForm').action = `/employees/${id}`;

    }

    function openCreateAccountModal(id, name) {
        document.getElementById('createAccountModal').classList.remove('hidden');

        document.getElementById('accountEmployeeName').innerText = name;

        document.getElementById('createAccountForm').action = `/employees/${id}/create-account`;
    }
</script>
@endsection