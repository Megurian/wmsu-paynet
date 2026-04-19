@extends('layouts.dashboard')

@section('title', 'Manage College')
@section('page-title', 'College Management')

@section('content')
@php
$activeTab = request()->get('tab', 'college');
@endphp

<div class="flex space-x-4 border-b border-gray-300 mb-4">
    <a href="{{ route('college.users.index', ['tab' => 'college']) }}" class="px-4 py-2 font-medium {{ $activeTab === 'college' ? 'border-b-2 border-red-600 text-red-600' : 'text-gray-600 hover:text-red-600' }}">
        College Info
    </a>
    <a href="{{ route('college.users.index', ['tab' => 'employees']) }}" class="px-4 py-2 font-medium {{ $activeTab === 'employees' ? 'border-b-2 border-red-600 text-red-600' : 'text-gray-600 hover:text-red-600' }}">
        Staff Management
    </a>
    <a href="{{ route('college.users.index', ['tab' => 'accounts']) }}" class="px-4 py-2 font-medium {{ $activeTab === 'accounts' ? 'border-b-2 border-red-600 text-red-600' : 'text-gray-600 hover:text-red-600' }}">
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
                    <img src="{{ asset('storage/' . $currentCollege->logo) }}" alt="Logo" class="h-48 w-48 object-cover rounded border border-gray-300 mx-auto md:mx-0">
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
                    <input type="text" name="college_name" id="college_name" value="{{ $currentCollege->name ?? '' }}" class="flex-1 border rounded px-3 py-3 focus:outline-none focus:ring focus:ring-red-700 text-lg" placeholder="Enter College Name">
                    <button type="button" onclick="openConfirmModal({
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
                <button type="button" onclick="openConfirmModal({
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
                    <form action="{{ route('college.courses.destroy', $course->id) }}" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="button" onclick="openConfirmModal({
                                    title: 'Delete Course',
                                    message: 'Do you want to proceed?',
                                    confirmText: 'Confirm',
                                    onConfirm: () => this.closest('form').submit()
                                })" class="text-red-600 hover:text-red-800 text-sm">Delete</button>
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
                <button type="button" onclick="openConfirmModal({
                            title: 'Year Level',
                            message: 'Do you want to proceed?',
                            confirmText: 'Confirm',
                            onConfirm: () => this.closest('form').submit()
                        })" class="bg-red-800 text-white px-4 py-2 rounded-lg hover:bg-red-700 transition">Add</button>
            </form>

            <ul class="text-gray-800 space-y-2 max-h-48 overflow-y-auto">
                @forelse($years as $year)
                <li class="flex justify-between items-center border-b py-1">
                    <span>{{ $year->name }}</span>
                    <form action="{{ route('college.years.destroy', $year->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this year level?');">
                        @csrf
                        @method('DELETE')
                        <button type="button" onclick="openConfirmModal({
                                    title: 'Delete Year Level',
                                    message: 'Do you want to proceed?',
                                    confirmText: 'Confirm',
                                    onConfirm: () => this.closest('form').submit()
                                })" class="text-red-600 hover:text-red-800 text-sm">Delete</button>
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
                <button type="button" onclick="openConfirmModal({
                                    title: 'Sections',
                                    message: 'Do you want to proceed?',
                                    confirmText: 'Confirm',
                                    onConfirm: () => this.closest('form').submit()
                                })" class="bg-red-800 text-white px-4 py-2 rounded-lg hover:bg-red-700 transition">Add</button>
            </form>

            <ul class="text-gray-800 space-y-2 max-h-48 overflow-y-auto">
                @forelse($sections as $section)
                <li class="flex justify-between items-center border-b py-1">
                    <span>{{ $section->name }}</span>
                    <form action="{{ route('college.sections.destroy', $section->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this section?');">
                        @csrf
                        @method('DELETE')
                        <button type="button" onclick="openConfirmModal({
                                    title: 'Delete Section',
                                    message: 'Do you want to proceed?',
                                    confirmText: 'Confirm',
                                    onConfirm: () => this.closest('form').submit()
                                })" class="text-red-600 hover:text-red-800 text-sm">Delete</button>
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

@if($activeTab === 'accounts')

@php
$activeSY = \App\Models\SchoolYear::where('is_active', true)->first();
$activeSem = \App\Models\Semester::where('is_active', true)->first();

$syLabel = $activeSY
? $activeSY->sy_start->format('Y') . '-' . $activeSY->sy_end->format('Y')
: 'No Active SY';

$semLabel = $activeSem->name ?? 'No Active Semester';
@endphp

