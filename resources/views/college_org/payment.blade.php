@extends('layouts.dashboard')

@section('title', 'Cashiering')
@section('page-title', 'Cashiering')

@section('content')
<div class="max-w-7xl mx-auto space-y-6">
    <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
        <div class="space-y-3">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <h2 class="text-lg font-semibold tracking-tight text-slate-900">Cashiering</h2>
                    <p class="text-sm text-slate-500">Welcome, {{ Auth::user()->name }}</p>
                </div>
            </div>

            <div class="relative">
                <input
                    type="text"
                    id="studentSearch"
                    placeholder="Search student by name or ID"
                    class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm shadow-sm outline-none transition placeholder:text-slate-400 focus:border-red-500 focus:ring-2 focus:ring-red-100"
                    autocomplete="off"
                >
                <ul id="searchResults"
                    class="absolute z-20 mt-2 hidden max-h-56 w-full overflow-auto rounded-xl border border-slate-200 bg-white text-sm shadow-xl"></ul>
            </div>

            <div id="studentCard" class="hidden rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">
                <div class="flex items-center justify-between gap-3">
                    <div class="grid flex-1 grid-cols-2 gap-x-4 gap-y-2 text-sm sm:grid-cols-4">
                        <div>
                            <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-500">Student ID</p>
                            <p id="cardStudentId" class="truncate font-semibold text-slate-900"></p>
                        </div>
                        <div class="sm:col-span-2">
                            <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-500">Name</p>
                            <p id="cardName" class="truncate font-semibold text-slate-900"></p>
                        </div>
                        <div>
                            <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-500">Course</p>
                            <p id="cardCourse" class="truncate font-semibold text-slate-900"></p>
                        </div>
                        <div>
                            <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-500">Year & Section</p>
                            <p id="cardYearSection" class="truncate font-semibold text-slate-900"></p>
                        </div>
                        <div class="sm:col-span-2">
                            <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-500">Email</p>
                            <p id="cardEmail" class="truncate font-semibold text-slate-900"></p>
                        </div>
                        <div>
                            <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-500">School Year</p>
                            <p id="cardSchoolYear" class="truncate font-semibold text-slate-900"></p>
                        </div>
                        <div>
                            <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-500">Semester</p>
                            <p id="cardSemester" class="truncate font-semibold text-slate-900"></p>
                        </div>
                    </div>
                    <button id="paymentHistoryButton" disabled
                        class="rounded-xl border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-50"
                    >
                        View Payment History
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 xl:grid-cols-2">
        <div id="regularFeesPanel" class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between gap-3 border-b border-slate-200 pb-3">
                <div>
                    <h3 class="text-base font-semibold text-slate-900">Regular Fees</h3>
                    <p class="text-sm text-slate-500">Standard fee collection for the selected student.</p>
                </div>
                <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">Cash Collection</span>
            </div>

            <div class="mt-4 space-y-4">
                <div class="space-y-2 text-sm">
                    <p class="font-medium text-slate-600">Fee Items</p>
                    <div id="regularFeesList" class="space-y-2"></div>
                        <p id="regularPanelNotice" class="text-sm text-red-600"></p>
                    <div class="flex justify-between text-sm">
                        <span class="text-slate-600">Total</span>
                        <span class="font-semibold text-slate-900">₱ <span id="regularTotalAmount">0.00</span></span>
                    </div>

                    <div class="mt-3">
                        <label class="text-sm font-medium text-slate-600">Cash Received</label>
                        <input
                            type="number"
                            id="regularCashInput"
                            class="mt-1 w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm outline-none transition focus:border-red-500 focus:ring-2 focus:ring-red-100"
                            min="0"
                            step="0.01"
                        >
                    </div>

                    <div class="mt-3 flex justify-between text-sm">
                        <span class="text-slate-600">Change</span>
                        <span class="font-semibold text-slate-900">₱ <span id="regularChangeAmount">0.00</span></span>
                    </div>
                </div>

                <button id="regularProceedPayment"
                    class="w-full rounded-xl bg-red-700 py-3 text-sm font-semibold text-white transition hover:bg-red-800 disabled:cursor-not-allowed disabled:bg-slate-300"
                >
                    Proceed Payment
                </button>
            </div>
        </div>

        <div id="promissoryPanel" class="rounded-2xl border border-amber-200 bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between gap-3 border-b border-amber-100 pb-3">
                <div>
                    <h3 class="text-base font-semibold text-slate-900">Promissory Note Payment</h3>
                    <p class="text-sm text-slate-500">Settle an active, defaulted, or bad-debt PN here.</p>
                </div>
                <span class="rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-700">PN Settlement</span>
            </div>

            <div class="mt-4 space-y-4">
                <div id="promissoryNoteSummary" class="hidden rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900"></div>

                <div class="space-y-2 text-sm">
                    <p class="font-medium text-slate-600">PN Fee Items</p>
                    <div id="promissoryFeesList" class="space-y-2"></div>
                </div>

                <div class="rounded-xl bg-amber-50 p-4">
                    <div class="flex justify-between text-sm">
                        <span class="text-slate-600">Total</span>
                        <span class="font-semibold text-slate-900">₱ <span id="promissoryTotalAmount">0.00</span></span>
                    </div>

                    <div class="mt-3">
                        <label class="text-sm font-medium text-slate-600">Cash Received</label>
                        <input
                            type="number"
                            id="promissoryCashInput"
                            class="mt-1 w-full rounded-xl border border-amber-200 bg-white px-3 py-2 text-sm outline-none transition focus:border-amber-500 focus:ring-2 focus:ring-amber-100"
                            min="0"
                            step="0.01"
                        >
                    </div>

                    <div class="mt-3 flex justify-between text-sm">
                        <span class="text-slate-600">Change</span>
                        <span class="font-semibold text-slate-900">₱ <span id="promissoryChangeAmount">0.00</span></span>
                    </div>
                </div>

                <button id="promissoryProceedPayment"
                    class="w-full rounded-xl bg-amber-700 py-3 text-sm font-semibold text-white transition hover:bg-amber-800 disabled:cursor-not-allowed disabled:bg-slate-300"
                >
                    Settle Promissory Note
                </button>
            </div>
        </div>
    </div>
