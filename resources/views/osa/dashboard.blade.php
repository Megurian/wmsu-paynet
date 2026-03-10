@extends('layouts.dashboard')

@section('title','OSA Dashboard')
@section('page-title','Welcome to OSA Dashboard!')

@section('content')
<div class="space-y-8">

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="md:col-span-2 bg-gradient-to-r from-red-500 to-pink-600 text-white rounded-xl shadow-md p-6 flex flex-col gap-4">
            <h2 class="text-3xl font-bold">Hello, {{ Auth::user()->first_name }}!</h2>
            <p class="text-sm opacity-90 leading-relaxed">
                Welcome back! Monitor organizations, student enrollments, and fee collections university-wide.
            </p>
            <div class="flex flex-wrap gap-4 mt-2">
                <span class="bg-white text-red-600 px-3 py-1 rounded-full font-semibold shadow">Mother Orgs: {{ $totalMotherOrgs ?? 0 }}</span>
                <span class="bg-white text-red-600 px-3 py-1 rounded-full font-semibold shadow">Child Orgs: {{ $totalChildOrgs ?? 0 }}</span>
                <span class="bg-white text-red-600 px-3 py-1 rounded-full font-semibold shadow">Local College Orgs: {{ $totalLocalOrgs ?? 0 }}</span>
                <span class="bg-white text-red-600 px-3 py-1 rounded-full font-semibold shadow">Active Fees: {{ $totalFees ?? 0 }}</span>
                <span class="bg-white text-red-600 px-3 py-1 rounded-full font-semibold shadow">Students Enrolled: {{ $totalStudents ?? 0 }}</span>
                <span class="bg-white text-red-600 px-3 py-1 rounded-full font-semibold shadow">Students Paid: {{ $studentsPaid ?? 0 }}</span>
                <span class="bg-white text-red-600 px-3 py-1 rounded-full font-semibold shadow">Pending Students: {{ $studentsPending ?? 0 }}</span>
                <span class="bg-white text-red-600 px-3 py-1 rounded-full font-semibold shadow">Payments Collected: ₱ {{ number_format($totalPaymentsCollected ?? 0,2) }}</span>
            </div>
        </div>

<div class="md:col-span-1 bg-white rounded-xl shadow-md p-6 flex flex-col">
    <h3 class="text-lg font-semibold text-gray-700 mb-2">Fee Collection Progress</h3>
    <p class="text-sm text-gray-500 mb-3">Monitor approved fees and student payments</p>

    <div class="flex-1 overflow-y-auto space-y-3" style="max-height: 200px;">
        @foreach($fees as $fee)
        <div>
            <div class="flex justify-between text-sm font-medium mb-1">
                <div>
                    <span class="font-semibold">{{ $fee->fee_name }}</span>
                    <span class="text-xs text-gray-400 ml-2">
                        @if($fee->organization)
                            @if($fee->organization->college_id)
                                ( {{ $fee->organization->college->name ?? 'N/A' }})
                            @else
                                ( {{ $fee->organization->name }})
                            @endif
                        @else
                            (University-wide)
                        @endif
                    </span>
                </div>
                <span class="text-gray-500">{{ $fee->totalPaidCount ?? 0 }}/{{ $fee->totalStudents ?? 0 }} paid</span>
            </div>

            <div class="w-full bg-gray-200 rounded-full h-3 mb-1">
                <div class="bg-gradient-to-r from-blue-500 to-indigo-600 h-3 rounded-full transition-all duration-500" 
                    style="width: {{ min(100, $fee->progress ?? 0) }}%">
                </div>
            </div>
            <span class="text-xs text-gray-400">{{ ($fee->totalStudents ?? 0) - ($fee->totalPaidCount ?? 0) }} students pending</span>
        </div>
        @endforeach
    </div>
