@extends('layouts.dashboard')

@section('title', 'Validate Students')
@section('page-title', 'Validate Students')

@section('content')
<div class="flex flex-col md:flex-row items-center justify-between mb-6 gap-4">
    <h2 class="text-2xl font-bold text-gray-800">Validate Students</h2>
    {{-- <a href="{{ route('college.students') }}" 
       class="px-4 py-2 bg-red-700 text-white rounded-lg shadow hover:bg-red-600 transition">
        &larr; Back
    </a> --}}
</div>

<div class="bg-white shadow rounded-lg p-4 mb-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
    <form method="GET" class="flex flex-wrap gap-3 flex-1 items-center">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by name or Student ID" class="flex-1 min-w-[150px] px-3 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-400 focus:outline-none" >
        <select name="course" class="rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition" onchange="this.form.submit()">
            <option value="">All Courses</option>
            @foreach($courses as $course)
                <option value="{{ $course->id }}" @selected(request('course') == $course->id)>{{ $course->name }}</option>
            @endforeach
        </select>

        <select name="year" class="rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition" onchange="this.form.submit()">
            <option value="">All Years</option>
            @foreach($years as $year)
                <option value="{{ $year->id }}" @selected(request('year') == $year->id)>{{ $year->name }}</option>
            @endforeach
        </select>

        <select name="section" class="rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition" onchange="this.form.submit()">
            <option value="">All Sections</option>
            @foreach($sections as $section)
                <option value="{{ $section->id }}" @selected(request('section') == $section->id)>{{ $section->name }}</option>
            @endforeach
        </select>
    </form>
    <div class="flex justify-end mb-4">
       <button onclick="document.getElementById('importModal').classList.remove('hidden')" 
            class="px-4 py-2 bg-blue-600 text-white rounded-lg shadow hover:bg-blue-500 transition">
            Import Student List
        </button>
    </div>

</div>


<div id="importModal" class="fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center hidden z-50">
    <div class="bg-white rounded-xl shadow-lg w-full max-w-md p-6 relative">
        <h3 class="text-lg font-semibold mb-4">Import Student List</h3>
        <p class="text-sm text-gray-700 mb-4">
            Download the template, fill in student details, and upload. Students remain unvalidated until manual validation.
        </p>

       <a href="{{ route('college.students.import.template') }}"
        class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-500 transition">
            📥 Download Import Template
        </a>


        <form action="{{ route('college.students.import') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <input type="file" name="student_file" accept=".csv, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/vnd.ms-excel" class="mb-4">
            <div class="flex justify-end gap-2">
                <button type="button" onclick="document.getElementById('importModal').classList.add('hidden')" class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-500">Upload</button>
            </div>
        </form>
    </div>
</div>


@if(session('success'))
<div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-800 rounded">
    {{ session('success') }}
</div>
@endif

