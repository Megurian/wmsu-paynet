@extends('layouts.dashboard')

@section('title', 'Organization Details')
@section('page-title', 'Organization Details')

@section('content')
<div class="max-w-4xl mx-auto space-y-4 mt-2">
    <div class="text-left">
        <a href="{{ route('college.local_organizations') }}" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300 transition font-medium">
            Back to Organizations
        </a>
    </div>

    <div class="bg-white shadow-md rounded-xl p-6 flex items-center space-x-6">
        @if($org->logo)
        <img src="{{ asset('storage/' . $org->logo) }}" alt="{{ $org->name }} Logo" class="w-32 h-32 rounded-full object-cover border border-gray-200">
        @else
        <div class="w-32 h-32 rounded-full bg-gray-100 flex items-center justify-center text-gray-400 border border-gray-200">
            No Logo
        </div>
        @endif

        <div class="flex-1 space-y-3">
            <div class="flex justify-between items-center ">
                <h2 class="text-2xl font-bold text-gray-800">{{ $org->name }}</h2>
                <span class="px-3 py-1 text-xs font-medium rounded-full 
                    @if(is_null($org->mother_organization_id)) bg-blue-100 text-blue-700
                    @else bg-purple-100 text-purple-700 @endif">
                    @if(is_null($org->mother_organization_id)) College Organization
                    @else Office @endif
                </span>
            </div>
            <span class="text-purple-700 font-bold ">
                @if(!is_null($org->mother_organization_id))
                {{ $org->motherOrganization->name ?? 'N/A' }}
                @endif
            </span>
            <p class="text-gray-500">
                <span class="font-medium text-gray-700">{{ $org->org_code }}</span>
            </p>
            <div class="flex flex-wrap gap-2 mt-2 items-center">
                <span class="px-3 py-1 text-xs font-medium rounded-full
                    @if($org->status === 'pending') bg-yellow-100 text-yellow-700
                    @elseif($org->status === 'approved') bg-green-100 text-green-700
                    @elseif($org->status === 'rejected') bg-red-100 text-red-700
                    @endif">
                    @if($org->status === 'pending') Pending Approval
                    @elseif($org->status === 'approved' && $org->approved_at)
                    <span>
                        Approved on {{ \Carbon\Carbon::parse($org->approved_at)->format('M d, Y') }}
                    </span>
                    @elseif($org->status === 'rejected') Rejected
                    @endif
                </span>
            </div>
        </div>
    </div>

    <div x-data="{ open: true }" class="bg-white shadow-md rounded-xl w-full">
        <button @click="open = !open" class="w-full flex justify-between items-center px-6 py-4 font-medium text-gray-800 hover:bg-gray-100 rounded-t-xl focus:outline-none">
            <span>Approved Fees ({{ $fees->count() }})</span>
            <svg :class="{ 'rotate-180': open }" class="w-5 h-5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
        </button>
        <div x-show="open" class="px-6 pb-4 space-y-2">
            @if($fees->count())
            @foreach($fees as $fee)
            <div class="p-4 border border-gray-200 rounded-lg">
                <div class="flex justify-between items-center">
                    <span class="font-medium">{{ $fee->fee_name }}</span>
                    <span class="text-sm text-gray-500">{{ \Carbon\Carbon::parse($fee->date)->format('M d, Y') }}</span>
                </div>
                <div class="mt-1 text-gray-600 text-sm">
                    Amount: ₱{{ number_format($fee->amount, 2) }}<br>
                    Requirement: {{ $fee->requirement_level ?? 'N/A' }}<br>
                    Description: {{ $fee->description ?? 'N/A' }}
                </div>
            </div>
            @endforeach
            @else
            <p class="text-gray-400">No approved fees for this organization.</p>
            @endif
        </div>
    </div>

