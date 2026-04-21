<div class="bg-white p-6 rounded shadow space-y-6">

    <!-- Header -->
    <div class="flex justify-between items-center">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Staff Management</h2>
            <p class="text-sm text-gray-500">Master list of employees (Dean/Admin)</p>
        </div>

        <div class="flex gap-2">
            <button onclick="document.getElementById('importEmployeeModal').classList.remove('hidden')"
                class="bg-gray-700 text-white px-4 py-2 rounded">
                Import
            </button>

            <button onclick="document.getElementById('addEmployeeModal').classList.remove('hidden')"
                class="bg-red-800 text-white px-4 py-2 rounded">
                Add Employee
            </button>
        </div>
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
                    {{ $employee->first_name }} {{ $employee->middle_name ? $employee->middle_name . ' ' : '' }} {{ $employee->last_name }} {{ $employee->suffix ? ', ' . $employee->suffix : '' }}

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

                <div class="text-sm text-gray-500">
                    @if($department === 'faculty_staff')
                        Faculty Staff
                    @else
                        Department of {{ $department }}
                    @endif
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
                        onclick="openEditEmployeeModal({{ json_encode([
                            'id' => $employee->id,
                            'first_name' => $employee->first_name,
                            'last_name' => $employee->last_name,
                            'middle_name' => $employee->middle_name,
                            'department' => $employee->department,
                            'email' => $employee->email ?? $employee->user?->email
                        ]) }})"
                        class="px-3 py-1 text-xs bg-blue-50 text-blue-600 rounded hover:bg-blue-100">
                        Edit
                    </button>

                    @if(!$employee->has_account)
                        <button
                            onclick='openCreateAccountModal(
                                @json($employee->id),
                                @json($employee->first_name . " " . $employee->last_name),
                                @json($employee->email),
                                @json($employee->department)
                            )'
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

<div id="addEmployeeModal" class="hidden fixed inset-0 bg-black/50 flex items-center justify-center p-4 z-50">
    <div class="bg-white w-full max-w-lg rounded-2xl shadow-xl overflow-hidden">

        <!-- HEADER -->
        <div class="px-6 py-4 border-b bg-gray-50">
            <h2 class="text-xl font-bold text-gray-800">Add New Employee</h2>
            <p class="text-sm text-gray-500">
                Fill in the required details to register a new staff member.
            </p>
        </div>

        <!-- FORM -->
        <form method="POST" action="{{ route('employees.store') }}" class="p-6 space-y-5">
            @csrf

            <!-- NAME GRID -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">

                <div>
                    <label class="text-sm font-medium text-gray-700">First Name</label>
                    <input name="first_name" required
                        placeholder="e.g. Juan"
                        class="mt-1 border rounded-lg p-2 w-full focus:ring-2 focus:ring-red-200 focus:border-red-400">
                </div>

                <div>
                    <label class="text-sm font-medium text-gray-700">Last Name</label>
                    <input name="last_name" required
                        placeholder="e.g. Dela Cruz"
                        class="mt-1 border rounded-lg p-2 w-full focus:ring-2 focus:ring-red-200 focus:border-red-400">
                </div>

            </div>

            <!-- MIDDLE NAME /SUffix-->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="text-sm font-medium text-gray-700">Middle Name (Optional)</label>
                    <input name="middle_name"
                        placeholder="Optional"
                        class="mt-1 border rounded-lg p-2 w-full focus:ring-2 focus:ring-red-200 focus:border-red-400">
                </div>
                <div>
                <label class="text-sm font-medium text-gray-700">Suffix (Optional)</label>
                <select name="suffix"
                     class="mt-1 border rounded-lg p-2 w-full focus:ring-2 focus:ring-red-200 focus:border-red-400">

                    <option value="">Suffix</option>
                    <option value="Jr.">Jr.</option>
                    <option value="Sr.">Sr.</option>
                    <option value="II">II</option>
                    <option value="III">III</option>
                    <option value="IV">IV</option>
                </select>
                </div>
            </div>

            <!-- EMAIL -->
            <div>
                <label class="text-sm font-medium text-gray-700">Email Address</label>
                <input name="email" type="email" required
                    placeholder="employee@school.edu"
                    class="mt-1 border rounded-lg p-2 w-full focus:ring-2 focus:ring-red-200 focus:border-red-400">

                <p class="text-xs text-gray-400 mt-1">
                    This email will be used for login and notifications.
                </p>
            </div>

            <!-- DEPARTMENT -->
            <div>
                <label class="text-sm font-medium text-gray-700">Department</label>

                <select name="department" id="departmentSelect"
                    class="mt-1 border rounded-lg p-2 w-full focus:ring-2 focus:ring-red-200 focus:border-red-400"
                    onchange="toggleOtherDepartment(this)">

                    <option value="">Select Department</option>
                    <option value="faculty_staff">Faculty Staff</option>

                    @foreach($courses as $course)
                        <option value="{{ $course->name }}">{{ $course->name }}</option>
                    @endforeach

                    <option value="other">Other</option>
                </select>

                <p class="text-xs text-gray-400 mt-1">
                    Select the department or course the employee belongs to.
                </p>
            </div>

            <!-- OTHER DEPARTMENT -->
            <div>
                <input type="text"
                    name="other_department"
                    id="otherDepartmentInput"
                    placeholder="Enter custom department"
                    class="border rounded-lg p-2 w-full hidden focus:ring-2 focus:ring-red-200 focus:border-red-400">
            </div>

            <!-- ACTIONS -->
            <div class="flex justify-end gap-2 pt-2 border-t">

                <button type="button"
                    onclick="document.getElementById('addEmployeeModal').classList.add('hidden')"
                    class="px-4 py-2 rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-700">
                    Cancel
                </button>

                <button class="px-5 py-2 rounded-lg bg-red-800 hover:bg-red-700 text-white font-medium shadow">
                    Save Employee
                </button>

            </div>

        </form>

    </div>