<form method="POST" action="{{ route('college.students.validate.bulk') }}" x-data="studentSelection()" x-init="init()" class="space-y-4">
    @csrf

    <div class="flex flex-col md:flex-row items-center justify-between mt-4 gap-2">
        <div x-show="selected.length > 0" x-transition class="transition duration-200">
            <button type="submit" class="px-4 py-2 bg-red-800 text-white rounded-md hover:bg-red-700 shadow transition">
                Validate Selected Students
            </button>
        </div>

        <div class="mt-2 md:mt-0">
            {{ $students->links() }}
        </div>
    </div>
   

    <div class="bg-white shadow-sm border border-gray-200 rounded-xl overflow-hidden">
        <table class="min-w-full text-sm text-gray-80000">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr class="text-left text-[11px] font-semibold uppercase tracking-wide text-gray-600">
                   <th class="px-3 py-2">
                        <input type="checkbox" 
                            @click="toggleAll($event)" 
                            class="w-4 h-4 border-gray-400 rounded-sm focus:ring-2 focus:ring-blue-400 cursor-pointer">
                    </th>
                    <th class="px-5 py-3 cursor-pointer select-none">Student ID</th>
                    <th class="px-5 py-3 cursor-pointer select-none">Name</th>
                    <th class="px-5 py-3 cursor-pointer select-none">Last Semester Info</th>
                    <th class="px-5 py-3 cursor-pointer select-none">Course</th>
                    <th class="px-5 py-3 cursor-pointer select-none">Year</th>
                    <th class="px-5 py-3 cursor-pointer select-none">Section</th>
                    <th class="px-5 py-3 cursor-pointer select-none">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($students as $student)
                @php
                    $enrollment = $student->enrollments->first(); 
                    $validated = $enrollment ? true : false;
                    $prev = $student->lastEnrollment; 
                @endphp
                <tr class="{{ $validated ? 'bg-green-50' : 'hover:bg-gray-50' }}">
                    <td class="px-3 py-2">
                        @if(!$validated)
                        <input type="checkbox" name="selected_students[]"  class="w-4 h-4 border-gray-400 rounded-sm focus:ring-2 focus:ring-blue-400 cursor-pointer" value="{{ $student->id }}" @click="toggleOne($event, '{{ $student->id }}')">
                        @endif
                    </td>
                    <td class="px-3 py-2 font-medium">{{ $student->student_id }}</td>
                    <td class="px-3 py-2 font-medium">{{ strtoupper($student->last_name) }}, {{ strtoupper($student->first_name) }} {{ strtoupper($student->middle_name ?? '') }} {{ $student->suffix ?? '' }}</td>
                    <td class="px-3 py-2 text-sm text-gray-700">
                        @if($prev)
                            <div class="font-semibold">{{ $prev->course->name ?? '—' }}</div>
                            <div>{{ $prev->yearLevel->name ?? '—' }} {{ $prev->section->name ?? '—' }}</div>
                        @else
                            <span class="italic text-gray-400">No previous record</span>
                        @endif
                    </td>
                    <td class="px-3 py-2">
                        <select name="course_id[{{ $student->id }}]" class="rounded-lg border border-gray-300 px-6 py-2.5 text-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition" required {{ $validated ? 'disabled' : '' }}>
                            @foreach($courses as $course)
                                <option value="{{ $course->id }}"
                                    @if($validated && $course->id == $enrollment->course_id) selected
                                    @elseif(!$validated && $prev && $course->id == $prev->course_id) selected
                                    @endif
                                >{{ $course->name }}</option>
                            @endforeach
                        </select>
                    </td>
                    <td class="px-3 py-2">
                        <select name="year_level_id[{{ $student->id }}]" class="rounded-lg border border-gray-300 px-6 py-2.5 text-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition" required {{ $validated ? 'disabled' : '' }}>
                            @foreach($years as $year)
                                <option value="{{ $year->id }}"
                                    @if($validated && $year->id == $enrollment->year_level_id) selected
                                    @elseif(!$validated && $prev && $year->id == $prev->year_level_id) selected
                                    @endif
                                >{{ $year->name }}</option>
                            @endforeach
                        </select>
                    </td>
                    <td class="px-3 py-2">
                        <select name="section_id[{{ $student->id }}]" class="rounded-lg border border-gray-300 px-6 py-2.5 text-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition" required {{ $validated ? 'disabled' : '' }}>
                            @foreach($sections as $section)
                                <option value="{{ $section->id }}"
                                    @if($validated && $section->id == $enrollment->section_id) selected
                                    @elseif(!$validated && $prev && $section->id == $prev->section_id) selected
                                    @endif
                                >{{ $section->name }}</option>
                            @endforeach
                        </select>
                    </td>
                    <td class="px-3 py-2">
                       @if(!$validated)
                            <button 
                                formaction="{{ route('college.students.validate.store', $student->id) }}" 
                                formmethod="POST" 
                                class="px-3 py-1 bg-green-600 text-white rounded hover:bg-green-500 transition"
                                onclick="return confirm('Are you sure you want to validate this student?');"
                            >
                                Validate
                            </button>
                            @else
                            <span class="text-green-800 font-semibold">Validated</span>
                            @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center text-gray-500 py-6 italic">
                        No students found.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</form>


<script>
function studentSelection() {
    return {
        selected: [],
        submitting: false,
        toggleAll(event) {
            const checked = event.target.checked;
            const checkboxes = document.querySelectorAll('input[name="selected_students[]"]');
            checkboxes.forEach(cb => {
                cb.checked = checked;
                if (checked && !this.selected.includes(cb.value)) this.selected.push(cb.value);
                if (!checked) this.selected = this.selected.filter(id => id != cb.value);
            });
        },
        toggleOne(event, studentId) {
            if (event.target.checked) this.selected.push(studentId);
            else this.selected = this.selected.filter(id => id != studentId);
        },
        init() {
            
            window.addEventListener('beforeunload', (e) => {
                if (!this.submitting && this.selected.length > 0) {
                    e.preventDefault();
                    e.returnValue = "You have selected students that are not yet validated. Please validate them before leaving the page.";
                }
            });

            const form = this.$el;

            
            form.addEventListener('submit', (e) => {
                if (!this.submitting) {
                    if (this.selected.length > 0) {
                        const confirmed = confirm(`Are you sure you want to validate ${this.selected.length} student(s)?`);
                        if (!confirmed) {
                            e.preventDefault(); 
                            return;
                        }
                    }
                    this.submitting = true;
                }
            });
        }
    }
}
</script>

@endsection