</div>

<div id="paymentHistoryModal" class="hidden fixed inset-0 z-50 items-center justify-center bg-slate-900/50 p-4">
    <div class="w-full max-w-4xl overflow-hidden rounded-3xl bg-white shadow-xl">
        <div class="flex items-center justify-between border-b border-slate-200 px-6 py-4">
            <div>
                <h3 class="text-lg font-semibold text-slate-900">Payment History</h3>
                <p id="paymentHistoryStudentLabel" class="text-sm text-slate-500"></p>
            </div>
            <button id="paymentHistoryClose" type="button" class="rounded-full bg-slate-100 px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-200">
                Close
            </button>
        </div>
        <div id="paymentHistoryBody" class="max-h-[70vh] overflow-auto p-6">
            <p class="text-sm text-slate-500">Payment history will appear here.</p>
        </div>
    </div>
</div>

<script>
const searchInput = document.getElementById('studentSearch');
const resultsList = document.getElementById('searchResults');
const studentCard = document.getElementById('studentCard');
const regularFeesPanel = document.getElementById('regularFeesPanel');
const promissoryPanel = document.getElementById('promissoryPanel');

const regularFeesList = document.getElementById('regularFeesList');
const regularPanelNotice = document.getElementById('regularPanelNotice');
const regularTotalAmountEl = document.getElementById('regularTotalAmount');
const regularCashInput = document.getElementById('regularCashInput');
const regularChangeAmountEl = document.getElementById('regularChangeAmount');
const regularProceedBtn = document.getElementById('regularProceedPayment');

