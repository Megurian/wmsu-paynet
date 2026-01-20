@extends('layouts.dashboard')

@section('title', 'Fees')
@section('page-title', 'USC Fees')


@section('content')
<div class="mb-8">
    <h2 class="text-3xl font-bold text-gray-800">{{ Auth::user()->name }} Setup of Fees</h2>
    <p class="text-sm text-gray-500 mt-1">
        Welcome, {{ Auth::user()->name }}. Here you can manage the fees associated with different colleges within the university.
    </p>
    <br>
    <button class="px-4 py-2 bg-red-700 text-white rounded hover:bg-red-800 transition">
            New Fee
    </button>
</div>

    <!-- Fees Section -->
    <div class="bg-white rounded shadow p-6">
    <h3 class="text-xl font-semibold mb-4">Fees</h3>
    <p class="text-gray-500 italic">Fees information for this organization will appear here.</p>

    <table class="w-full text-left border-collapse mt-4">
        <thead>
            <tr class="bg-gray-100">
                <th class="border px-4 py-2">Fee Name</th>
                <th class="border px-4 py-2">Amount</th>
                <th class="border px-4 py-2">Status</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="border px-4 py-2">CSC Fee</td>
                <td class="border px-4 py-2">₱0.00</td>
                <td class="border px-4 py-2">Approved</td>
            </tr>
            <tr>
                <td class="border px-4 py-2">Miscellaneous Fee</td>
                <td class="border px-4 py-2">₱0.00</td>
                <td class="border px-4 py-2">Pending Approval</td>
            </tr>
        </tbody>
    </table>
</div>
@endsection