<div
    x-data="{
        showModal: false,
        listOpen: true,

        search: '',
        open: false,
        selectedId: null,
        selectedStudent: null,

        role: '',

        roles: [
            'Mayor',
            'President',
            'Vice Mayor',
            'Vice President',
            'Finance Officer'
        ],

        students: @js($eligibleStudents),
        filtered: [],

        init() {
            this.filtered = this.students;
        },

        filterStudents() {
            const q = this.search.toLowerCase().trim();

            this.filtered = this.students.filter(s => {
                const fullName = `${s.last_name} ${s.first_name}`.toLowerCase();

                return (
                    fullName.includes(q) ||
                    (s.student_id ?? '').toLowerCase().includes(q) ||
                    (s.email ?? '').toLowerCase().includes(q)
                );
            });
        },

        selectStudent(student) {
            this.search = `${student.last_name}, ${student.first_name}`;
            this.selectedId = student.id;
            this.selectedStudent = student;
            this.open = false;
        }
    }"
    class="bg-white shadow-md rounded-xl w-full mt-4"
>

    <!-- HEADER -->
    <div class="flex justify-between items-center px-6 py-4 border-b">
        <h3 class="font-medium text-gray-800">
            Organization Officers ({{ $officers->count() }})
        </h3>

        <button
            @click="showModal = true"
            class="px-3 py-1 bg-indigo-600 text-white text-sm rounded-lg hover:bg-indigo-700 transition"
        >
            + Assign Officer
        </button>
    </div>

    <!-- OFFICERS LIST -->
    <div x-show="listOpen" x-transition class="px-6 py-4 space-y-3">

        @forelse($officers as $officer)
            <div class="p-4 border rounded-lg flex justify-between items-center bg-gray-50">

                <div>
                    <p class="font-semibold text-gray-800">
                        {{ $officer->last_name }}, {{ $officer->first_name }}
                    </p>

                    <p class="text-xs text-gray-500">
                        {{ $officer->email }}
                    </p>

                    <p class="text-xs text-gray-400">
                        ID: {{ $officer->student_id }}
                    </p>
                </div>

                <div class="text-right">
                    <span class="px-3 py-1 text-xs bg-indigo-100 text-indigo-700 rounded-full">
                        {{ $officer->role }}
                    </span>

                    @if($officer->is_active)
                        <p class="text-[10px] text-green-600 mt-1">Active</p>
                    @else
                        <p class="text-[10px] text-gray-400 mt-1">Inactive</p>
                    @endif
                </div>

            </div>
        @empty
            <div class="text-center py-6 text-gray-400">
                No officers assigned yet.
            </div>
        @endforelse

    </div>

    <!-- MODAL -->
<div
    x-show="showModal"
    x-cloak
    class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm px-4"