const promissoryFeesList = document.getElementById('promissoryFeesList');
const promissoryTotalAmountEl = document.getElementById('promissoryTotalAmount');
const promissoryCashInput = document.getElementById('promissoryCashInput');
const promissoryChangeAmountEl = document.getElementById('promissoryChangeAmount');
const promissoryProceedBtn = document.getElementById('promissoryProceedPayment');

const paymentHistoryButton = document.getElementById('paymentHistoryButton');
const paymentHistoryModal = document.getElementById('paymentHistoryModal');
const paymentHistoryBody = document.getElementById('paymentHistoryBody');
const paymentHistoryStudentLabel = document.getElementById('paymentHistoryStudentLabel');
const paymentHistoryClose = document.getElementById('paymentHistoryClose');

let FEES = [];
let PAID_FEES = [];
let ACTIVE_PROMISSORY_NOTE = null;
let SELECTED_STUDENT = null;
let PROMISSORY_FEES = [];


searchInput.addEventListener('input', function () {
    const query = this.value.trim();
    if (!query) {
        resultsList.classList.add('hidden');
        return;
    }

    fetch(`/college_org/search-students?q=${encodeURIComponent(query)}`)
        .then(res => {
            if (!res.ok) throw new Error('Search failed');
            return res.json();
        })
        .then(data => {
            resultsList.innerHTML = '';
            if (data.length === 0) {
                resultsList.classList.add('hidden');
                return;
            }

            data.forEach(student => {
                const li = document.createElement('li');
                li.textContent = ` ${student.student_id} - ${student.name}`;
                li.className = 'px-3 py-2 hover:bg-gray-100 cursor-pointer text-md uppercase';
                li.addEventListener('click', () => {
                    searchInput.value = `${student.name} (${student.student_id})`;
                    resultsList.classList.add('hidden');
                    loadStudentDetails(student.id);
                });
                resultsList.appendChild(li);
            });
            resultsList.classList.remove('hidden');
        })
        .catch(err => {
            resultsList.innerHTML = `<li class="px-3 py-2 text-red-600 text-sm">Search error: ${err.message}</li>`;
            resultsList.classList.remove('hidden');
        });
});

function loadStudentDetails(studentId) {
    fetch(`/college_org/students/${studentId}/fees`)
        .then(res => {
            if (!res.ok) throw new Error('Failed to load student details');
            return res.json();
        })
        .then(data => {
            SELECTED_STUDENT = data.student;
            FEES = data.fees || [];
            PAID_FEES = (data.paid_fee_ids || []).map(Number);

            document.getElementById('cardStudentId').textContent = data.student.student_id;
            document.getElementById('cardName').textContent = `${data.student.first_name} ${data.student.last_name}`;
            document.getElementById('cardCourse').textContent = data.student.course ?? '—';
            document.getElementById('cardYearSection').textContent =
                `${data.student.year ?? '—'} - ${data.student.section ?? '—'}`;
            document.getElementById('cardEmail').textContent = data.student.email ?? '—';
            document.getElementById('cardSchoolYear').textContent = data.student.school_year ?? '—';
            document.getElementById('cardSemester').textContent = data.student.semester ?? '—';

            studentCard.classList.remove('hidden');
            resultsList.classList.add('hidden');
            ACTIVE_PROMISSORY_NOTE = null;
            PROMISSORY_FEES = [];
            renderPromissoryNoteSummary();
            regularPanelNotice.textContent = '';
            promissoryCashInput.disabled = true;
            promissoryProceedBtn.disabled = true;

            return fetch(`/college_org/students/${studentId}/promissory-notes`)
                .then(res => res.ok ? res.json() : null)
                .catch(() => null);
        })
        .then(promissoryNote => {
            ACTIVE_PROMISSORY_NOTE = promissoryNote && promissoryNote.id && Number(promissoryNote.remaining_balance) > 0
                ? promissoryNote
                : null;
            PROMISSORY_FEES = ACTIVE_PROMISSORY_NOTE ? (ACTIVE_PROMISSORY_NOTE.fees || []) : [];

            renderPromissoryNoteSummary();
            renderRegularFees();
            renderPromissoryFees();
            resetRegularPayment();
            resetPromissoryPayment();

            regularCashInput.disabled = false;
            promissoryCashInput.disabled = !ACTIVE_PROMISSORY_NOTE;
            updateRegularProceedBtnState();
            updatePromissoryProceedBtnState();
            setPaymentHistoryButtonState();
        })
        .catch(err => {
            alert('Error loading student details: ' + (err.message || 'Please try again.'));
            hideStudentCard();
        });
}