</div>

<div id="editEmployeeModal"
    class="hidden fixed inset-0 bg-black/50 flex items-center justify-center p-4 z-50">

    <div class="bg-white w-full max-w-lg rounded-2xl shadow-xl overflow-hidden">

        <!-- HEADER -->
        <div class="px-6 py-4 border-b bg-gray-50">
            <h2 class="text-xl font-bold text-gray-800">Edit Employee</h2>
            <p class="text-sm text-gray-500">
                Update employee information and department details.
            </p>
        </div>

        <!-- FORM -->
        <form id="editEmployeeForm" method="POST" class="p-6 space-y-5">
            @csrf
            @method('PUT')

            <!-- NAME GRID -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">

                <div>
                    <label class="text-sm font-medium text-gray-700">First Name</label>
                    <input name="first_name" id="edit_first_name"
                        class="mt-1 border rounded-lg p-2 w-full focus:ring-2 focus:ring-blue-200 focus:border-blue-400"
                        placeholder="First name">
                </div>

                <div>
                    <label class="text-sm font-medium text-gray-700">Last Name</label>
                    <input name="last_name" id="edit_last_name"
                        class="mt-1 border rounded-lg p-2 w-full focus:ring-2 focus:ring-blue-200 focus:border-blue-400"
                        placeholder="Last name">
                </div>

            </div>

            <!-- MIDDLE + SUFFIX -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">

                <div>
                    <label class="text-sm font-medium text-gray-700">Middle Name</label>
                    <input name="middle_name" id="edit_middle_name"
                        class="mt-1 border rounded-lg p-2 w-full focus:ring-2 focus:ring-blue-200 focus:border-blue-400"
                        placeholder="Optional">
                </div>

                <div>
                    <label class="text-sm font-medium text-gray-700">Suffix</label>
                    <select name="suffix" id="edit_suffix"
                        class="mt-1 border rounded-lg p-2 w-full focus:ring-2 focus:ring-blue-200 focus:border-blue-400">

                        <option value="">None</option>
                        <option value="Jr.">Jr.</option>
                        <option value="Sr.">Sr.</option>
                        <option value="II">II</option>
                        <option value="III">III</option>
                        <option value="IV">IV</option>

                    </select>
                </div>

            </div>

            <!-- EMAIL -->
            <div>
                <label class="text-sm font-medium text-gray-700">Email Address</label>
                <input name="email" id="edit_email" type="email"
                    class="mt-1 border rounded-lg p-2 w-full focus:ring-2 focus:ring-blue-200 focus:border-blue-400"
                    placeholder="employee@school.edu">

                <p class="text-xs text-gray-400 mt-1">
                    This email is used for login and notifications.
                </p>
            </div>

            <!-- DEPARTMENT -->
            <div>
                <label class="text-sm font-medium text-gray-700">Department</label>

                <select name="department" id="edit_department"
                    class="mt-1 border rounded-lg p-2 w-full focus:ring-2 focus:ring-blue-200 focus:border-blue-400"
                    onchange="toggleEditOtherDepartment(this)">

                    <option value="">Select Department</option>
                    <option value="faculty_staff">Faculty Staff</option>

                    @foreach($courses as $course)
                        <option value="{{ $course->name }}">{{ $course->name }}</option>
                    @endforeach

                    <option value="other">Other</option>

                </select>

                <p class="text-xs text-gray-400 mt-1">
                    Update the employee’s assigned department or course.
                </p>
            </div>

            <!-- OTHER DEPARTMENT -->
            <div>
                <input type="text"
                    name="other_department"
                    id="edit_other_department"
                    placeholder="Enter custom department"
                    class="border rounded-lg p-2 w-full hidden focus:ring-2 focus:ring-blue-200 focus:border-blue-400">
            </div>

            <!-- ACTIONS -->
            <div class="flex justify-end gap-2 pt-2 border-t">

                <button type="button"
                    onclick="document.getElementById('editEmployeeModal').classList.add('hidden')"
                    class="px-4 py-2 rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-700">
                    Cancel
                </button>

                <button class="px-5 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white font-medium shadow">
                    Update Employee
                </button>

            </div>

        </form>

    </div>
