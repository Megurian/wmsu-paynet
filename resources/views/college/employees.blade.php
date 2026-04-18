
<div class="bg-white p-6 rounded shadow space-y-6">

    <!-- Header -->
    <div class="flex justify-between items-center">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Staff Management</h2>
            <p class="text-sm text-gray-500">Master list of employees (Dean/Admin)</p>
        </div>

        <button onclick="document.getElementById('addEmployeeModal').classList.remove('hidden')"
            class="bg-red-800 text-white px-4 py-2 rounded">
             Add Employee
        </button>
    </div>

        <div class="space-y-4">

        @forelse($employees as $employee)
        <div class="bg-white border rounded-xl shadow-sm p-5 flex items-center justify-between hover:shadow-md transition
            {{ !$employee->is_active ? 'opacity-60' : '' }}">

            <div class="flex items-center gap-4 w-1/4">
                <div class="h-12 w-12 rounded-full bg-gray-200 flex items-center justify-center text-gray-600 font-bold">
                    {{ strtoupper(substr($employee->first_name, 0, 1)) }}
                </div>

                <div>
                    <div class="font-semibold text-gray-800">
                        {{ $employee->first_name }} {{ $employee->last_name }}
                    </div>

                    <div class="text-sm {{ !$employee->is_active ? 'text-red-500' : 'text-gray-500' }}">
                        @if(!$employee->is_active)
                            Disabled Account
                        @else
                            {{ $employee->email ?? $employee->user?->email ?? 'No Email' }}
                        @endif
                    </div>
                </div>

            </div>

            <div class="w-2/4 text-left space-y-1">

                @php
                    $roleLabels = [
                        'student_coordinator' => 'Student Coordinator',
                        'adviser' => 'Adviser',
                        'assessor' => 'Assessor',
                        'treasurer' => 'Treasurer',
                    ];

                    $roles = $employee->user?->role ?? $employee->position ?? [];
                    if (!is_array($roles)) $roles = [$roles];

                    $isAdviser = in_array('adviser', $roles);

                    $department = $employee->department ?? '-';

                    $course = $employee->user?->course ?? null;
                @endphp
                
                <div class="flex flex-wrap gap-1">
                    @foreach($roles as $role)
                        <span class="text-sm px-2 py-1 rounded-full bg-blue-100 text-blue-700">
                            {{ $roleLabels[$role] ?? ucfirst($role) }}
                        </span>
                    @endforeach

                   @if(in_array('adviser', $roles) && $employee->currentAssignment?->course)
                        <div >
                            <span class="text-sm px-2 py-1 bg-indigo-100 text-indigo-700 rounded-full">
                                 {{ $employee->currentAssignment->course->name }}
                            </span>
                        </div>
                    @endif
                </div>

                <div class="text-xs text-gray-500">
                    Department of {{ $department }}
                    
                </div>

            </div>

            <div class="w-1/4 flex flex-col items-end gap-2">

                <div class="text-right">
                    @if(!$employee->is_active)
                        <span class="text-xs text-red-500 font-semibold">Disabled Account</span>
                    @elseif($employee->has_account)
                        <span class="text-xs text-green-600 font-semibold">Active Account</span>
                    @else
                        <span class="text-xs text-gray-400">No Active Account</span>
                    @endif
                </div>

                <div class="flex gap-2">

                    <button
                        onclick="openEditEmployeeModal({{ $employee->id }})"
                        class="px-3 py-1 text-xs bg-blue-50 text-blue-600 rounded hover:bg-blue-100">
                        Edit
                    </button>

                    @if(!$employee->has_account)
                        <button
                            onclick="openCreateAccountModal(
                                {{ $employee->id }},
                                '{{ $employee->first_name }} {{ $employee->last_name }}',
                                '{{ $employee->email }}'
                            )"
                            class="px-3 py-1 text-xs bg-green-50 text-green-600 rounded hover:bg-green-100">
                            Create
                        </button>
                    @else
                        <span class="text-xs text-gray-400">Created</span>
                    @endif

                </div>

            </div>

        </div>
        @empty
            <div class="text-center text-gray-400 py-6">
                No employees yet
            </div>
        @endforelse

</div>
</div>

<div id="addEmployeeModal" class="hidden fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center">
    <div class="bg-white p-6 rounded w-full max-w-md">

        <h2 class="text-lg font-bold mb-4">Add Employee</h2>

        <form method="POST" action="{{ route('employees.store') }}">
            @csrf

            <input name="first_name" placeholder="First Name" class="border p-2 w-full mb-2">
            <input name="last_name" placeholder="Last Name" class="border p-2 w-full mb-2">
            <input name="middle_name" placeholder="Middle Name" class="border p-2 w-full mb-2">
            <input name="email" type="email" placeholder="Email" class="border p-2 w-full mb-2">

            <!-- DEPARTMENT DROPDOWN -->
            <label class="text-sm text-gray-600">Department</label>
            <select name="department" id="departmentSelect"
                class="border p-2 w-full mb-2"
                onchange="toggleOtherDepartment(this)">
                
                <option value="">Select Department</option>

                @foreach($courses as $course)
                    <option value="{{ $course->name }}">
                        {{ $course->name }}
                    </option>
                @endforeach

                <option value="other">Other</option>
            </select>

            <!-- IF OTHER -->
            <input type="text"
                name="other_department"
                id="otherDepartmentInput"
                placeholder="Enter Department"
                class="border p-2 w-full mb-2 hidden">

            <div class="flex justify-end gap-2">
                <button type="button"
                    onclick="document.getElementById('addEmployeeModal').classList.add('hidden')"
                    class="bg-gray-300 px-3 py-2 rounded">
                    Cancel
                </button>

                <button class="bg-red-800 text-white px-3 py-2 rounded">
                    Save
                </button>
            </div>

        </form>
    </div>