function renderRegularFees() {
    regularFeesList.innerHTML = '';

    if (FEES.length === 0) {
        regularFeesList.innerHTML = `<p class="text-gray-500 text-sm">No approved fees for this organization.</p>`;
        regularPanelNotice.textContent = ACTIVE_PROMISSORY_NOTE
            ? 'All collectable fees are currently covered by an outstanding promissory note.'
            : '';
        return;
    }

    FEES.forEach(fee => {
        const amount = parseFloat(fee.amount) || 0;
        const isMandatory = fee.requirement_level === 'mandatory';
        const isPaid = PAID_FEES.includes(Number(fee.id));
        const checkedAttr = isMandatory && !isPaid ? 'checked' : '';
        const disabledAttr = isPaid ? 'disabled' : '';

        const periodLabel = fee.school_year || fee.semester
            ? ` <span class="text-xs text-slate-500">[${fee.school_year ?? 'General'} ${fee.semester ? '· ' + fee.semester : ''}]</span>`
            : '';

        const div = document.createElement('div');
        div.className = 'flex items-center justify-between text-sm';
        div.innerHTML = `
            <label class="flex items-center gap-2 ${isPaid ? 'text-gray-400 ' : ''}">
                <input type="checkbox" data-id="${fee.id}" data-amount="${amount}" class="regularFeeCheckbox"
                    ${checkedAttr} ${disabledAttr}>
                <span>
                    ${fee.fee_name}
                    <span class="text-xs text-gray-400">(${fee.requirement_level})</span>
                    ${periodLabel}
                </span>
                ${isPaid ? '<span class="text-xs text-green-600 font-semibold ml-1">(PAID)</span>' : ''}
            </label>
            <span>₱ ${amount.toFixed(2)}</span>
        `;
        regularFeesList.appendChild(div);
    });

    document.querySelectorAll('.regularFeeCheckbox').forEach(cb => cb.addEventListener('change', calculateRegularTotal));
    calculateRegularTotal();

    const hasOpenPN = !!(ACTIVE_PROMISSORY_NOTE && Number(ACTIVE_PROMISSORY_NOTE.remaining_balance) > 0);
    regularPanelNotice.textContent = hasOpenPN
        ? 'This student has an outstanding promissory note; regular fee collection is disabled until the note is settled.'
        : '';
}

function renderPromissoryFees() {
    promissoryFeesList.innerHTML = '';

    if (!ACTIVE_PROMISSORY_NOTE) {
        promissoryFeesList.innerHTML = `<p class="text-gray-500 text-sm">Search a student with an active or overdue promissory note to settle it here.</p>`;
        return;
    }

    if (PROMISSORY_FEES.length === 0) {
        promissoryFeesList.innerHTML = `<p class="text-gray-500 text-sm">No fees attached to this promissory note.</p>`;
        return;
    }

    PROMISSORY_FEES.forEach(fee => {
        const amount = parseFloat(fee.amount_remaining) || 0;
        const paid = parseFloat(fee.amount_paid) || 0;
        const partialLabel = paid > 0 ? ` <span class="text-xs text-amber-600">(partial paid ₱ ${paid.toFixed(2)})</span>` : '';
        const div = document.createElement('div');
        div.className = 'flex items-center justify-between text-sm';
        div.innerHTML = `
            <label class="flex items-center gap-2">
                <input type="checkbox" data-id="${fee.id}" data-amount="${amount}" class="promissoryFeeCheckbox" checked>
                ${fee.name || 'Deferred fee'}${partialLabel}
            </label>
            <span>₱ ${amount.toFixed(2)}</span>
        `;
        promissoryFeesList.appendChild(div);
    });

    document.querySelectorAll('.promissoryFeeCheckbox').forEach(cb => cb.addEventListener('change', calculatePromissoryTotal));
    calculatePromissoryTotal();
}

