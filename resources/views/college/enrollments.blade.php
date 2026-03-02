@if($tab === 'enrollments')
<div x-data="{ search: '{{ request('search') }}', rowsVisible: {{ $students->count() }}, clear() { this.search = ''; $refs.table.querySelectorAll('tbody tr').forEach(tr => tr.style.display = ''); this.rowsVisible = $refs.table.querySelectorAll('tbody tr').length; } }" class="mb-4">
    <div class="flex justify-end gap-2 px-2 mb-2">
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
                            if($student->assessed_at) echo 'bg-green-100 text-green-700';
                            elseif($student->validated_at) echo 'bg-yellow-100 text-yellow-700';
                            elseif($student->advised_at) echo 'bg-blue-100 text-blue-700';
                            else echo 'bg-gray-100 text-gray-500';
                        @endphp
                    ">
                            @php
                            if($student->assessed_at) echo 'Assessed';
                            elseif($student->validated_at) echo 'To be Assessed';
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