<div class="bg-white rounded-xl shadow-sm border p-6 space-y-6">

    <!-- HEADER -->
    <div class="space-y-1">
        <h2 class="text-2xl font-bold text-gray-800">
            Role Assignment Panel
        </h2>

        <p class="text-sm text-gray-500">
            Academic Year: <span class="font-semibold text-gray-700">{{ $syLabel }}</span>
            • Semester: <span class="font-semibold text-gray-700">{{ $semLabel }}</span>
        </p>

        <p class="text-xs text-gray-400">
            Manage employee roles per semester. Make sure to double-check assignments before saving.
        </p>
    </div>

    <!-- ACTION BAR -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">

        <input type="text" placeholder="Search employee name or email..." class="w-full md:w-1/2 border rounded-lg px-4 py-2 text-sm focus:ring-2 focus:ring-red-200 focus:border-red-400">

        <a href="{{ route('college.roles.history') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-lg text-sm border text-center">
            View Role History
        </a>

    </div>

    <form method="POST" action="{{ route('college.roles.bulkAssign') }}">
        @csrf

        <!-- SAVE BUTTON -->
        <div class="flex justify-end">
            <button type="submit" class="bg-red-800 hover:bg-red-700 text-white px-6 py-2 rounded-lg text-sm shadow">
                Save Changes
            </button>
        </div>

        <!-- TABLE -->
        <div class="overflow-x-auto rounded-lg border">

            <table class="w-full text-sm">

                <thead class="bg-gray-50 text-gray-600 text-xs uppercase">
                    <tr>
                        <th class="text-left px-4 py-3">Employee</th>
                        <th class="text-center px-4 py-3">Roles</th>
                        <th class="text-center px-4 py-3">Status</th>
                    </tr>
                </thead>

                <tbody class="divide-y">

                    @foreach($accountEmployees as $employee)

                    @php
                    $roles = $employee->currentAssignment?->positions ?? [];
                    $isLockedAdviser = in_array('adviser', $roles);
                    @endphp

                    <tr class="hover:bg-gray-50">

                        <td class="px-4 py-4">
                            <div class="flex flex-col">
                                <span class="font-semibold text-gray-800">
                                    {{ $employee->first_name }} {{ $employee->last_name }}
                                </span>


                                <span class="text-xs text-gray-500"> 
                                    @if(!$employee->is_active) 
                                        <span class="text-red-500 font-medium">Disabled Account</span> 
                                    @else {{ $employee->email ?? $employee->user?->email ?? 'No Email' }} 
                                    @endif
                                </span>
                            </div>
                        </td>

                        <!-- ROLES -->
                        <td class="px-4 py-4">

                            <div class="grid grid-cols-2 md:grid-cols-4 gap-3">

                                <!-- ADVISER CARD -->
                                <label class="flex flex-col border rounded-lg p-2 transition
                                {{ $isLockedAdviser ? 'bg-gray-100 opacity-70 cursor-not-allowed' : 'hover:bg-gray-50 cursor-pointer' }}">

                                    <div class="flex items-center gap-2">

                                        <input type="checkbox" class="adviser-checkbox" data-employee="{{ $employee->id }}" name="roles[{{ $employee->id }}][]" value="adviser" {{ in_array('adviser', $roles) ? 'checked' : '' }} {{ $isLockedAdviser ? 'disabled' : '' }}>

                                        <span class="font-medium text-gray-800">Adviser</span>

                                        @if($isLockedAdviser)
                                        <span class="text-[10px] bg-red-100 text-red-600 px-2 py-0.5 rounded-full">
                                            Locked
                                        </span>
                                        @endif

                                    </div>

                                    <span class="text-[11px] text-gray-400 mt-1">
                                        Permanently assigned to a class
                                    </span>


                                </label>


                                <!-- COORDINATOR -->
                                <label class="flex flex-col border rounded-lg p-2 cursor-pointer hover:bg-gray-50">
                                    <div class="flex items-center gap-2">
                                        <input type="checkbox" name="roles[{{ $employee->id }}][]" value="student_coordinator" {{ in_array('student_coordinator', $roles) ? 'checked' : '' }}>
                                        <span>Coordinator</span>
                                    </div>
                                    <span class="text-[11px] text-gray-400 mt-1">
                                        Handles student coordination
                                    </span>
                                </label>

                                <!-- ASSESSOR -->
                                <label class="flex flex-col border rounded-lg p-2 cursor-pointer hover:bg-gray-50">
                                    <div class="flex items-center gap-2">
                                        <input type="checkbox" name="roles[{{ $employee->id }}][]" value="assessor" {{ in_array('assessor', $roles) ? 'checked' : '' }}>
                                        <span>Assessor</span>
                                    </div>
                                    <span class="text-[11px] text-gray-400 mt-1">
                                        Handles evaluation tasks
                                    </span>
                                </label>

                                <!-- TREASURER -->
                                <label class="flex flex-col border rounded-lg p-2 cursor-pointer hover:bg-gray-50">
                                    <div class="flex items-center gap-2">
                                        <input type="checkbox" name="roles[{{ $employee->id }}][]" value="treasurer" {{ in_array('treasurer', $roles) ? 'checked' : '' }}>
                                        <span>Treasurer</span>
                                    </div>
                                    <span class="text-[11px] text-gray-400 mt-1">
                                        Handles financial records
                                    </span>
                                </label>
                            </div>
                            <!-- COURSE SELECT -->
                            <div id="course-box-{{ $employee->id }}" class="mt-3 {{ in_array('adviser', $roles) ? '' : 'hidden' }}">

                                <label class="text-xs text-gray-500">
                                    Assigned Course
                                </label>
                                <br>
                                <select class="mt-1 w-1/2 border rounded-md px-2 py-1 text-xs
                                    {{ $isLockedAdviser ? 'bg-gray-100 text-gray-500 cursor-not-allowed' : '' }}" {{ $isLockedAdviser ? 'disabled' : '' }}>

                                    <option value="">Select Course</option>

                                    @foreach($courses as $course)
                                    <option value="{{ $course->id }}" {{ $employee->currentAssignment?->course_id == $course->id ? 'selected' : '' }}>
                                        {{ $course->name }}
                                    </option>
                                    @endforeach

                                </select>

                                @if($isLockedAdviser)
                                <p class="text-[10px] text-gray-400 mt-1">
                                    This adviser assignment is locked and cannot be changed.
                                </p>
                                @endif
                            </div>
                        </td>

                        <!-- STATUS -->
                        <td class="px-4 py-3 text-center">
                            <form action="{{ route('employees.toggle', $employee->id) }}" method="POST"> @csrf <button type="button" onclick="openConfirmModal({ title: '{{ $employee->is_active ? 'Disable' : 'Enable' }} Account', message: 'Do you want to {{ $employee->is_active ? 'disable' : 'enable' }} this account?', confirmText: 'Confirm', onConfirm: () => this.closest('form').submit() })" class="px-3 py-1 text-xs rounded {{ $employee->is_active ? 'bg-red-600 hover:bg-red-700 text-white' : 'bg-green-600 hover:bg-green-700 text-white' }}"> {{ $employee->is_active ? 'Disable Account' : 'Enable Account' }} </button> </form>
                        </td>


                    </tr>

                    @endforeach

                </tbody>
            </table>
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

    function openCreateAccountModal(id, name, email, department) {
            const modal = document.getElementById('createAccountModal');

            modal.classList.remove('hidden');

            document.getElementById('accountEmployeeName').innerText = name;

            document.getElementById('createAccountForm').action =
                `/employees/${id}/create-account`;

            document.getElementById('accountEmail').value = email ?? '';

            modal.dataset.department = department || '';
        }

    document.addEventListener('DOMContentLoaded', function () {

        document.querySelectorAll('.adviser-checkbox').forEach(cb => {
            cb.addEventListener('change', function () {

                const empId = this.dataset.employee;
                const box = document.getElementById('course-box-' + empId);

                if (this.checked) {
                    box.classList.remove('hidden');
                } else {
                    box.classList.add('hidden');

                    const select = box.querySelector('select');
                    if (select) select.value = '';
                }
            });
        });

    });

