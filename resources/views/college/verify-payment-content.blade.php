<div class="space-y-4">
    <!-- top two columns: student details left, summary right -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <!-- Student Information -->
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
            <h3 class="font-semibold text-blue-800 mb-2">Student Information</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-sm">
                <div>
                    <p class="text-gray-600 font-medium">Student ID</p>
                    <p class="font-semibold" x-text="studentNumber"></p>
                </div>
                <div>
                    <p class="text-gray-600 font-medium">Name</p>
                    <p class="font-semibold" x-text="studentName"></p>
                </div>
                <div>
                    <p class="text-gray-600 font-medium">Course</p>
                    <p class="font-semibold" x-text="studentCourse || '—'"></p>
                </div>
                <div>
                    <p class="text-gray-600 font-medium">Year · Section</p>
                    <p class="font-semibold" x-text="`${studentYear || '—'} · ${studentSection || '—'}`"></p>
                </div>
            </div>
        </div>
        <!-- Payment Summary -->
        <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
            <h3 class="font-semibold text-gray-800 mb-2">Payment Summary</h3>
            <div class="grid grid-cols-3 gap-4 text-center text-sm">
                <div>
                    <p class="text-gray-600 font-medium">Total Mandatory</p>
                    <p class="text-2xl font-bold text-gray-800" x-text="mandatoryFees.length"></p>
                </div>
                <div>
                    <p class="text-gray-600 font-medium">Paid</p>
                    <p class="text-2xl font-bold text-green-600" x-text="mandatoryFees.filter(f => f.payments && f.payments.length > 0).length"></p>
                </div>
                <div>
                    <p class="text-gray-600 font-medium">Pending</p>
                    <p class="text-2xl font-bold text-yellow-600" x-text="mandatoryFees.filter(f => !f.payments || f.payments.length === 0).length"></p>
                </div>
            </div>
            <div class="mt-3 pt-3 border-t border-gray-300 text-sm">
                <span :class="allMandatoryFeesPaid ? 'text-green-600 font-semibold' : 'text-yellow-600 font-semibold'" x-text="allMandatoryFeesPaid ? '✓ All mandatory fees paid' : 'Some mandatory fees pending'"></span>
            </div>
            <template x-if="financialStatus">
                <div class="mt-3 rounded-lg border px-3 py-2 text-sm" :class="{
                    'border-gray-200 bg-gray-50 text-gray-800': financialStatus === 'UNPAID',
                    'border-green-200 bg-green-50 text-green-800': financialStatus === 'PAID',
                    'border-blue-200 bg-blue-50 text-blue-800': financialStatus === 'DEFERRED',
                    'border-amber-200 bg-amber-50 text-amber-800': financialStatus === 'PARTIALLY_PAID',
                    'border-red-200 bg-red-50 text-red-800': financialStatus === 'DEFAULT' || financialStatus === 'BAD_DEBT'
                }">
                    <p class="font-semibold">Financial Status: <span x-text="financialStatus"></span></p>
                    <template x-if="financialStatus === 'DEFERRED' && activePromissoryNote">
                        <p class="mt-1 text-xs">
                            Active PN #<span x-text="activePromissoryNote.id"></span> due
                            <span x-text="activePromissoryNote.due_date || '—'"></span>,
                            remaining balance <span x-text="`₱ ${parseFloat(activePromissoryNote.remaining_balance || 0).toFixed(2)}`"></span>
                        </p>
                    </template>
                </div>
            </template>

            <template x-if="canIssuePromissoryNote">
                <div class="mt-3 rounded-lg border border-red-200 bg-red-50 px-3 py-3 text-sm text-red-900">
                    <p class="font-semibold">Promissory note available</p>
                    <p class="mt-1 text-red-800">Create a PN for the unpaid mandatory fees before clearing this student.</p>
                    <button type="button" @click="openPromissoryPreview()" class="mt-3 rounded-lg bg-red-800 px-4 py-2 text-sm font-semibold text-white transition hover:bg-red-700">
                        Create Promissory Note
                    </button>
                </div>
            </template>
        </div>
    </div>

    <!-- Mandatory Fees Table -->
    <div class="rounded-lg border border-gray-200 overflow-hidden">
        <div class="bg-red-50 border-b border-red-200 px-4 py-2">
            <h3 class="font-semibold text-red-800">Mandatory Fees</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-100 border-b border-gray-200">
                    <tr>
                        <th class="px-4 py-2 text-left font-semibold text-gray-700">Organization</th>
                        <th class="px-4 py-2 text-left font-semibold text-gray-700">Fee Name</th>
                        <th class="px-4 py-2 text-right font-semibold text-gray-700 w-28">Amount</th>
                        <th class="px-4 py-2 text-center font-semibold text-gray-700 w-20">Status</th>
                        <th class="px-4 py-2 text-left font-semibold text-gray-700">Payment Details</th>
                    </tr>
                </thead>
                <tbody>
                    <template x-for="fee in mandatoryFees" :key="fee.id">
                        <tr :class="fee.payments && fee.payments.length ? 'bg-green-50' : 'bg-yellow-50'" class="border-b border-gray-200 hover:bg-opacity-75 transition">
                            <td class="px-4 py-3 text-gray-700" x-text="fee.organization?.name || 'College'"></td>
                            <td class="px-4 py-3 font-medium text-gray-800" x-text="fee.fee_name"></td>
                            <td class="px-4 py-3 text-right font-semibold text-gray-800" x-text="`₱ ${parseFloat(fee.amount).toFixed(2)}`"></td>
                            <td class="px-4 py-3 text-center">
                                <span class="text-xs px-2 py-1 rounded font-semibold" :class="fee.payments && fee.payments.length ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700'" x-text="fee.payments && fee.payments.length ? 'PAID' : 'PENDING'"></span>
                            </td>
                            <td class="px-4 py-3 text-xs">
                                <template x-if="fee.payments && fee.payments.length > 0">
                                    <div class="space-y-1">
                                        <template x-for="payment in fee.payments" :key="payment.id">
                                            <div class="text-gray-700">
                                                <p><strong>TXN:</strong> <span x-text="payment.transaction_id" class="text-green-700 font-mono"></span></p>
                                                <p><strong>Paid:</strong> <span x-text="`₱ ${parseFloat(payment.pivot?.amount_paid || payment.amount_due).toFixed(2)}`" class="text-green-700 font-semibold"></span></p>
                                                <p><strong>Collected by:</strong> <span x-text="payment.organization?.org_code || '—'" class="text-blue-700 font-semibold"></span></p>
                                                <p class="text-gray-500">
                                                    <span x-text="new Date(payment.created_at).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' })"></span>
                                                </p>
                                            </div>
                                        </template>
                                    </div>
                                </template>
                                <template x-if="!fee.payments || fee.payments.length === 0">
                                    <p class="text-gray-500 italic">—</p>
                                </template>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
        <template x-if="mandatoryFees.length === 0">
            <div class="text-center py-6 text-gray-500 italic bg-yellow-50">
                No mandatory fees assigned for this student.
            </div>
        </template>
    </div>

    <!-- Optional Fees (Collapsible) -->
    <template x-if="optionalFees.length > 0">
        <div class="rounded-lg border border-gray-200 overflow-hidden">
            <button @click="optionalFeeShown = !optionalFeeShown" class="w-full bg-blue-50 border-b border-blue-200 px-4 py-3 flex items-center justify-between hover:bg-blue-100 transition">
                <h3 class="font-semibold text-blue-800 flex items-center gap-1">
                    <template x-if="optionalFeeShown">
                        <!-- chevron down -->
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </template>
                    <template x-if="!optionalFeeShown">
                        <!-- chevron right -->
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </template>
                    Optional Fees
                </h3>
                <span class="text-sm text-blue-700 font-medium" x-text="`(${optionalFees.length})`"></span>
            </button>

            <template x-if="optionalFeeShown">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-100 border-b border-gray-200">
                            <tr>
                                <th class="px-4 py-2 text-left font-semibold text-gray-700">Organization</th>
                                <th class="px-4 py-2 text-left font-semibold text-gray-700">Fee Name</th>
                                <th class="px-4 py-2 text-right font-semibold text-gray-700 w-28">Amount</th>
                                <th class="px-4 py-2 text-center font-semibold text-gray-700 w-20">Status</th>
                                <th class="px-4 py-2 text-left font-semibold text-gray-700">Payment Details</th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-for="fee in optionalFees" :key="fee.id">
                                <tr :class="fee.payments && fee.payments.length ? 'bg-green-50' : 'bg-gray-50'" class="border-b border-gray-200 hover:bg-opacity-75 transition">
                                    <td class="px-4 py-3 text-gray-700" x-text="fee.organization?.name || 'College'"></td>
                                    <td class="px-4 py-3 font-medium text-gray-800" x-text="fee.fee_name"></td>
                                    <td class="px-4 py-3 text-right font-semibold text-gray-800" x-text="`₱ ${parseFloat(fee.amount).toFixed(2)}`"></td>
                                    <td class="px-4 py-3 text-center">
                                        <span class="text-xs px-2 py-1 rounded font-semibold" :class="fee.payments && fee.payments.length ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-700'" x-text="fee.payments && fee.payments.length ? 'PAID' : 'PENDING'"></span>
                                    </td>
                                    <td class="px-4 py-3 text-xs">
                                        <template x-if="fee.payments && fee.payments.length > 0">
                                            <div class="space-y-1">
                                                <template x-for="payment in fee.payments" :key="payment.id">
                                                    <div class="text-gray-700">
                                                        <p><strong>TXN:</strong> <span x-text="payment.transaction_id" class="text-green-700 font-mono"></span></p>
                                                        <p><strong>Paid:</strong> <span x-text="`₱ ${parseFloat(payment.pivot?.amount_paid || payment.amount_due).toFixed(2)}`" class="text-green-700 font-semibold"></span></p>
                                                        <p><strong>Collected by:</strong> <span x-text="payment.organization?.org_code || '—'" class="text-blue-700 font-semibold"></span></p>
                                                        <p class="text-gray-500">
                                                            <span x-text="new Date(payment.created_at).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' })"></span>
                                                        </p>
                                                    </div>
                                                </template>
                                            </div>
                                        </template>
                                        <template x-if="!fee.payments || fee.payments.length === 0">
                                            <p class="text-gray-500 italic">—</p>
                                        </template>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </template>
        </div>
    </template>
</div>
