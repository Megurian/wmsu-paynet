@extends('layouts.dashboard')

@section('title', 'Cashiering')
@section('page-title', 'Cashiering')

@section('content')
<div class="max-w-6xl mx-auto space-y-6">

    <div>
        <h2 class="text-2xl font-semibold">Cashier</h2>
        <p class="text-sm text-gray-500">Welcome, {{ auth()->user()->full_name }}</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white border rounded-lg p-4 space-y-4">
            <div>
                <label class="text-sm font-medium text-gray-700">Search Student</label>
                <input
                    type="text"
                    id="studentSearch"
                    placeholder="Name or Student ID"
                    class="mt-1 w-full border rounded-md px-3 py-2 text-sm focus:ring-indigo-600 focus:border-indigo-600"
                    autocomplete="off" >
                <ul id="searchResults"
                    class="border rounded mt-1 bg-white hidden max-h-48 overflow-auto text-sm"></ul>
            </div>

            <div id="studentCard" class="hidden border rounded-md bg-gray-50 p-3">
                <h3 class="text-sm font-semibold mb-3 text-gray-700">Student Information</h3>

                <div class="grid grid-cols-2 gap-y-3 gap-x-4 text-sm">
                    <div>
                        <p class="text-gray-500">Student ID</p>
                        <p id="cardStudentId" class="font-medium"></p>
                    </div>
                    <div>
                        <p class="text-gray-500">Name</p>
                        <p id="cardName" class="font-medium"></p>
                    </div>

                    <div>
                        <p class="text-gray-500">Course</p>
                        <p id="cardCourse" class="font-medium"></p>
                    </div>

                    <div>
                        <p class="text-gray-500">Year & Section</p>
                        <p id="cardYearSection" class="font-medium"></p>
                    </div>

                    <div class="col-span-2">
                        <p class="text-gray-500">Email</p>
                        <p id="cardEmail" class="font-medium"></p>
                    </div>
                </div>
            </div>
        </div>

        <div id="cashierPanel"
             class="bg-white border rounded-lg p-4 flex flex-col justify-between">

            <div class="space-y-4">

                <h3 class="text-sm font-semibold text-gray-700">Payment Details</h3>

                <div class="space-y-2 text-sm">
                    <p class="font-medium text-gray-600">Fees</p>
                    <div id="feesList" class="space-y-2"></div>
                </div>

                <hr>
                <div class="flex justify-between text-sm">
                    <span class="text-gray-600">Total</span>
                    <span class="font-semibold">₱ <span id="totalAmount">0.00</span></span>
                </div>

                <div>
                    <label class="text-sm font-medium text-gray-600">Cash Received</label>
                    <input
                        type="number"
                        id="cashInput"
                        class="mt-1 w-full border rounded-md px-3 py-2 text-sm"
                        min="0"
                        step="0.01"
                    >
                </div>

                <div class="flex justify-between text-sm">
                    <span class="text-gray-600">Change</span>
                    <span class="font-semibold">₱ <span id="changeAmount">0.00</span></span>
                </div>
            </div>

            <button id="proceedPayment"
                class="mt-4 w-full bg-indigo-600 hover:bg-indigo-700 text-white py-2 rounded-md text-sm font-medium transition">
                Proceed Payment
            </button>
        </div>

    </div>
</div>

<script>
const searchInput = document.getElementById('studentSearch');
const resultsList = document.getElementById('searchResults');
const studentCard = document.getElementById('studentCard');
const cashierPanel = document.getElementById('cashierPanel');
const feesList = document.getElementById('feesList');
const totalAmountEl = document.getElementById('totalAmount');
const cashInput = document.getElementById('cashInput');
const changeAmountEl = document.getElementById('changeAmount');
const proceedBtn = document.getElementById('proceedPayment');

let selectedStudent = null;
let FEES = @json($fees);
let PAID_FEES = [];

function updateProceedBtnState() {
    const hasStudent = !!selectedStudent;
    const hasFees = document.querySelectorAll('.feeCheckbox:checked').length > 0;
    const total = parseFloat(totalAmountEl.textContent) || 0;
    const cash = parseFloat(cashInput.value) || 0;
    const sufficientCash = cash >= total;

    proceedBtn.disabled = !(hasStudent && hasFees && sufficientCash);
}

function calculateTotal() {
    let total = 0;
    document.querySelectorAll('.feeCheckbox:checked').forEach(cb => {
        total += parseFloat(cb.dataset.amount);
    });
    totalAmountEl.textContent = total.toFixed(2);
    calculateChange();
    updateProceedBtnState();
}

function calculateChange() {
    const total = parseFloat(totalAmountEl.textContent) || 0;
    const cash = parseFloat(cashInput.value) || 0;
    const change = cash - total;
    changeAmountEl.textContent = change >= 0 ? change.toFixed(2) : '0.00';
    updateProceedBtnState();
}

function resetPayment() {
    cashInput.value = '';
    changeAmountEl.textContent = '0.00';
    document.querySelectorAll('.feeCheckbox').forEach(cb => {
        cb.checked = cb.hasAttribute('checked'); 
    });
    calculateTotal();
}