function calculateRegularTotal() {
    let total = 0;
    document.querySelectorAll('.regularFeeCheckbox:checked').forEach(cb => total += parseFloat(cb.dataset.amount));
    regularTotalAmountEl.textContent = total.toFixed(2);
    calculateRegularChange();
}

function calculatePromissoryTotal() {
    let total = 0;
    document.querySelectorAll('.promissoryFeeCheckbox:checked').forEach(cb => total += parseFloat(cb.dataset.amount));
    promissoryTotalAmountEl.textContent = total.toFixed(2);
    calculatePromissoryChange();
}

function calculateRegularChange() {
    const total = parseFloat(regularTotalAmountEl.textContent) || 0;
    const cash = parseFloat(regularCashInput.value) || 0;
    const change = cash - total;
    regularChangeAmountEl.textContent = change >= 0 ? change.toFixed(2) : '0.00';
    updateRegularProceedBtnState();
}

function calculatePromissoryChange() {
    const total = parseFloat(promissoryTotalAmountEl.textContent) || 0;
    const cash = parseFloat(promissoryCashInput.value) || 0;
    const change = cash - total;
    promissoryChangeAmountEl.textContent = change >= 0 ? change.toFixed(2) : '0.00';
    updatePromissoryProceedBtnState();
}

function resetRegularPayment() {
    regularCashInput.value = '';
    regularChangeAmountEl.textContent = '0.00';
    document.querySelectorAll('.regularFeeCheckbox').forEach(cb => cb.checked = cb.hasAttribute('checked'));
    calculateRegularTotal();

    if (!SELECTED_STUDENT) {
        regularCashInput.disabled = true;
        regularProceedBtn.disabled = true;
        regularFeesList.innerHTML = `<p class="text-gray-500 text-sm">Select a student to load fees.</p>`;
    }
}

function resetPromissoryPayment() {
    promissoryCashInput.value = '';
    promissoryChangeAmountEl.textContent = '0.00';
    document.querySelectorAll('.promissoryFeeCheckbox').forEach(cb => cb.checked = cb.hasAttribute('checked'));
    calculatePromissoryTotal();

    if (!SELECTED_STUDENT || !ACTIVE_PROMISSORY_NOTE) {
        promissoryCashInput.disabled = true;
        promissoryProceedBtn.disabled = true;
        return;
    }

    promissoryCashInput.disabled = false;
    promissoryProceedBtn.disabled = false;
}

function updateRegularProceedBtnState() {
    const hasStudent = !!SELECTED_STUDENT;
    const hasFees = document.querySelectorAll('.regularFeeCheckbox:checked').length > 0;
    const cash = parseFloat(regularCashInput.value) || 0;
    const total = parseFloat(regularTotalAmountEl.textContent) || 0;
    const hasOpenPN = !!(ACTIVE_PROMISSORY_NOTE && Number(ACTIVE_PROMISSORY_NOTE.remaining_balance) > 0);
    regularProceedBtn.disabled = hasOpenPN || !(hasStudent && hasFees && cash >= total);
}

function updatePromissoryProceedBtnState() {
    const hasStudent = !!SELECTED_STUDENT;
    const hasNote = !!ACTIVE_PROMISSORY_NOTE;
    const hasFees = document.querySelectorAll('.promissoryFeeCheckbox:checked').length > 0;
    const cash = parseFloat(promissoryCashInput.value) || 0;
    const total = parseFloat(promissoryTotalAmountEl.textContent) || 0;
    promissoryProceedBtn.disabled = !(hasStudent && hasNote && hasFees && cash >= total);
}

