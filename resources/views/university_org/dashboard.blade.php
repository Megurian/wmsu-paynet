@extends('layouts.dashboard')

@section('title','Dashboard')
@section('page-title','Welcome to Your Dashboard!')

@section('content')

<div class="space-y-8">

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="md:col-span-2 bg-gradient-to-r from-red-500 to-pink-600 text-white rounded-xl shadow-md p-6 flex flex-col gap-4">
            <h2 class="text-3xl font-bold">Hello, {{ Auth::user()->first_name }}! </h2>
            <p class="text-sm opacity-90 leading-relaxed">
                Welcome back to your dashboard! Here you can quickly see how your child organizations are performing, track student payments, and monitor overall fee collection. Stay on top of your units and make data-driven decisions with ease.
            </p>
            <div class="flex flex-wrap gap-4 mt-2">
                <span class="bg-white text-red-600 px-3 py-1 rounded-full font-semibold shadow">Total Students: {{ $totalStudents ?? 0 }}</span>
                <span class="bg-white text-red-600 px-3 py-1 rounded-full font-semibold shadow">Total Payments: ₱ {{ number_format($totalPaymentsCollected ?? 0,2) }}</span>
                <span class="bg-white text-red-600 px-3 py-1 rounded-full font-semibold shadow">Pending Payments: {{ $pendingStudents ?? 0 }}</span>
            </div>
        </div>
        <div class="md:col-span-1 bg-white rounded-xl shadow-md p-6">
            <h3 class="text-lg font-semibold text-gray-700 mb-2">Fee Collection Progress</h3>
            <p class="text-sm text-gray-500 mb-3">Monitor approved fees and student payments</p>

            @foreach($fees as $fee)
            <div class="mb-2">
                <div class="flex justify-between text-sm font-medium mb-1">
                    <span>{{ $fee['name'] }}</span>
                    <span class="text-gray-500">{{ $fee['paid'] }} students paid</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-3">
                    <div class="bg-gradient-to-r from-blue-500 to-indigo-600 h-3 rounded-full transition-all duration-500" style="width: {{ $fee['percent'] }}%">
                    </div>
                </div>
                <div class="flex justify-between text-xs text-gray-500 mt-1">
                    <span>{{ $fee['paid'] }} paid</span>
                    <span>{{ $fee['expected'] }} total</span>
                </div>
            </div>
            @endforeach
        </div>

    </div>

    <div class="mt-6"></div>
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6">
        <div class="p-5 rounded-xl shadow-md bg-gradient-to-r from-emerald-500 to-green-600 text-white hover:shadow-lg transition relative">
            <div class="flex justify-between items-center">
                <div>
                    <p class="text-sm opacity-80">Total Collected</p>
                    <p class="text-3xl font-bold mt-1">₱ {{ number_format($totalPaymentsCollected ?? 0,2) }}</p>
                    <p class="text-xs mt-1 opacity-70">All child organizations combined</p>
                </div>
                <div class="text-4xl opacity-80"></div>
            </div>
        </div>
        <div class="p-5 rounded-xl shadow-md bg-gradient-to-r from-blue-500 to-indigo-600 text-white hover:shadow-lg transition">
            <div class="flex justify-between items-center">
                <div>
                    <p class="text-sm opacity-80">Students Paid</p>
                    <p class="text-3xl font-bold mt-1">{{ $totalStudentsPaid ?? 0 }}</p>
                    <p class="text-xs mt-1 opacity-70">Students who completed payment</p>
                </div>
                <div class="text-4xl opacity-80"></div>
            </div>
        </div>
        <div class="p-5 rounded-xl shadow-md bg-gradient-to-r from-red-500 to-pink-600 text-white hover:shadow-lg transition">
            <div class="flex justify-between items-center">
                <div>
                    <p class="text-sm opacity-80">Pending Students</p>
                    <p class="text-3xl font-bold mt-1">{{ $pendingStudents ?? 0 }}</p>
                    <p class="text-xs mt-1 opacity-70">Students yet to pay</p>
                </div>
                <div class="text-4xl opacity-80"></div>
            </div>
        </div>
        <div class="p-5 rounded-xl shadow-md bg-gradient-to-r from-purple-500 to-violet-600 text-white hover:shadow-lg transition">
            <div class="flex justify-between items-center">
                <div>
                    <p class="text-sm opacity-80">Child Organizations</p>
                    <p class="text-3xl font-bold mt-1">{{ $totalChildOrgs ?? 0 }}</p>
                    <p class="text-xs mt-1 opacity-70">Units under your supervision</p>
                </div>
                <div class="text-4xl opacity-80"></div>
            </div>
        </div>
    </div>

       <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white rounded-xl shadow-md p-6 md:col-span-2">
            <h3 class="text-lg font-semibold text-gray-700 mb-4">Child Organization Performance</h3>
            <p class="text-sm text-gray-500 mb-3">See how each unit is doing with student payments</p>

            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-100 text-gray-600">
                        <tr>
                            <th class="p-3 text-left">Organization</th>
                            <th class="p-3 text-center">Students</th>
                            <th class="p-3 text-center">Paid</th>
                            <th class="p-3 text-center">Pending</th>
                            <th class="p-3 text-right">Collected</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        @foreach($childOrgPerformance as $org)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="p-3 font-medium text-gray-700">{{ $org['name'] }}</td>
                            <td class="p-3 text-center"><span class="px-2 py-1 rounded bg-gray-100">{{ $org['students'] }}</span></td>
                            <td class="p-3 text-center text-green-600 font-semibold">{{ $org['paid'] }}</td>
                            <td class="p-3 text-center text-red-500 font-semibold">{{ $org['pending'] }}</td>
                            <td class="p-3 text-right font-bold text-indigo-600">₱ {{ number_format($org['payments'],2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-md p-6 flex flex-col h-[400px]">
            <h3 class="text-lg font-semibold text-gray-700 mb-4">Recent Transactions</h3>
            <p class="text-sm text-gray-500 mb-3">Latest payments made by students</p>

            <div class="flex flex-col gap-2 overflow-y-auto pr-2">
                @foreach($recentPayments as $payment)
                <div class="flex justify-between items-center bg-gray-50 px-3 py-2 rounded-lg shadow-sm hover:shadow-md transition">
                    <div>
                        <p class="font-medium text-gray-700 text-sm">{{ $payment->student->first_name }} {{ $payment->student->last_name }}</p>
                        <p class="text-xs text-gray-500">{{ $payment->created_at->format('M d, Y') }}</p>
                    </div>
                    <div class="text-green-600 font-semibold text-sm">
                        ₱ {{ number_format($payment->amount_paid ?? 0, 2) }}
                    </div>
                </div>
                @endforeach
            </div>
        </div>

    </div>
    <div class="bg-white rounded-xl shadow-md p-6">

        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold text-gray-700">Payment Trend</h3>
            <span class="text-xs text-gray-400">Track daily collections over time</span>
        </div>

        <canvas id="paymentChart" height="100"></canvas>
    </div>


</div>


<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
new Chart(document.getElementById('paymentChart'), {
    type: 'line',
    data: {
        labels: @json($dailyPaymentLabels),
        datasets: [{
            label: 'Payments Collected',
            data: @json($dailyPaymentData),
            borderColor: '#4f46e5',
            backgroundColor: 'rgba(99,102,241,0.15)',
            borderWidth: 3,
            fill: true,
            tension: 0.35,
            pointRadius: 4
        }]
    },
    options: {
        plugins: { legend: { display: false } },
        scales: { y: { beginAtZero: true } }
    }
})
</script>

@endsection
