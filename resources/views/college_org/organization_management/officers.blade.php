<div class="bg-white p-8 rounded-xl shadow-sm border border-gray-200">

    <div class="mb-6">
        <h2 class="text-xl font-semibold text-gray-800">Organization Officers</h2>
        <p class="text-sm text-gray-500">
            View all assigned officers and their academic details.
        </p>
    </div>

    @if($officers->isEmpty())
        <div class="text-center py-16 text-gray-400">
            <div class="text-4xl mb-2">👥</div>
            <p class="text-sm">No officers assigned yet.</p>
        </div>
    @else

    <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">

        @foreach($officers as $officer)
            @php
                $student = $officer->student;
                $enrollment = $student?->latestEnrollment;
            @endphp

            <div class="bg-gray-50 border rounded-xl p-5 hover:shadow-md transition">

                <div class="flex items-center gap-3 mb-4">
                    <div class="h-12 w-12 rounded-full bg-red-100 flex items-center justify-center text-red-700 font-bold">
                        {{ strtoupper(substr($student->first_name ?? 'U', 0, 1)) }}
                    </div>

                    <div>
                        <p class="font-semibold text-gray-800">
                            {{ $student->full_name ?? 'Unknown Student' }}
                        </p>
                        <p class="text-xs text-gray-500">
                            {{ $student->student_id ?? '-' }}
                        </p>
                    </div>
                </div>

                <div class="mb-4">
                    <span class="inline-block px-3 py-1 text-xs font-medium bg-blue-100 text-blue-700 rounded-full">
                        {{ ucfirst($officer->role) }}
                    </span>
                </div>

                <div class="text-sm text-gray-600 space-y-1">
                    <p><span class="font-medium">Program</span></p>
                    <p><span class="font-medium"></span> {{ $enrollment?->course?->name ?? '-' }} {{ $enrollment?->yearLevel?->name ?? '-' }}  {{ $enrollment?->section?->name ?? '-' }}</p>
                </div>

                <div class="mt-4 space-y-2 text-xs text-gray-500 border-t pt-3">

                    <div class="flex justify-between">
                        <span class="font-medium text-gray-600">Student Email</span>
                        <span class="text-gray-700">
                            {{ $student->email ?? '-' }}
                        </span>
                    </div>

                    <div class="flex justify-between">
                        <span class="font-medium text-gray-600">Org Account Email</span>
                        <span class="text-gray-700">
                          {{ optional($officer->user)->email ?? '-' }}
                        </span>
                    </div>

                </div>

            </div>

        @endforeach

    </div>

    @endif

</div>