</div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-5 gap-6">
        <div class="p-5 rounded-xl shadow-md bg-gradient-to-r from-emerald-500 to-green-600 text-white hover:shadow-lg transition">
            <p class="text-sm opacity-80">Total Payments Collected</p>
            <p class="text-3xl font-bold mt-1">₱ {{ number_format($totalPaymentsCollected ?? 0,2) }}</p>
            <p class="text-xs mt-1 opacity-70">All organizations combined</p>
        </div>
        <div class="p-5 rounded-xl shadow-md bg-gradient-to-r from-blue-500 to-indigo-600 text-white hover:shadow-lg transition">
            <p class="text-sm opacity-80">Students Paid</p>
            <p class="text-3xl font-bold mt-1">{{ $studentsPaid ?? 0 }}</p>
            <p class="text-xs mt-1 opacity-70">Students who completed payment</p>
        </div>
        <div class="p-5 rounded-xl shadow-md bg-gradient-to-r from-red-500 to-pink-600 text-white hover:shadow-lg transition">
            <p class="text-sm opacity-80">Pending Students</p>
            <p class="text-3xl font-bold mt-1">{{ $studentsPending ?? 0 }}</p>
            <p class="text-xs mt-1 opacity-70">Students yet to pay</p>
        </div>
        <div class="p-5 rounded-xl shadow-md bg-gradient-to-r from-purple-500 to-violet-600 text-white hover:shadow-lg transition">
            <p class="text-sm opacity-80">Child Organizations</p>
            <p class="text-3xl font-bold mt-1">{{ $totalChildOrgs ?? 0 }}</p>
            <p class="text-xs mt-1 opacity-70">Units under your supervision</p>
        </div>
        <div class="p-5 rounded-xl shadow-md bg-gradient-to-r from-yellow-400 to-orange-500 text-white hover:shadow-lg transition">
            <p class="text-sm opacity-80">Total Active Fees</p>
            <p class="text-3xl font-bold mt-1">{{ $totalFees ?? 0 }}</p>
            <p class="text-xs mt-1 opacity-70">All fees currently available</p>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white rounded-xl shadow-md p-6 md:col-span-2 flex flex-col">
            <h3 class="text-lg font-semibold text-gray-700 mb-4">Organization Performance</h3>
            <p class="text-sm text-gray-500 mb-3">Monitor student payment status per unit</p>

            <div class="flex-1 overflow-y-auto" style="max-height: 350px;">
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-gray-100 text-gray-600">
                            <tr>
                                <th class="p-3 text-left">Organization</th>
                                <th class="p-3 text-center">Students</th>
                                <th class="p-3 text-center">Paid</th>
                                <th class="p-3 text-center">Pending</th>
                                <th class="p-3 text-right">Collected</th>
                                <th class="p-3 text-center">Completion %</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            @foreach($organizations as $org)
                            <tr class="hover:bg-gray-50 transition">
                                <td class="p-3 font-medium text-gray-700">{{ $org->name }}</td>
                                <td class="p-3 text-center">{{ $org->totalStudents ?? 0 }}</td>
                                <td class="p-3 text-center text-green-600 font-semibold">{{ $org->studentsPaid ?? 0 }}</td>
                                <td class="p-3 text-center text-red-500 font-semibold">{{ ($org->totalStudents ?? 0) - ($org->studentsPaid ?? 0) }}</td>
                                <td class="p-3 text-right font-bold text-indigo-600">₱ {{ number_format($org->totalPayments ?? 0,2) }}</td>
                                <td class="p-3 text-center font-semibold">{{ $org->completionRate ?? 0 }}%</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-md p-6 flex flex-col h-[450px]">
            <h3 class="text-lg font-semibold text-gray-700 mb-4">Recent Transactions</h3>
            <p class="text-sm text-gray-500 mb-3">Latest student payments</p>
            <div class="flex flex-col gap-2 overflow-y-auto pr-2">
                @foreach($recentPayments as $payment)
                <div class="flex justify-between items-center bg-gray-50 px-3 py-2 rounded-lg shadow-sm hover:shadow-md transition">
                    <div>
                        <p class="font-medium text-gray-700 text-sm">{{ $payment->student->first_name }} {{ $payment->student->last_name }}</p>
                        <p class="text-xs text-gray-500">{{ $payment->created_at->format('M d, Y') }}</p>
                    </div>
                    <div class="text-green-600 font-semibold text-sm">
                        ₱ {{ number_format($payment->amount_due,2) }}
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
        labels: @json($paymentTrend->pluck('date')),
        datasets: [{
            label: 'Payments Collected',
            data: @json($paymentTrend->pluck('total')),
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