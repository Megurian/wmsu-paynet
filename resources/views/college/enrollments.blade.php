@if($tab === 'enrollments')

<div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-5 gap-4 mb-4">
    <div class="bg-blue-50 text-blue-700 rounded-lg p-4 flex flex-col items-center justify-center shadow-sm">
        <span class="text-xs font-semibold uppercase">Total Students</span>
        <span class="text-xl font-bold">{{ $students->count() }}</span>
    </div>

    <div class="bg-indigo-100 text-indigo-700 rounded-lg p-4 flex flex-col items-center justify-center shadow-sm">
        <span class="text-xs font-semibold uppercase">Assessment Completed</span>
        <span class="text-xl font-bold">{{ $students->whereNotNull('assessed_at')->count() }}</span>
    </div>

    <div class="bg-green-100 text-green-700 rounded-lg p-4 flex flex-col items-center justify-center shadow-sm">
        <span class="text-xs font-semibold uppercase">Payment Completed</span>
        <span class="text-xl font-bold">{{ $students->whereNotNull('validated_at')->count() }}</span>
    </div>

    <div class="bg-yellow-100 text-yellow-700 rounded-lg p-4 flex flex-col items-center justify-center shadow-sm">
        <span class="text-xs font-semibold uppercase">Pending Payment</span>
        <span class="text-xl font-bold">{{ $students->whereNotNull('advised_at')->count() }}</span>
    </div>

    <div class="bg-gray-100 text-gray-500 rounded-lg p-4 flex flex-col items-center justify-center shadow-sm">
        <span class="text-xs font-semibold uppercase">Not Enrolled</span>
        <span class="text-xl font-bold">{{ $students->whereNull('assessed_at')->whereNull('validated_at')->whereNull('advised_at')->count() }}</span>
    </div>
</div>

<div x-data="{ search: '{{ request('search') }}', rowsVisible: {{ $students->count() }}, clear() { this.search = ''; $refs.table.querySelectorAll('tbody tr').forEach(tr => tr.style.display = ''); this.rowsVisible = $refs.table.querySelectorAll('tbody tr').length; } }" class="mb-4">
    <div class="flex justify-between items-center">
        <div class="text-gray-600 text-sm">
        Filtered by:
        <span class="font-semibold">
            @if($selectedCourse) Course: {{ $courses->firstWhere('id', $selectedCourse)?->name }} @endif
            @if($selectedYear) | Year: {{ $years->firstWhere('id', $selectedYear)?->name }} @endif
            @if($selectedSection) | Section: {{ $sections->firstWhere('id', $selectedSection)?->name }} @endif
            @if($selectedAdviser) | Adviser: {{ $advisers->firstWhere('id', $selectedAdviser)?->first_name }} {{ $advisers->firstWhere('id', $selectedAdviser)?->last_name }} @endif
            @if($selectedStatus) | Status: {{ ucfirst(str_replace('_', ' ', $selectedStatus)) }} @endif
        </span>
    </div>
    <div class="flex justify-end gap-2 px-2 mb-2 ">
        <div class="relative">
            <input type="text" placeholder="Search student..." x-model="search" @input="
                    let count = 0;
                    $refs.table.querySelectorAll('tbody tr').forEach(tr => {
                        let text = tr.innerText.toLowerCase();
                        if(text.includes(search.toLowerCase())) {
                            tr.style.display = '';
                            count++;
                        } else {
                            tr.style.display = 'none';
                        }
                    });
                    rowsVisible = count;
                " class="rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition">
            <button type="button" x-show="search.length > 0" @click="clear()" class="absolute right-1 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 text-sm font-bold px-1">
                &times;
            </button>
        </div>

        <button @click="openFilter = true" class="bg-gray-300 inline-flex items-center gap-2 px-4 py-2 text-sm font-medium bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg transition"> <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2l-6 7v5l-4 2v-7L3 6V4z" /> </svg> Filters </button>
    </div>
    </div>
    <div x-ref="table" class="overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr class="text-left text-[11px] font-semibold uppercase tracking-wide text-gray-600">
                    <th class="px-5 py-3">#</th>
                    <th class="px-5 py-3">Student</th>
                    <th class="px-5 py-3">Course</th>
                    <th class="px-5 py-3">Year & Section</th>
                    <th class="px-5 py-3">Adviser</th>
                    <th class="px-5 py-3">Status</th>
                    <th class="px-5 py-3 text-right"> </th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 text-gray-700">
                @foreach($students as $student)
                <tr class="hover:bg-gray-50 transition">
                    <td class="px-5 py-3 text-gray-500">{{ $loop->iteration }}</td>
                    <td class="px-5 py-3">
                        {{ strtoupper($student->student->last_name) }}, {{ strtoupper($student->student->first_name) }}
                        <p class="text-xs text-gray-400 mt-1">ID: {{ $student->student->student_id }}</p>
                    </td>
                    <td class="px-5 py-3">{{ $student->course?->name ?? '—' }}</td>
                    <td class="px-5 py-3">{{ $student->yearLevel?->name ?? '—' }} {{ $student->section?->name ?? '—' }}</td>
                    <td class="px-5 py-3">{{ $student->adviser?->first_name ?? '—' }} {{ $student->adviser?->last_name ?? '' }}</td>
                    <td class="px-5 py-3">
                        <span class="px-3 py-1 text-xs font-semibold rounded-full
                        @php
                            if($student->assessed_at) echo 'bg-indigo-600 text-white';
                            elseif($student->validated_at) echo 'bg-green-600 text-white';
                            elseif($student->advised_at) echo 'bg-yellow-600 text-white';
                            else echo 'bg-gray-100 text-gray-500';
                        @endphp
                    ">
                            @php
                            if($student->assessed_at) echo 'Assessment Completed';
                            elseif($student->validated_at) echo 'For Asessment';
                            elseif($student->advised_at) echo 'Pending Payment';
                            else echo 'Not Enrolled';
                            @endphp
                        </span>
                    </td>
                    <td class="px-5 py-3 text-right">
                        <a href="{{ route('college.students.history', $student->student->id) }}" class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition">
                            View
                        </a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div x-show="rowsVisible === 0" class="p-8 text-center text-gray-500 text-sm">
        No student history found for the selected filters.
        <p class="text-xs text-gray-400 mt-1">Try adjusting the school year or semester.</p>
    </div>
</div>

@endif
