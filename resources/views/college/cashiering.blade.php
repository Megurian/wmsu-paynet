@extends('layouts.dashboard')

@section('title', 'Cashiering')
@section('page-title', 'Cashiering')

@section('content')
<div x-data="cashieringDropdown()" class="max-w-3xl mx-auto mt-10">
    <label for="student" class="block text-sm font-medium text-gray-700">Select Student</label>
    <input 
        type="text"
        x-model="search"
        @input.debounce.300ms="fetchStudents"
        placeholder="Search by name or ID"
        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
    >
    <ul x-show="students.length > 0" class="border mt-1 rounded-md max-h-60 overflow-auto">
        <template x-for="student in students" :key="student.id">
            <li 
                @click="selectStudent(student)"
                class="p-2 hover:bg-indigo-100 cursor-pointer"
            >
                <span x-text="student.student_id"></span> - <span x-text="student.first_name + ' ' + student.last_name"></span>
            </li>
        </template>
    </ul>

    <template x-if="selectedStudent">
        <div class="mt-4 p-4 border rounded bg-gray-50">
            <h3 class="font-bold text-lg mb-2">Student Details</h3>
            <p><strong>Name:</strong> <span x-text="selectedStudent.first_name + ' ' + selectedStudent.last_name"></span></p>
            <p><strong>ID:</strong> <span x-text="selectedStudent.student_id"></span></p>
            <p><strong>Email:</strong> <span x-text="selectedStudent.email"></span></p>
            <p><strong>Course:</strong> <span x-text="selectedStudent.course"></span></p>
            <p><strong>Year & Section:</strong> <span x-text="selectedStudent.year_level + ' - ' + selectedStudent.section"></span></p>

            <h3 class="font-bold text-lg mt-4 mb-2">Fees</h3>
            <form @submit.prevent="submitPayment">
                <template x-for="fee in fees" :key="fee.id">
                    <div class="flex items-center mb-2">
                        <input type="checkbox" :value="fee.id" x-model="selectedFees" class="mr-2">
                        <span x-text="fee.fee_name + ' (' + fee.amount + ')'"></span>
                    </div>
                </template>
                <button type="submit" class="mt-4 px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700">Collect Payment</button>
            </form>
        </div>
    </template>
</div>

<script>
function cashieringDropdown() {
    return {
        search: '',
        students: [],
        selectedStudent: null,
        fees: [],
        selectedFees: [],
        fetchStudents() {
            if(this.search.length < 2) { this.students = []; return; }
            fetch(`/admin/cashiering/search?q=${this.search}`)
                .then(res => res.json())
                .then(data => this.students = data);
        },
        selectStudent(student) {
            this.selectedStudent = null;
            fetch(`/admin/cashiering/student/${student.id}`)
                .then(res => res.json())
                .then(data => {
                    this.selectedStudent = data.student;
                    this.fees = data.fees;
                    this.selectedFees = [];
                });
            this.students = [];
            this.search = student.first_name + ' ' + student.last_name;
        },
        submitPayment() {
            if(this.selectedFees.length === 0) {
                alert('Select at least one fee to collect payment.');
                return;
            }
            fetch('/admin/cashiering/collect-payment', {
                method: 'POST',
                headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}'},
                body: JSON.stringify({
                    student_id: this.selectedStudent.id,
                    fee_ids: this.selectedFees
                })
            })
            .then(res => res.json())
            .then(data => {
                alert(data.message || 'Payment collected successfully.');
                this.selectedStudent = null;
                this.fees = [];
                this.selectedFees = [];
                this.search = '';
            });
        }
    }
}
</script>
@endsection