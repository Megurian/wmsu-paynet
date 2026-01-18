@extends('layouts.dashboard')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')
<h2 class="text-2xl font-bold mb-4">Dashboard</h2>
<p>Welcome, {{ Auth::user()->name }}. </p>

<div class="mt-6 max-w-md">
    <label class="block text-sm font-medium text-gray-700 mb-1">Search Student</label>
    <input type="text" id="studentSearch" placeholder="Enter name or student ID"
        class="w-full border rounded-lg px-3 py-2 focus:ring-red-600 focus:border-red-600" autocomplete="off">
    
    <ul id="searchResults" class="border rounded mt-1 bg-white hidden max-h-60 overflow-auto"></ul>
</div>

<div id="studentCard" class="mt-6 hidden border rounded-lg bg-white p-4 shadow-sm">
    <h3 class="text-lg font-semibold mb-3">Student Information</h3>

    <div class="grid grid-cols-2 gap-4 text-sm">
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

<div id="cashierPanel" class="mt-6 hidden border rounded-lg bg-white p-4 shadow-sm">
    <h3 class="text-lg font-semibold mb-4">Payment Details</h3>

    {{-- FEES --}}
    <div class="mb-4">
        <p class="font-medium mb-2">Select Fees</p>
        <div id="feesList" class="space-y-2 text-sm"></div>
    </div>

    {{-- TOTAL --}}
    <div class="flex justify-between text-sm mb-2">
        <span>Total Amount</span>
        <span class="font-semibold">₱ <span id="totalAmount">0.00</span></span>
    </div>

    {{-- CASH INPUT --}}
    <div class="mt-4">
        <label class="block text-sm font-medium text-gray-700 mb-1">Cash Received</label>
        <input type="number" id="cashInput"
            class="w-full border rounded-lg px-3 py-2"
            min="0" step="0.01">
    </div>

    {{-- CHANGE --}}
    <div class="flex justify-between text-sm mt-3">
        <span>Change</span>
        <span class="font-semibold">₱ <span id="changeAmount">0.00</span></span>
    </div>

    {{-- ACTION --}}
    <button
        class="mt-4 w-full bg-red-700 hover:bg-red-800 text-white py-2 rounded-lg font-medium transition">
        Proceed Payment (Placeholder)
    </button>
</div>


<script>
const searchInput = document.getElementById('studentSearch');
const resultsList = document.getElementById('searchResults');
const studentCard = document.getElementById('studentCard');

searchInput.addEventListener('input', function () {
    const query = this.value.trim();

    if (!query) {
        resultsList.innerHTML = '';
        resultsList.classList.add('hidden');
        studentCard.classList.add('hidden');
        return;
    }

    fetch(`/college/students/search?q=${encodeURIComponent(query)}`)
        .then(res => res.json())
        .then(data => {
            resultsList.innerHTML = '';

            if (data.length === 0) {
                resultsList.classList.add('hidden');
                return;
            }

            data.forEach(student => {
                const li = document.createElement('li');
                li.textContent = `${student.name} (${student.student_id})`;
                li.className = 'px-3 py-2 hover:bg-gray-100 cursor-pointer';

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

function renderStudentCard(student) {
    document.getElementById('cardStudentId').textContent = student.student_id;
    document.getElementById('cardName').textContent = student.name;
    document.getElementById('cardCourse').textContent = student.course ?? '—';
    document.getElementById('cardYearSection').textContent =
        `${student.year ?? '—'} / ${student.section ?? '—'}`;
    document.getElementById('cardEmail').textContent = student.email ?? '—';

    studentCard.classList.remove('hidden');
}

// Hide dropdown on outside click
document.addEventListener('click', function (e) {
    if (!searchInput.contains(e.target) && !resultsList.contains(e.target)) {
        resultsList.classList.add('hidden');
    }
});
</script>
<script>
const FEES = [
    { id: 1, name: 'Registration Fee', amount: 300 },
    { id: 2, name: 'Library Fee', amount: 150 },
    { id: 3, name: 'Laboratory Fee', amount: 500 },
    { id: 4, name: 'Organization Fee', amount: 200 },
];

const cashierPanel = document.getElementById('cashierPanel');
const feesList = document.getElementById('feesList');
const totalAmountEl = document.getElementById('totalAmount');
const cashInput = document.getElementById('cashInput');
const changeAmountEl = document.getElementById('changeAmount');

function renderStudentCard(student) {
    document.getElementById('cardStudentId').textContent = student.student_id;
    document.getElementById('cardName').textContent = student.name;
    document.getElementById('cardCourse').textContent = student.course ?? '—';
    document.getElementById('cardYearSection').textContent =
        `${student.year ?? '—'} / ${student.section ?? '—'}`;
    document.getElementById('cardEmail').textContent = student.email ?? '—';

    studentCard.classList.remove('hidden');
    cashierPanel.classList.remove('hidden');

    renderFees();
    resetPayment();
}

function renderFees() {
    feesList.innerHTML = '';

    FEES.forEach(fee => {
        const div = document.createElement('div');
        div.className = 'flex items-center justify-between';

        div.innerHTML = `
            <label class="flex items-center gap-2">
                <input type="checkbox" data-amount="${fee.amount}" class="feeCheckbox">
                ${fee.name}
            </label>
            <span>₱ ${fee.amount.toFixed(2)}</span>
        `;

        feesList.appendChild(div);
    });

    document.querySelectorAll('.feeCheckbox').forEach(cb => {
        cb.addEventListener('change', calculateTotal);
    });
}

function calculateTotal() {
    let total = 0;

    document.querySelectorAll('.feeCheckbox:checked').forEach(cb => {
        total += parseFloat(cb.dataset.amount);
    });

    totalAmountEl.textContent = total.toFixed(2);
    calculateChange();
}

function calculateChange() {
    const total = parseFloat(totalAmountEl.textContent) || 0;
    const cash = parseFloat(cashInput.value) || 0;

    const change = cash - total;
    changeAmountEl.textContent = change >= 0 ? change.toFixed(2) : '0.00';
}

function resetPayment() {
    totalAmountEl.textContent = '0.00';
    changeAmountEl.textContent = '0.00';
    cashInput.value = '';
}

cashInput.addEventListener('input', calculateChange);
</script>


@endsection