searchInput.addEventListener('input', function () {
    const query = this.value.trim();
    if (!query) {
        resultsList.innerHTML = '';
        resultsList.classList.add('hidden');
        selectedStudent = null;
        studentCard.classList.add('hidden');
        updateProceedBtnState();
        return;
    }

    fetch(`/admin/cashiering/search?q=${encodeURIComponent(query)}`)
        .then(res => res.json())
        .then(data => {
            resultsList.innerHTML = '';
            if (data.length === 0) { resultsList.classList.add('hidden'); return; }

            data.forEach(student => {
                const li = document.createElement('li');
                li.textContent = `${student.student_id} - ${student.first_name} ${student.last_name}`;
                li.className = 'px-3 py-2 hover:bg-gray-100 cursor-pointer text-sm';
                li.addEventListener('click', () => {
                    searchInput.value = `${student.first_name} ${student.last_name} (${student.student_id})`;
                    resultsList.classList.add('hidden');
                    loadStudentDetails(student.id);
                });
                resultsList.appendChild(li);
            });
            resultsList.classList.remove('hidden');
        });
});

function loadStudentDetails(studentId) {
    fetch(`/admin/cashiering/student/${studentId}`)
        .then(res => res.json())
        .then(data => {
            selectedStudent = data.student;
            FEES = data.fees || [];

            PAID_FEES = (data.paid_fee_ids || []).map(id => Number(id));

            document.getElementById('cardStudentId').textContent = selectedStudent.student_id;
            document.getElementById('cardName').textContent =
                selectedStudent.first_name + ' ' + selectedStudent.last_name;
            document.getElementById('cardCourse').textContent =
                selectedStudent.course ?? '—';
            document.getElementById('cardYearSection').textContent =
                selectedStudent.year_level + ' - ' + selectedStudent.section;
            document.getElementById('cardEmail').textContent =
                selectedStudent.email ?? '—';

            studentCard.classList.remove('hidden');
            renderFees();
            resetPayment();
            cashierPanel.classList.remove('hidden');
        });
}

function renderFees() {
    feesList.innerHTML = '';

    if (FEES.length === 0) {
        feesList.innerHTML = `<p class="text-gray-500 text-sm">No approved fees available.</p>`;
        return;
    }

    FEES.forEach(fee => {
        const div = document.createElement('div');
        div.className = 'flex items-center justify-between text-sm';

        const amount = parseFloat(fee.amount) || 0;
        const isMandatory = fee.requirement_level === 'mandatory';
        const isPaid = PAID_FEES.includes(Number(fee.id));

        const checkedAttr = isMandatory && !isPaid ? 'checked' : '';
        const disabledAttr = isPaid ? 'disabled' : '';

        div.innerHTML = `
            <label class="flex items-center gap-2 ${isPaid ? 'text-gray-400' : ''}">
                <input 
                    type="checkbox" 
                    data-id="${fee.id}" 
                    data-amount="${amount}" 
                    class="feeCheckbox" 
                    ${checkedAttr}
                    ${disabledAttr}
                >
                ${fee.fee_name}
                <span class="text-xs text-gray-400">
                    (${fee.requirement_level})
                </span>
                ${isPaid ? '<span class="text-xs text-green-600 font-semibold ml-1">(PAID)</span>' : ''}
            </label>
            <span>₱ ${amount.toFixed(2)}</span>
        `;

        feesList.appendChild(div);
    });

    document.querySelectorAll('.feeCheckbox').forEach(cb => {
        cb.addEventListener('change', calculateTotal);
    });

    calculateTotal();
}

cashInput.addEventListener('input', calculateChange);

proceedBtn.addEventListener('click', () => {
    if (!selectedStudent) { alert('Select a student first.'); return; }

    const cashReceived = parseFloat(cashInput.value) || 0;
    const selectedFees = Array.from(document.querySelectorAll('.feeCheckbox:checked'))
        .map(cb => parseInt(cb.dataset.id));

    if (selectedFees.length === 0) { alert('Select at least one fee.'); return; }
    if (cashReceived < parseFloat(totalAmountEl.textContent)) { alert('Cash received is less than total.'); return; }

    fetch('/admin/cashiering/collect', {
        method: 'POST',
        headers: { 
            'Content-Type': 'application/json', 
            'X-CSRF-TOKEN': '{{ csrf_token() }}' 
        },
        body: JSON.stringify({
            student_id: selectedStudent.id,
            fee_ids: selectedFees,
            cash_received: cashReceived
        })
    })
    .then(res => {
        if (!res.ok) return res.json().then(data => { throw data; });
        return res.json();
    })
    .then(data => {
        alert(data.message || 'Payment collected successfully.');

        window.open(`/admin/cashiering/receipt/pdf/${data.payment_id}`, '_blank');

        selectedStudent = null;
        FEES = @json($fees);
        searchInput.value = '';
        studentCard.classList.add('hidden');
        resetPayment();
        updateProceedBtnState();
    })
    .catch(err => {
        alert(err.message || 'Something went wrong.');
    });
});

document.addEventListener('click', function (e) {
    if (!searchInput.contains(e.target) && !resultsList.contains(e.target)) {
        resultsList.classList.add('hidden');
    }
});
</script>
@endsection