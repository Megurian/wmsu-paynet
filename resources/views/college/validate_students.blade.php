@extends('layouts.dashboard')

@section('title', 'Validate Students')
@section('page-title', 'Validate Students')

@section('content')
<h2 class="text-2xl font-bold mb-4">Validate Students</h2>
<div>
    <a href="{{ route('college.students') }}" 
       class="inline-block mb-2 px-4 py-2 bg-red-800 text-white rounded-lg shadow hover:bg-red-700 transition">
        &larr; Back
    </a>
</div>

{{-- FILTER FORM (GET) --}}
<form method="GET" class="mb-4">
    <div class="grid grid-cols-1 sm:grid-cols-4 gap-3">
        <input
            type="text"
            name="search"
            value="{{ request('search') }}"
            placeholder="Search name or Student ID"
            class="rounded border px-3 py-2 text-sm"
        >

        <select name="course" class="rounded border px-3 py-2 text-sm" onchange="this.form.submit()">
            <option value="">All Courses</option>
            @foreach($courses as $course)
                <option value="{{ $course->id }}" @selected(request('course') == $course->id)>
                    {{ $course->name }}
                </option>
            @endforeach
        </select>

        <select name="year" class="rounded border px-3 py-2 text-sm" onchange="this.form.submit()">
            <option value="">All Years</option>
            @foreach($years as $year)
                <option value="{{ $year->id }}" @selected(request('year') == $year->id)>
                    {{ $year->name }}
                </option>
            @endforeach
        </select>

        <select name="section" class="rounded border px-3 py-2 text-sm" onchange="this.form.submit()">
            <option value="">All Sections</option>
            @foreach($sections as $section)
                <option value="{{ $section->id }}" @selected(request('section') == $section->id)>
                    {{ $section->name }}
                </option>
            @endforeach
        </select>
    </div>
</form>

<form method="POST" action="{{ route('college.students.validate.bulk') }}" x-data="studentSelection()" x-init="init()">
    @csrf
    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-500 mb-4">
        Validate Selected
    </button>
    <table class="min-w-full text-sm text-gray-800">
        <thead class="bg-gray-50">
            <tr class="uppercase text-xs font-semibold text-gray-600">
                <th>
                    <input type="checkbox" @click="toggleAll($event)"> 
                </th>
                <th>Student ID</th>
                <th>Name</th>
                <th class="text-left">Last Semester Info</th>
                <th>Course</th>
                <th>Year</th>
                <th>Section</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            @foreach($students as $student)
            @php
                $enrollment = $student->enrollments->first(); 
                $validated = $enrollment ? true : false;
                $prev = $student->lastEnrollment; 
            @endphp
            <tr class="{{ $validated ? 'bg-green-100' : '' }}">
                <td>
                    @if(!$validated)
                    <input type="checkbox" name="selected_students[]" 
                           value="{{ $student->id }}" 
                           @click="toggleOne($event, '{{ $student->id }}')">
                    @endif
                </td>

                <td>{{ $student->student_id }}</td>
                <td>{{ strtoupper($student->last_name) }}, {{ strtoupper($student->first_name) }} {{ strtoupper($student->middle_name ?? '') }} {{ $student->suffix ?? '' }}</td>
                <td class="text-xs text-gray-700">
                    @if($prev)
                        <div class="font-semibold">
                            {{ $prev->course->name ?? '—' }}
                        </div>
                        <div>
                            {{ $prev->yearLevel->name ?? '—' }} {{ $prev->section->name ?? '—' }}
                        </div>
                    @else
                        <span class="italic text-gray-400">No previous record</span>
                    @endif
                </td>
                <td>
                    <select name="course_id[{{ $student->id }}]" required {{ $validated ? 'disabled' : '' }}>
                        @foreach($courses as $course)
                        <option value="{{ $course->id }}"
                            @if($validated && $course->id == $enrollment->course_id)
                                selected
                            @elseif(!$validated && $prev && $course->id == $prev->course_id)
                                selected
                            @endif
                        >
                            {{ $course->name }}
                        </option>
                        @endforeach
                    </select>
                </td>

                <td>
                    <select name="year_level_id[{{ $student->id }}]" required {{ $validated ? 'disabled' : '' }}>
                        @foreach($years as $year)
                        <option value="{{ $year->id }}"
                            @if($validated && $year->id == $enrollment->year_level_id)
                                selected
                            @elseif(!$validated && $prev && $year->id == $prev->year_level_id)
                                selected
                            @endif
                        >
                            {{ $year->name }}
                        </option>
                        @endforeach
                    </select>
                </td>

                <td>
                    <select name="section_id[{{ $student->id }}]" required {{ $validated ? 'disabled' : '' }}>
                        @foreach($sections as $section)
                        <option value="{{ $section->id }}"
                            @if($validated && $section->id == $enrollment->section_id)
                                selected
                            @elseif(!$validated && $prev && $section->id == $prev->section_id)
                                selected
                            @endif
                        >
                            {{ $section->name }}
                        </option>
                        @endforeach
                    </select>
                </td>

                <td>
                    @if(!$validated)
                    <button formaction="{{ route('college.students.validate.store', $student->id) }}"
                            formmethod="POST"
                            class="px-3 py-1 bg-green-600 text-white rounded hover:bg-green-500">
                        Validate
                    </button>
                    @else
                    <span class="text-green-800 font-semibold">Validated</span>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    <div class="mt-4"> {{ $students->links() }} </div> 
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
            form.addEventListener('submit', () => {
                this.submitting = true;
            });
        }
    }
}
</script>

@endsection