</div>

<div id="importEmployeeModal"
    class="hidden fixed inset-0 bg-black/60 flex items-center justify-center p-6 z-50">

    <div class="bg-white w-full max-w-5xl rounded-2xl shadow-2xl overflow-hidden
                max-h-[98vh] flex flex-col">

        <div class="px-8 py-6 border-b bg-gradient-to-r from-gray-50 to-white">
            <h2 class="text-2xl font-bold text-gray-800">Import Employees</h2>
            <p class="text-sm text-gray-500 mt-1">
                Upload a filled Excel file to bulk add employees into the system.
            </p>
        </div>

        <div class="flex-1 overflow-y-auto">

            <div class="grid grid-cols-1 md:grid-cols-2">

                <div class="p-8 border-r bg-gray-50 space-y-6">

                    <div>
                        <h3 class="text-sm font-semibold text-gray-700 mb-2"> Instructions</h3>
                        <ul class="text-sm text-gray-600 space-y-2 list-disc pl-5">
                            <li>Download the official template first</li>
                            <li>Do NOT change column headers</li>
                            <li>Email must be unique per employee</li>
                            <li>Department can be faculty, course, or custom</li>
                        </ul>
                    </div>

                    <div>
                        <h3 class="text-sm font-semibold text-gray-700 mb-2"> Notes</h3>
                        <div class="text-sm text-gray-600 space-y-2">
                            <p>• Duplicate emails will be updated</p>
                            <p>• Missing fields will be skipped</p>
                            <p>• Names are auto-converted to UPPERCASE</p>
                        </div>
                    </div>

                    <div class="bg-white border rounded-xl p-4">
                        <p class="text-xs text-gray-500">Template Format:</p>
                        <p class="text-xs font-mono text-gray-700 mt-1 break-words">
                            first_name | middle_name | last_name | suffix | email | department
                        </p>
                    </div>

                </div>

                <div class="p-8 space-y-6">

                    <div class="space-y-2">
                        <h3 class="text-sm font-semibold text-gray-700"> Download Template</h3>

                        <a href="{{ route('employees.template') }}"
                            class="flex items-center justify-center gap-2 w-full bg-green-600 hover:bg-green-700 text-white py-3 rounded-xl font-medium shadow">
                             Download Excel Template
                        </a>

                        <p class="text-xs text-gray-500 text-center">
                            Always use the latest template version
                        </p>
                    </div>

                    <div class="space-y-3">
                        <h3 class="text-sm font-semibold text-gray-700">Upload File</h3>

                        <form method="POST" action="{{ route('employees.import') }}" enctype="multipart/form-data"
                            class="space-y-4">
                            @csrf

                            <label class="flex flex-col items-center justify-center w-full border-2 border-dashed border-gray-300 rounded-xl p-6 cursor-pointer hover:border-gray-400 transition">
                                <span class="text-sm text-gray-600">Click to upload Excel file</span>
                                <span class="text-xs text-gray-400 mt-1">.xlsx or .csv only</span>

                                <input type="file" name="file" id="employeeFileInput" required class="hidden">

                                <p id="filePreview" class="text-sm text-green-600 mt-3 font-medium"></p>

                            </label>

                            <button
                                class="w-full bg-red-800 hover:bg-red-700 text-white py-3 rounded-xl font-medium shadow">
                                Import Employees
                            </button>

                        </form>
                    </div>

                </div>

            </div>

        </div>

        <div class="flex justify-end px-8 py-4 border-t bg-gray-50">
            <button
                onclick="document.getElementById('importEmployeeModal').classList.add('hidden')"
                class="px-5 py-2 bg-gray-200 hover:bg-gray-300 rounded-lg text-gray-700">
                Close
            </button>
        </div>

    </div>
