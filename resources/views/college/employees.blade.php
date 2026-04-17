
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
                    <th class="p-2 text-left">Account</th>
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
                    <td class="p-2">{{ $employee->position ?? '-' }}</td>

                    <td class="p-2">
                        @if($employee->has_account)
                            <span class="text-green-600">Has Account</span>
                        @else
                            <span class="text-gray-400">No Account</span>
                        @endif
                    </td>

                    <td class="p-2">
                        <form action="{{ route('employees.destroy', $employee) }}" method="POST">
                            @csrf
                            @method('DELETE')

                            <button type="button"
                                onclick="openConfirmModal({
                                    title: 'Delete Employee',
                                    message: 'Do you want to proceed?',
                                    confirmText: 'Delete',
                                    onConfirm: () => this.closest('form').submit()
                                })"
                                class="text-red-600">
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

            <!-- POSITION DROPDOWN -->
            <label class="text-sm text-gray-600">Position</label>
            <select name="position" class="border p-2 w-full mb-4">
                <option value="">Select Position</option>
                <option value="faculty">Faculty</option>
                <option value="staff">Staff</option>
            </select>

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
</script>