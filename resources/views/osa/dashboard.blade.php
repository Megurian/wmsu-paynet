@extends('layouts.dashboard')

@section('title', 'OSA Dashboard')
@section('page-title', 'OSA Dashboard')

@section('content')
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

        <!-- CARD -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-sm font-medium text-gray-500">
                Total Payments
            </h3>
            <p class="text-2xl font-bold text-gray-800 mt-2">
                ₱0.00
            </p>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-sm font-medium text-gray-500">
                Pending Requests
            </h3>
            <p class="text-2xl font-bold text-gray-800 mt-2">
                0
            </p>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-sm font-medium text-gray-500">
                Completed Transactions
            </h3>
            <p class="text-2xl font-bold text-gray-800 mt-2">
                0
            </p>
        </div>

    </div>
@endsection