</div>

<div id="createAccountModal"
    class="hidden fixed inset-0 bg-black/50 flex items-center justify-center p-4 z-50">

    <div class="bg-white w-full max-w-4xl rounded-2xl shadow-xl overflow-hidden">

        <div class="px-6 py-5 border-b bg-gray-50">
            <h2 class="text-xl font-bold text-gray-800">Create Employee Account</h2>
            <p class="text-sm text-gray-500">
                Set login credentials and assign roles for system access.
            </p>
        </div>

        <form method="POST" id="createAccountForm" class="p-6 space-y-6">
            @csrf

            <!-- EMPLOYEE INFO -->
            <div class="bg-gray-50 border rounded-xl p-4">
                <p class="text-sm text-gray-500">Creating account for:</p>
                <p class="font-semibold text-gray-800" id="accountEmployeeName"></p>
            </div>

            <!-- GRID LAYOUT -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                <!-- LEFT SIDE -->
                <div class="space-y-5">

                    <!-- EMAIL -->
                    <div>
                        <label class="text-sm font-medium text-gray-700">Email Address</label>
                        <input id="accountEmail" name="email" type="email" required
                            placeholder="employee@school.edu"
                            class="mt-1 w-full border rounded-lg p-2 focus:ring-2 focus:ring-green-200 focus:border-green-400">

                        <p class="text-xs text-gray-400 mt-1">
                            This will be used for login and notifications.
                        </p>
                    </div>

                    <!-- PASSWORD -->
                    <div>
                        <label class="text-sm font-medium text-gray-700">Password</label>
                        <input name="password" type="password" required
                            placeholder="Enter secure password"
                            class="mt-1 w-full border rounded-lg p-2 focus:ring-2 focus:ring-green-200 focus:border-green-400">

                        <p class="text-xs text-gray-400 mt-1">
                            Must be at least 6 characters.
                        </p>
                    </div>

                </div>

                <!-- RIGHT SIDE -->
                <div class="space-y-5">

                    <!-- ROLES -->
                    <div>
                        <label class="text-sm font-medium text-gray-700">Assign Roles</label>
                        <p class="text-xs text-gray-400 mb-2">
                            Select one or more roles for this employee.
                        </p>

                        <div class="grid grid-cols-2 gap-2">

                            <label class="flex items-center gap-2 border rounded-lg p-2 hover:bg-gray-50">
                                <input type="checkbox" name="position[]" value="assessor">
                                <span class="text-sm">Assessor</span>
                            </label>

                            <label class="flex items-center gap-2 border rounded-lg p-2 hover:bg-gray-50">
                                <input type="checkbox" name="position[]" value="student_coordinator">
                                <span class="text-sm">Student Coordinator</span>
                            </label>

                            <label class="flex items-center gap-2 border rounded-lg p-2 hover:bg-gray-50">
                                <input type="checkbox" name="position[]" value="adviser" id="adviserCheckbox">
                                <span class="text-sm">Adviser</span>
                            </label>

                            <label class="flex items-center gap-2 border rounded-lg p-2 hover:bg-gray-50">
                                <input type="checkbox" name="position[]" value="treasurer">
                                <span class="text-sm">Treasurer</span>
                            </label>

                        </div>
                    </div>

                    <!-- COURSE ASSIGNMENT -->
                    <div id="courseDropdown" class="hidden">
                        <label class="text-sm font-medium text-gray-700">
                            Course Assignment
                        </label>

                        <p class="text-xs text-gray-400 mb-2">
                            Required only if role includes Adviser.
                        </p>

                        <select name="course_id" id="courseSelect"
                            class="w-full border rounded-lg p-2 focus:ring-2 focus:ring-green-200 focus:border-green-400">
                            <option value="">Select Course</option>
                            @foreach($courses as $course)
                                <option value="{{ $course->id }}">{{ $course->name }}</option>
                            @endforeach
                        </select>

                        @error('course_id')
                            <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                </div>
            </div>

            <!-- ACTIONS -->
            <div class="flex justify-end gap-2 pt-4 border-t">

                <button type="button"
                    onclick="document.getElementById('createAccountModal').classList.add('hidden')"
                    class="px-4 py-2 rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-700">
                    Cancel
                </button>

                <button class="px-6 py-2 rounded-lg bg-green-600 hover:bg-green-700 text-white font-medium shadow">
                    Create Account
                </button>

            </div>

        </form>

    </div>
