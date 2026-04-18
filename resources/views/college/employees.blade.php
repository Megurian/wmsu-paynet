
<div class="bg-white p-6 rounded shadow space-y-6">

    <!-- Header -->
    <div class="flex justify-between items-center">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Staff Management</h2>
            <p class="text-sm text-gray-500">Master list of employees (Dean/Admin)</p>
        </div>

        <button onclick="document.getElementById('addEmployeeModal').classList.remove('hidden')"
            class="bg-red-800 text-white px-4 py-2 rounded">
            + Add Employee
        </button>
    </div>

    <!-- Table -->
    <div class="overflow-x-auto">
        <table class="w-full border text-sm">
            <thead class="bg-gray-100">
                <tr>
                    <th class="p-2 text-left">Name</th>
                    <th class="p-2 text-left">Department</th>
                    <th class="p-2 text-left">Position</th>
                    <th class="p-2 text-left">Email</th>
                    <th class="p-2 text-left">Actions</th>
                </tr>
            </thead>

            <tbody>
                @forelse($employees as $employee)
                <tr class="border-t">
                    <td class="p-2">
                        {{ $employee->first_name }} {{ $employee->last_name }}
                    </td>

                    <td class="p-2">{{ $employee->department ?? '-' }}</td>
                    <td class="p-2">
                        @php
                            $roles = $employee->user?->role ?? $employee->position ?? [];

                            if (!is_array($roles)) {
                                $roles = [$roles];
                            }
                        @endphp

                        {{ implode(', ', $roles) }}
                    </td>

                   <td class="p-2">
                        @if($employee->has_account)
                            <div class="flex flex-col">
                                
                                <span class="text-gray-800 font-medium">
                                    {{ $employee->email ?? $employee->user?->email ?? 'No Email' }}
                                </span>

                                <span class="text-xs text-green-600 font-semibold">
                                    ● Active Account
                                </span>

                            </div>
                        @else
                            <div class="flex flex-col">
                                
                                <span class="text-gray-800 font-medium">
                                    {{ $employee->email ?? $employee->user?->email ?? 'No Email' }}
                                </span>

                                <span class="text-xs text-gray-400">
                                    ● No Active Account
                                </span>

                            </div>
                        @endif
                    </td>

                    <td class="p-2 space-x-2">
                        <!-- VIEW / EDIT -->
                        <button
                            onclick="openEditEmployeeModal({{ $employee->id }})"
                            class="text-blue-600 hover:underline">
                            Edit
                        </button>

                        <!-- CREATE ACCOUNT -->
                        @if(!$employee->has_account)
                           <button
                                onclick="openCreateAccountModal(
                                    {{ $employee->id }},
                                    '{{ $employee->first_name }} {{ $employee->last_name }}',
                                    '{{ $employee->email }}'
                                )"
                                class="text-green-600 hover:underline">
                                Create Account
                            </button>
                        @else
                            <span class="text-gray-400 text-sm">Account Created</span>
                        @endif

                        <!-- DELETE -->
                        <form action="{{ route('employees.destroy', $employee) }}" method="POST" class="inline">
                            @csrf
                            @method('DELETE')

                            <button type="button"
                                onclick="openConfirmModal({
                                    title: 'Delete Employee',
                                    message: 'Do you want to proceed?',
                                    confirmText: 'Delete',
                                    onConfirm: () => this.closest('form').submit()
                                })"
                                class="text-red-600 hover:underline">
                                Delete
                            </button>
                        </form>

                    </td>
                </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center p-4 text-gray-400">
                            No employees yet
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
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