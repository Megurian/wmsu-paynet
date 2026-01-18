@extends('layouts.dashboard')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')
<div class="max-w-6xl mx-auto space-y-6">

    <div>
        <h2 class="text-2xl font-semibold">Cashier</h2>
        <p class="text-sm text-gray-500">Welcome, {{ Auth::user()->name }}</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        <div class="bg-white border rounded-lg p-4 space-y-4">

            <div>
                <label class="text-sm font-medium text-gray-700">Search Student</label>
                <input
                    type="text"
                    id="studentSearch"
                    placeholder="Name or Student ID"
                    class="mt-1 w-full border rounded-md px-3 py-2 text-sm focus:ring-red-600 focus:border-red-600"
                    autocomplete="off"
                >
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
             class="hidden bg-white border rounded-lg p-4 flex flex-col justify-between">

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

            <button
                class="mt-4 w-full bg-red-700 hover:bg-red-800 text-white py-2 rounded-md text-sm font-medium transition">
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

const FEES = [
    { id: 1, name: 'Registration Fee', amount: 300 },
    { id: 2, name: 'Membership Fee', amount: 150 },
    { id: 3, name: 'Organization Fee', amount: 500 },
];

const feesList = document.getElementById('feesList');
const totalAmountEl = document.getElementById('totalAmount');
const cashInput = document.getElementById('cashInput');
const changeAmountEl = document.getElementById('changeAmount');

// --- STUDENT SEARCH ---
searchInput.addEventListener('input', function () {
    const query = this.value.trim();
    if (!query) {
        resultsList.innerHTML = '';
        resultsList.classList.add('hidden');
        studentCard.classList.add('hidden');
        cashierPanel.classList.add('hidden');
        return;
    }

    fetch(`/college/students/search?q=${encodeURIComponent(query)}`)
        .then(res => res.json())
        .then(data => {
            resultsList.innerHTML = '';
            if (data.length === 0) { resultsList.classList.add('hidden'); return; }

            data.forEach(student => {
                const li = document.createElement('li');
                li.textContent = ` ${student.student_id} ${student.name}`;
                li.className = 'px-3 py-2 hover:bg-gray-100 cursor-pointer text-md uppercase ';
                li.addEventListener('click', () => {
                    searchInput.value = `${student.name} (${student.student_id})`;
                    resultsList.classList.add('hidden');
                    renderStudentCard(student);
                });
                resultsList.appendChild(li);
            });
            resultsList.classList.remove('hidden');
        });
});

// --- RENDER STUDENT + CASHIER PANEL ---
function renderStudentCard(student) {
    document.getElementById('cardStudentId').textContent = student.student_id;
    document.getElementById('cardName').textContent = student.name;
    document.getElementById('cardCourse').textContent = student.course ?? '—';
    document.getElementById('cardYearSection').textContent =
        `${student.year ?? '—'} ${student.section ?? '—'}`;
    document.getElementById('cardEmail').textContent = student.email ?? '—';

    studentCard.classList.remove('hidden');
    cashierPanel.classList.remove('hidden');

    renderFees();
    resetPayment();
}

// --- RENDER FEES ---
function renderFees() {
    feesList.innerHTML = '';
    FEES.forEach(fee => {
        const div = document.createElement('div');
        div.className = 'flex items-center justify-between text-sm';
        div.innerHTML = `
            <label class="flex items-center gap-2">
                <input type="checkbox" data-amount="${fee.amount}" class="feeCheckbox">
                ${fee.name}
            </label>
            <span>₱ ${fee.amount.toFixed(2)}</span>
        `;
        feesList.appendChild(div);
    });
    document.querySelectorAll('.feeCheckbox').forEach(cb => cb.addEventListener('change', calculateTotal));
}

// --- CALCULATE TOTAL ---
function calculateTotal() {
    let total = 0;
    document.querySelectorAll('.feeCheckbox:checked').forEach(cb => {
        total += parseFloat(cb.dataset.amount);
    });
    totalAmountEl.textContent = total.toFixed(2);
    calculateChange();
}

// --- CALCULATE CHANGE ---
function calculateChange() {
    const total = parseFloat(totalAmountEl.textContent) || 0;
    const cash = parseFloat(cashInput.value) || 0;
    const change = cash - total;
    changeAmountEl.textContent = change >= 0 ? change.toFixed(2) : '0.00';
}

// --- RESET PAYMENT ---
function resetPayment() {
    totalAmountEl.textContent = '0.00';
    changeAmountEl.textContent = '0.00';
    cashInput.value = '';
}

cashInput.addEventListener('input', calculateChange);

document.addEventListener('click', function (e) {
    if (!searchInput.contains(e.target) && !resultsList.contains(e.target)) {
        resultsList.classList.add('hidden');
    }
});
</script>
@endsection