</div>


<script>
    const courseMap = @json(
        $courses->pluck('id', 'name')
    );
</script>
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

            const modal = document.getElementById('createAccountModal');
            const department = modal.dataset.department;

            if (courseMap[department]) {
                courseSelect.value = courseMap[department];
            } else {
                courseSelect.value = '';
            }

        } else {
            courseDropdown.classList.add('hidden');
            courseSelect.value = '';
        }
    }

    adviserCheckbox.addEventListener('change', toggleCourseField);

});

document.addEventListener('DOMContentLoaded', function () {

    document.querySelectorAll('form').forEach(form => {
        form.addEventListener('keydown', function (e) {

            if (e.key === 'Enter') {

                const tag = e.target.tagName.toLowerCase();
                const type = e.target.type;

                if (tag === 'textarea') return;

                e.preventDefault();

                const focusable = Array.from(
                    form.querySelectorAll('input, select, textarea, button')
                ).filter(el =>
                    !el.disabled &&
                    el.type !== 'hidden' &&
                    el.offsetParent !== null
                );

                const index = focusable.indexOf(e.target);

                if (index > -1 && index < focusable.length - 1) {
                    focusable[index + 1].focus();
                }
            }

        });
    });

});

document.addEventListener('DOMContentLoaded', function () {

    const fileInput = document.getElementById('employeeFileInput');
    const filePreview = document.getElementById('filePreview');

    fileInput.addEventListener('change', function () {

        if (fileInput.files.length > 0) {
            filePreview.textContent = "Selected file: " + fileInput.files[0].name;
        } else {
            filePreview.textContent = "";
        }

    });

});

</script>