</div>

<div id="editEmployeeModal" class="hidden fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center">
    <div class="bg-white p-6 rounded w-full max-w-md">

        <h2 class="text-lg font-bold mb-4">Edit Employee</h2>

        <form id="editEmployeeForm" method="POST">
            @csrf
            @method('PUT')

            <input name="first_name" id="edit_first_name" class="border p-2 w-full mb-2">
            <input name="last_name" id="edit_last_name" class="border p-2 w-full mb-2">
            <input name="middle_name" id="edit_middle_name" class="border p-2 w-full mb-2">

            <select name="department" id="edit_department" class="border p-2 w-full mb-2">
                @foreach($courses as $course)
                    <option value="{{ $course->name }}">{{ $course->name }}</option>
                @endforeach
                <option value="other">Other</option>
            </select>

            <input name="email"
       id="accountEmail"
       placeholder="Email"
       class="border p-2 w-full mb-2">


            <div class="flex justify-end gap-2">
                <button type="button"
                    onclick="document.getElementById('editEmployeeModal').classList.add('hidden')"
                    class="bg-gray-300 px-3 py-2 rounded">
                    Cancel
                </button>

                <button class="bg-red-800 text-white px-3 py-2 rounded">
                    Update
                </button>
            </div>

        </form>
    </div>
</div>

<div id="createAccountModal" class="hidden fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center">
    <div class="bg-white p-6 rounded w-full max-w-md">

        <h2 class="text-lg font-bold mb-2">Create Account</h2>
        <p class="text-sm text-gray-600 mb-4" id="accountEmployeeName"></p>

        <form method="POST" id="createAccountForm">
            @csrf

            <!-- POSITION -->
            <label class="text-sm text-gray-600">Position</label>

            <div class="space-y-1 mb-2">

                <label>
                    <input type="checkbox" name="position[]" value="assessor">
                    Assessor
                </label>

                <label>
                    <input type="checkbox" name="position[]" value="student_coordinator">
                    Student Coordinator
                </label>

                <!-- ADVISER -->
                <label>
                    <input type="checkbox" name="position[]" value="adviser" id="adviserCheckbox">
                    Adviser
                </label>

                <label>
                    <input type="checkbox" name="position[]" value="treasurer">
                    Treasurer
                </label>

            </div>

            <!-- COURSE DROPDOWN (ONLY FOR ADVISER) -->
            <div id="courseDropdown" class="hidden mb-4">
                <label class="text-sm text-gray-600">
                    Assign Course (Required for Adviser)
                </label>

                <select name="course_id" id="courseSelect" class="border p-2 w-full">
                    <option value="">Select Course</option>
                    @foreach($courses as $course)
                        <option value="{{ $course->id }}">{{ $course->name }}</option>
                    @endforeach
                </select>

                @error('course_id')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <input name="email" placeholder="Email" class="border p-2 w-full mb-2">
            <input name="password" type="password" placeholder="Password" class="border p-2 w-full mb-4">

            <div class="flex justify-end gap-2">
                <button type="button"
                    onclick="document.getElementById('createAccountModal').classList.add('hidden')"
                    class="bg-gray-300 px-3 py-2 rounded">
                    Cancel
                </button>

                <button class="bg-green-600 text-white px-3 py-2 rounded">
                    Create
                </button>
            </div>

        </form>
    </div>
</div>

<script>
function toggleOtherDepartment(select) {
    const input = document.getElementById('otherDepartmentInput');

    if (select.value === 'other') {
        input.classList.remove('hidden');
        input.setAttribute('name', 'department');
    } else {
        input.classList.add('hidden');
        input.value = '';
        input.setAttribute('name', 'other_department');
    }
}

document.addEventListener('DOMContentLoaded', function () {

    const adviserCheckbox = document.getElementById('adviserCheckbox');
    const courseDropdown = document.getElementById('courseDropdown');
    const courseSelect = document.getElementById('courseSelect');

    function toggleCourseField() {
        if (adviserCheckbox.checked) {
            courseDropdown.classList.remove('hidden');
        } else {
            courseDropdown.classList.add('hidden');
            courseSelect.value = '';
        }
    }

    adviserCheckbox.addEventListener('change', toggleCourseField);

});

function openCreateAccountModal(id, name, email) {
    document.getElementById('createAccountModal').classList.remove('hidden');

    document.getElementById('accountEmployeeName').innerText = name;

    document.getElementById('createAccountForm').action =
        `/employees/${id}/create-account`;

    document.getElementById('accountEmail').value = email ?? '';
}
</script>