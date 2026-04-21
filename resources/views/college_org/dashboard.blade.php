@extends('layouts.dashboard')

@section('title','Dashboard')
@section('page-title','College Organization Dashboard')

@section('content')

<div class="space-y-8">

    <!-- HERO -->
    <div class="bg-gradient-to-r from-indigo-600 to-blue-600 text-white rounded-xl shadow-md p-6">
        <h2 class="text-3xl font-bold">
            Welcome, {{ Auth::user()->first_name }}
        </h2>

        <p class="text-sm opacity-90 mt-2">
            College Organization Dashboard — manage officers, fees, and student payments within your organization.
        </p>

        <div class="flex gap-3 mt-4 flex-wrap">
            <span class="bg-white text-indigo-600 px-3 py-1 rounded-full font-semibold">
                Org: {{ $org->name }}
            </span>

            <span class="bg-white text-indigo-600 px-3 py-1 rounded-full font-semibold">
                Total Collected: ₱ {{ number_format($totalPaymentsCollected,2) }}
            </span>

            <span class="bg-white text-indigo-600 px-3 py-1 rounded-full font-semibold">
                Paid Students: {{ $totalStudentsPaid }}
            </span>
        </div>
    </div>

    <!-- STATS -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">

        <div class="bg-white p-5 rounded-xl shadow">
            <p class="text-sm text-gray-500">Total Collected</p>
            <p class="text-2xl font-bold text-green-600">
                ₱ {{ number_format($totalPaymentsCollected,2) }}
            </p>
        </div>

        <div class="bg-white p-5 rounded-xl shadow">
            <p class="text-sm text-gray-500">Students Paid</p>
            <p class="text-2xl font-bold text-blue-600">
                {{ $totalStudentsPaid }}
            </p>
        </div>

        <div class="bg-white p-5 rounded-xl shadow">
            <p class="text-sm text-gray-500">Pending Students</p>
            <p class="text-2xl font-bold text-red-500">
                {{ $pendingStudents }}
            </p>
        </div>

        <div class="bg-white p-5 rounded-xl shadow">
            <p class="text-sm text-gray-500">College Orgs</p>
            <p class="text-2xl font-bold text-purple-600">
                {{ $totalChildOrgs }}
            </p>
        </div>

    </div>

    <!-- RECENT PAYMENTS -->
    <div class="bg-white rounded-xl shadow p-6">
        <h3 class="text-lg font-semibold mb-4">Recent Payments</h3>

        <div class="space-y-2">
            @forelse($recentPayments as $payment)
                <div class="flex justify-between p-3 bg-gray-50 rounded-lg">
                    <div>
                        <p class="font-medium">
                            {{ $payment->student->first_name ?? '' }}
                            {{ $payment->student->last_name ?? '' }}
                        </p>
                        <p class="text-xs text-gray-500">
                            {{ $payment->created_at->format('M d, Y') }}
                        </p>
                    </div>

                    <div class="font-bold text-green-600">
                        ₱ {{ number_format($payment->amount_due,2) }}
                    </div>
                </div>
            @empty
                <p class="text-gray-400">No payments yet.</p>
            @endforelse
        </div>
    </div>

    <!-- CHART -->
    <div class="bg-white rounded-xl shadow p-6">
        <h3 class="text-lg font-semibold mb-4">Payment Trend</h3>
        <canvas id="paymentChart"></canvas>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
new Chart(document.getElementById('paymentChart'), {
    type: 'line',
    data: {
        labels: @json($dailyPaymentLabels),
        datasets: [{
            label: 'Payments',
            data: @json($dailyPaymentData),
            borderColor: '#4f46e5',
            backgroundColor: 'rgba(79,70,229,0.15)',
            fill: true,
            tension: 0.4
        }]
    },
    options: {
        plugins: { legend: { display: false } },
        scales: { y: { beginAtZero: true } }
    }
});
</script>

@endsection