function renderPromissoryNoteSummary() {
    const summary = document.getElementById('promissoryNoteSummary');

    if (!ACTIVE_PROMISSORY_NOTE) {
        summary.classList.add('hidden');
        summary.innerHTML = `
            <div class="flex items-start justify-between gap-3">
                <div>
                    <p class="font-semibold">No Promissory Note</p>
                    <p class="mt-1 text-xs text-amber-800">This student has no active, defaulted, or bad debt promissory note to settle.</p>
                </div>
            </div>
        `;
        summary.classList.remove('hidden');
        promissoryProceedBtn.disabled = true;
        return;
    }

    const noteId = ACTIVE_PROMISSORY_NOTE.id ?? '—';
    const noteStatus = ACTIVE_PROMISSORY_NOTE.status ?? 'ACTIVE';
    const noteDueDate = ACTIVE_PROMISSORY_NOTE.due_date ?? '—';
    const noteRemainingBalance = Number(ACTIVE_PROMISSORY_NOTE.remaining_balance ?? 0);
    const noteFees = Array.isArray(ACTIVE_PROMISSORY_NOTE.fees) ? ACTIVE_PROMISSORY_NOTE.fees : [];
    const notePeriod = ACTIVE_PROMISSORY_NOTE.school_year || ACTIVE_PROMISSORY_NOTE.semester
        ? `${ACTIVE_PROMISSORY_NOTE.school_year ?? ''}${ACTIVE_PROMISSORY_NOTE.school_year && ACTIVE_PROMISSORY_NOTE.semester ? ' · ' : ''}${ACTIVE_PROMISSORY_NOTE.semester ?? ''}`
        : 'Not specified';

    summary.innerHTML = `
        <div class="flex items-start justify-between gap-3">
            <div>
                <p class="font-semibold">Promissory Note #${noteId}</p>
                <p class="mt-1 text-xs text-amber-800">Pay this note here by selecting the deferred fees and entering the cash received.</p>
            </div>
            <span class="rounded-full bg-amber-200 px-2 py-1 text-xs font-semibold text-amber-900">${noteStatus}</span>
        </div>
        <div class="mt-3 grid grid-cols-1 gap-2 sm:grid-cols-4 text-xs">
            <div><span class="font-semibold">Due:</span> ${noteDueDate}</div>
            <div><span class="font-semibold">Remaining:</span> ₱ ${noteRemainingBalance.toFixed(2)}</div>
            <div><span class="font-semibold">Fees:</span> ${noteFees.length}</div>
            <div><span class="font-semibold">PN Period:</span> ${notePeriod}</div>
        </div>
    `;

    summary.classList.remove('hidden');
}

regularCashInput.addEventListener('input', calculateRegularChange);
promissoryCashInput.addEventListener('input', calculatePromissoryChange);

paymentHistoryButton.addEventListener('click', () => {
    if (!SELECTED_STUDENT) return;
    openPaymentHistoryModal();
    loadPaymentHistory(SELECTED_STUDENT.id);
});

paymentHistoryClose.addEventListener('click', closePaymentHistoryModal);
paymentHistoryModal.addEventListener('click', (event) => {
    if (event.target === paymentHistoryModal) {
        closePaymentHistoryModal();
    }
});

function openPaymentHistoryModal() {
    paymentHistoryModal.classList.remove('hidden');
    paymentHistoryModal.classList.add('flex');
}

function closePaymentHistoryModal() {
    paymentHistoryModal.classList.add('hidden');
    paymentHistoryModal.classList.remove('flex');
}

function setPaymentHistoryButtonState() {
    paymentHistoryButton.disabled = !SELECTED_STUDENT;
}