function openEditEmployeeModal(employee) {
    console.log(employee); // debug

    document.getElementById('editEmployeeModal').classList.remove('hidden');

    // FIX: ensure ID exists
    if (!employee.id) {
        console.error('Employee ID missing!', employee);
        return;
    }

    document.getElementById('editEmployeeForm').action = `/employees/${employee.id}`;

    document.getElementById('edit_first_name').value = employee.first_name || '';
    document.getElementById('edit_last_name').value = employee.last_name || '';
    document.getElementById('edit_middle_name').value = employee.middle_name || '';
    document.getElementById('edit_email').value = employee.email || '';

    const deptSelect = document.getElementById('edit_department');
    const otherInput = document.getElementById('edit_other_department');

    let found = false;

    for (let option of deptSelect.options) {
        if (option.value === employee.department) {
            found = true;
            break;
        }
    }

    if (found) {
        deptSelect.value = employee.department;
        otherInput.classList.add('hidden');
        otherInput.value = '';
    } else {
        deptSelect.value = 'other';
        otherInput.classList.remove('hidden');
        otherInput.value = employee.department || '';
    }
}
function toggleEditOtherDepartment(select) {
    const input = document.getElementById('edit_other_department');

    if (select.value === 'other') {
        input.classList.remove('hidden');
        input.setAttribute('name', 'department');
    } else {
        input.classList.add('hidden');
        input.value = '';
        input.setAttribute('name', 'other_department');
    }
}
</script>
@endsection
