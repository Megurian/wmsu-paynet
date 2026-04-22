<div class="bg-white p-6 rounded shadow">

    <h2 class="text-xl font-semibold mb-6">Organization Officers</h2>

    @if($officers->isEmpty())
        <div class="text-gray-500 text-center py-10">
            No officers assigned yet.
        </div>
    @else

    <div class="overflow-x-auto">
        <table class="min-w-full border border-gray-200 rounded">

            <thead class="bg-gray-100">
                <tr>
                    <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">Name</th>
                    <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">Role</th>
                    <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">Student ID</th>
                    <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">Course</th>
                    <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">Year</th>
                    <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">Section</th>
                </tr>
            </thead>

            <tbody class="divide-y">

                @foreach($officers as $officer)
                    @php
                        $student = $officer->student;
                        $enrollment = $student?->latestEnrollment;
                    @endphp

                    <tr class="hover:bg-gray-50">

                        <!-- NAME -->
                        <td class="px-4 py-3 font-medium text-gray-800">
                            {{ $student->full_name ?? '-' }}
                        </td>

                        <!-- ROLE -->
                        <td class="px-4 py-3">
                            <span class="px-2 py-1 text-xs rounded bg-blue-100 text-blue-700">
                                {{ ucfirst($officer->role) }}
                            </span>
                        </td>

                        <!-- STUDENT ID -->
                        <td class="px-4 py-3 text-gray-600">
                            {{ $student->student_id ?? '-' }}
                        </td>

                        <!-- COURSE -->
                        <td class="px-4 py-3 text-gray-600">
                            {{ $enrollment?->course?->name ?? '-' }}
                        </td>

                        <!-- YEAR -->
                        <td class="px-4 py-3 text-gray-600">
                            {{ $enrollment?->yearLevel?->name ?? '-' }}
                        </td>

                        <!-- SECTION -->
                        <td class="px-4 py-3 text-gray-600">
                            {{ $enrollment?->section?->name ?? '-' }}
                        </td>

                    </tr>
                @endforeach

            </tbody>
        </table>
    </div>

    @endif

</div>