function loadPaymentHistory(studentId) {
    paymentHistoryStudentLabel.textContent = '';
    paymentHistoryBody.innerHTML = '<p class="text-sm text-slate-500">Loading payment history...</p>';

    fetch(`/college_org/students/${studentId}/payment-history`)
        .then(res => res.ok ? res.json() : res.json().then(data => { throw new Error(data.message || 'Failed to load payment history.'); }))
        .then(data => {
            paymentHistoryStudentLabel.textContent = `${data.student.name} • ${data.student.student_id}`;
            renderPaymentHistory(data.payments || []);
        })
        .catch(err => {
            paymentHistoryBody.innerHTML = `<p class="text-sm text-red-600">${err.message}</p>`;
        });
}

function renderPaymentHistory(payments) {
    if (!payments.length) {
        paymentHistoryBody.innerHTML = '<p class="text-sm text-slate-500">No payment history found.</p>';
        return;
    }

    const semesterOrder = {
        '1st SEMESTER': 1,
        '2nd SEMESTER': 2,
        'SUMMER': 3,
    };

    const grouped = payments.reduce((acc, payment) => {
        const key = `${payment.school_year}|${payment.semester}`;
        if (!acc[key]) {
            const schoolYearStart = parseInt(payment.school_year.split(' - ')[0], 10) || 0;
            acc[key] = {
                school_year: payment.school_year,
                semester: payment.semester,
                order: [schoolYearStart, semesterOrder[payment.semester] || 99],
                payments: [],
            };
        }
        acc[key].payments.push(payment);
        return acc;
    }, {});

    const sortedGroups = Object.values(grouped).sort((a, b) => {
        if (a.order[0] !== b.order[0]) return b.order[0] - a.order[0];
        return b.order[1] - a.order[1];
    });

    paymentHistoryBody.innerHTML = sortedGroups.map(group => {
        const rows = group.payments
            .sort((a, b) => new Date(b.date) - new Date(a.date))
            .map(payment => {
            const feeList = payment.fees.map(fee =>
                `<div class="text-xs text-slate-600">${fee.name} — ₱ ${fee.amount_paid.toFixed(2)}</div>`
            ).join('');

            const paymentTypeLabel = payment.payment_type === 'PROMISSORY' ? 'Promissory' : 'Cash';
            const collectorName = payment.collector_name || '—';

            return `
                <tr class="hover:bg-slate-50 transition">
                    <td class="px-4 py-3 text-sm text-slate-700">${payment.date}</td>
                    <td class="px-4 py-3 text-sm text-slate-700">${paymentTypeLabel}</td>
                    <td class="px-4 py-3 text-sm text-slate-700">${collectorName}</td>
                    <td class="px-4 py-3 text-sm text-slate-700">₱ ${payment.cash_received.toFixed(2)}</td>
                    <td class="px-4 py-3 text-sm text-slate-700">${feeList}</td>
                </tr>
            `;
        }).join('');

        return `
            <div class="rounded-3xl border border-slate-200 bg-slate-50 p-4 mb-4">
                <div class="mb-4 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p class="text-sm font-semibold text-slate-900">${group.school_year}</p>
                        <p class="text-xs text-slate-600">${group.semester}</p>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-left text-sm">
                        <thead class="bg-white border-b border-slate-200 text-slate-500 text-[11px] uppercase tracking-wide">
                            <tr>
                                <th class="px-4 py-3">Date</th>
                                <th class="px-4 py-3">Type</th>
                                <th class="px-4 py-3">Collector</th>
                                <th class="px-4 py-3">Amount</th>
                                <th class="px-4 py-3">Fees</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 text-slate-700">
                            ${rows}
                        </tbody>
                    </table>
                </div>
            </div>
        `;
    }).join('');
}

