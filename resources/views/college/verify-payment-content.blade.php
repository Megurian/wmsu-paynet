<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <div class="space-y-4">
        <h4 class="font-semibold text-gray-700">Student Information</h4>
        <div class="grid grid-cols-2 gap-3">
            <div class="bg-gray-50 border rounded-lg p-3">
                <p class="text-xs text-gray-500">Full Name</p>
                <p class="font-semibold text-gray-800" x-text="studentName"></p>
            </div>
            <div class="bg-gray-50 border rounded-lg p-3">
                <p class="text-xs text-gray-500">Student ID</p>
                <p class="font-semibold text-gray-800" x-text="studentNumber"></p>
            </div>
            <div class="bg-gray-50 border rounded-lg p-3">
                <p class="text-xs text-gray-500">Course · Year · Section</p>
                <p class="font-semibold text-gray-800" x-text="`${studentCourse} · ${studentYear} · ${studentSection}`"></p>
            </div>
            <div class="bg-gray-50 border rounded-lg p-3">
                <p class="text-xs text-gray-500">Email</p>
                <p class="font-semibold text-gray-800" x-text="studentEmail || '—'"></p>
            </div>
            <div class="bg-gray-50 border rounded-lg p-3">
                <p class="text-xs text-gray-500">Contact</p>
                <p class="font-semibold text-gray-800" x-text="studentContact || '—'"></p>
            </div>
            <div class="bg-gray-50 border rounded-lg p-3">
                <p class="text-xs text-gray-500">Religion</p>
                <p class="font-semibold text-gray-800" x-text="studentReligion || '—'"></p>
            </div>
        </div>
    </div>

    <!-- Payment Details -->
    <div class="space-y-4">
        <div class="flex items-center justify-between">
            <h4 class="font-semibold text-gray-700">Payment & Transaction Details</h4>
            <span class="text-xs text-gray-400 italic">S.Y. & Semester</span>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <span class="font-medium">Overall Status:</span>
                <span class="ml-1 text-yellow-600 font-semibold">Pending</span>
            </div>
            <div>
                <span class="font-medium">Last Updated:</span>
                <span class="ml-1 text-gray-500">—</span>
            </div>
        </div>

        <hr class="border-gray-200">

        <div class="space-y-3 max-h-80 overflow-y-auto">
            <!-- Example payment entries, repeat as needed -->
            <div class="border rounded-xl p-4 shadow-sm flex justify-between">
                <div>
                    <p class="font-medium">CSC Fee</p>
                    <p class="text-xs text-gray-500">University Student Council</p>
                </div>
                <div class="text-right">
                    <p class="font-semibold">₱ —</p>
                    <p class="text-green-600 text-sm font-medium">Paid</p>
                </div>
            </div>
        </div>
    </div>
</div>