>
    <div class="bg-white w-full max-w-5xl rounded-2xl shadow-2xl overflow-hidden">

        <!-- HEADER -->
        <div class="px-6 py-4 border-b bg-gray-50 flex justify-between items-center">
            <div>
                <h2 class="text-lg font-bold text-gray-800">Assign Organization Officer</h2>
                <p class="text-sm text-gray-500">
                    Select a student, assign a role, and create officer login credentials.
                </p>
            </div>

            <button @click="showModal = false"
                class="text-gray-500 hover:text-gray-700 text-xl">
                ✕
            </button>
        </div>

        <!-- BODY -->
        <form action="{{ route('college.local_organizations.assign', $org->id) }}" method="POST">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-0">

                <!-- LEFT SIDE -->
                <div class="p-6 border-r space-y-5">

                    <div>
                        <h3 class="font-semibold text-gray-800 mb-1">1. Select Student</h3>
                        <p class="text-sm text-gray-500 mb-3">
                            Search by name, student ID, or email.
                        </p>

                        <input
                            type="text"
                            x-model="search"
                            @focus="open = true"
                            @input="filterStudents"
                            @keydown.escape="open = false"
                            placeholder="Search student..."
                            class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-500 outline-none"
                            autocomplete="off"
                        >

                        <input type="hidden" name="student_id" x-model="selectedId" required>

                        <!-- DROPDOWN -->
                        <div
                            x-show="open && filtered.length"
                            x-transition
                            class="absolute z-50 mt-2 w-[45%] bg-white border rounded-xl shadow-lg max-h-64 overflow-y-auto"
                        >
                            <template x-for="student in filtered" :key="student.id">
                                <div
                                    @mousedown.prevent="selectStudent(student)"
                                    class="px-4 py-3 hover:bg-indigo-50 cursor-pointer border-b last:border-0"
                                >
                                    <div class="font-medium text-gray-800"
                                         x-text="student.last_name + ', ' + student.first_name"></div>

                                    <div class="text-xs text-gray-500"
                                         x-text="student.student_id + ' • ' + student.email"></div>
                                </div>
                            </template>
                        </div>
                    </div>

                    <!-- SELECTED STUDENT CARD -->
                    <template x-if="selectedStudent">
                        <div class="bg-gray-50 border rounded-xl p-4">
                            <p class="font-semibold text-gray-700 mb-2">Selected Student</p>

                            <div class="text-sm space-y-1">
                                <p><span class="text-gray-500">Name:</span>
                                    <span x-text="selectedStudent.last_name + ', ' + selectedStudent.first_name"></span>
                                </p>
                                <p><span class="text-gray-500">ID:</span>
                                    <span x-text="selectedStudent.student_id"></span>
                                </p>
                                <p><span class="text-gray-500">Email:</span>
                                    <span x-text="selectedStudent.email"></span>
                                </p>
                            </div>
                        </div>
                    </template>

                </div>

                <!-- RIGHT SIDE -->
                <div class="p-6 space-y-5">

                    <div>
                        <h3 class="font-semibold text-gray-800 mb-1">2. Assign Role</h3>
                        <p class="text-sm text-gray-500 mb-3">
                            Choose the officer position for this student.
                        </p>

                        <select
                            name="role"
                            x-model="role"
                            class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-500 outline-none"
                            required
                        >
                            <option value="">Select role</option>
                            <template x-for="r in roles" :key="r">
                                <option :value="r" x-text="r"></option>
                            </template>
                        </select>

                        <p class="text-xs text-gray-500 mt-2">
                            Selected:
                            <span class="font-semibold text-indigo-600" x-text="role || 'None'"></span>
                        </p>
                    </div>

                    <!-- LOGIN SECTION -->
                    <template x-if="selectedStudent">

                        <div class="pt-4 border-t space-y-4">

                            <div>
                                <h3 class="font-semibold text-gray-800 mb-1">3. Officer Login Credentials</h3>
                                <p class="text-sm text-gray-500 mb-3">
                                    This account will be used by the officer to access the system.
                                </p>
                            </div>

                            <div>
                                <label class="text-sm font-medium text-gray-700">Login Email</label>
                                <input
                                    type="email"
                                    name="secondary_email"
                                    placeholder="e.g. officer@email.com"
                                    class="w-full border rounded-lg px-3 py-2 mt-1 focus:ring-2 focus:ring-indigo-500"
                                    required
                                >
                            </div>

                            <div>
                                <label class="text-sm font-medium text-gray-700">Password</label>
                                <input
                                    type="password"
                                    name="password"
                                    class="w-full border rounded-lg px-3 py-2 mt-1 focus:ring-2 focus:ring-indigo-500"
                                    required
                                >
                            </div>

                            <div>
                                <label class="text-sm font-medium text-gray-700">Confirm Password</label>
                                <input
                                    type="password"
                                    name="password_confirmation"
                                    class="w-full border rounded-lg px-3 py-2 mt-1 focus:ring-2 focus:ring-indigo-500"
                                    required
                                >
                            </div>

                        </div>

                    </template>

                </div>
            </div>

            <!-- FOOTER -->
            <div class="px-6 py-4 bg-gray-50 border-t flex justify-end gap-3">

                <button
                    type="button"
                    @click="showModal = false"
                    class="px-4 py-2 rounded-lg border hover:bg-gray-100"
                >
                    Cancel
                </button>

                <button
                    type="submit"
                    class="px-5 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 shadow"
                >
                    Assign Officer
                </button>

            </div>

        </form>

    </div>
</div>

</div>

</div>
@endsection



<script>
function studentSearch() {
    return {
        search: '',
        open: false,
        selectedId: null,
        students: @json($eligibleStudents),
        filtered: [],

        init() {
            this.filtered = this.students;
        },

        filterStudents() {
            const q = this.search.toLowerCase().trim();

            this.filtered = this.students.filter(s => {
                const fullName = `${s.last_name} ${s.first_name}`.toLowerCase();

                return (
                    fullName.includes(q) ||
                    (s.student_id ?? '').toLowerCase().includes(q) ||
                    (s.email ?? '').toLowerCase().includes(q)
                );
            });
        },

        selectStudent(student) {
            this.search = `${student.last_name}, ${student.first_name}`;
            this.selectedId = student.id;
            this.open = false;
        }
    }
}
</script>