regularProceedBtn.addEventListener('click', () => {
    if(!SELECTED_STUDENT) {
        alert('Select a student first.');
        return;
    }

    const cashReceived = parseFloat(regularCashInput.value) || 0;
    const selectedFees = Array.from(document.querySelectorAll('.regularFeeCheckbox:checked'))
        .map(cb => parseInt(cb.dataset.id));

    if(selectedFees.length === 0) {
        alert('Select at least one fee.');
        return;
    }

    if(cashReceived < parseFloat(regularTotalAmountEl.textContent)) {
        alert('Cash received is less than total.');
        return;
    }

    regularProceedBtn.disabled = true;
    regularProceedBtn.textContent = 'Processing...';

    fetch('/college_org/payment/collect', {
        method:'POST',
        headers:{ 'Content-Type':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}' },
        body: JSON.stringify({
            student_id: SELECTED_STUDENT.id,
            fee_ids: selectedFees,
            cash_received: cashReceived,
        })
    })
    .then(res => res.ok ? res.json() : res.json().then(d=>{ throw d; }))
    .then(data => {
        alert(data.message || 'Payment collected successfully.');
        SELECTED_STUDENT = null;
        FEES = [];
        PAID_FEES = [];
        ACTIVE_PROMISSORY_NOTE = null;
        PROMISSORY_FEES = [];
        searchInput.value = '';
        regularProceedBtn.disabled = false;
        regularProceedBtn.textContent = 'Proceed Payment';
        hideStudentCard();
    })
    .catch(err => {
        alert(err.message || 'Something went wrong.');
        regularProceedBtn.disabled = false;
        regularProceedBtn.textContent = 'Proceed Payment';
    });
});

promissoryProceedBtn.addEventListener('click', () => {
    if(!SELECTED_STUDENT) {
        alert('Select a student first.');
        return;
    }

    if (!ACTIVE_PROMISSORY_NOTE) {
        alert('This student has no promissory note to settle.');
        return;
    }

    const cashReceived = parseFloat(promissoryCashInput.value) || 0;
    const selectedFees = Array.from(document.querySelectorAll('.promissoryFeeCheckbox:checked'))
        .map(cb => parseInt(cb.dataset.id));

    if(selectedFees.length === 0) {
        alert('Select at least one PN fee.');
        return;
    }

    if(cashReceived < parseFloat(promissoryTotalAmountEl.textContent)) {
        alert('Cash received is less than total.');
        return;
    }

    promissoryProceedBtn.disabled = true;
    promissoryProceedBtn.textContent = 'Processing...';

    fetch('/college_org/payment/collect', {
        method:'POST',
        headers:{ 'Content-Type':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}' },
        body: JSON.stringify({
            student_id: SELECTED_STUDENT.id,
            fee_ids: selectedFees,
            selected_fees: selectedFees,
            cash_received: cashReceived,
            promissory_note_id: ACTIVE_PROMISSORY_NOTE.id,
        })
    })
    .then(res => res.ok ? res.json() : res.json().then(d=>{ throw d; }))
    .then(data => {
        alert(data.message || 'Promissory note payment collected successfully.');
        SELECTED_STUDENT = null;
        FEES = [];
        PAID_FEES = [];
        ACTIVE_PROMISSORY_NOTE = null;
        PROMISSORY_FEES = [];
        searchInput.value = '';
        promissoryProceedBtn.disabled = false;
        promissoryProceedBtn.textContent = 'Settle Promissory Note';
        hideStudentCard();
    })
    .catch(err => {
        alert(err.message || 'Something went wrong.');
        promissoryProceedBtn.disabled = false;
        promissoryProceedBtn.textContent = 'Settle Promissory Note';
    });
});

document.addEventListener('click', function(e){
    if(!searchInput.contains(e.target) && !resultsList.contains(e.target)){
        resultsList.classList.add('hidden');
    }
});

function hideStudentCard() {
    resultsList.innerHTML = '';
    resultsList.classList.add('hidden');
    studentCard.classList.add('hidden');
    ACTIVE_PROMISSORY_NOTE = null;
    PROMISSORY_FEES = [];
    renderPromissoryNoteSummary();
    renderRegularFees();
    renderPromissoryFees();
    resetRegularPayment();
    resetPromissoryPayment();
    closePaymentHistoryModal();
    setPaymentHistoryButtonState();
}

renderPromissoryNoteSummary();
renderRegularFees();
renderPromissoryFees();
resetRegularPayment();
resetPromissoryPayment();
</script>
@endsection