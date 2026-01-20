@extends('layouts.dashboard')

@section('title', 'Remittance')
@section('page-title', ($organization?->org_code ?? 'Organization') . ' Remittances')


@section('content')
<div class="mb-8">
    <h2 class="text-3xl font-bold text-gray-800">USC Remittance</h2>
    <p class="text-sm text-gray-500 mt-1">
        Welcome, {{ Auth::user()->name }}. Here you can manage the remittance details for different colleges within the university.
    </p>
</div>

<div class="container mx-auto px-0">

    <!-- Setup of Fees Section -->
    <div class="bg-white rounded-xl shadow-md border border-gray-200 p-6 mb-10">

        <!-- Add New Fee Section -->
        <div class="border-t pt-4">
            <h3 class="text-lg font-semibold text-gray-800 mb-1">Remittance</h3> <br>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                <div>
                    <label class="block text-gray-700 text-sm font-medium mb-2" for="college">
                        College
                    </label>
                    <div class="relative">
                        <select class="shadow border-gray-300 rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="college">
                            <option value="" disabled selected>Select College</option>
                            <option value="CAS">College of Arts and Sciences</option>
                            <option value="CIT">College of Information Technology</option>
                            <option value="CBM">College of Business and Management</option>
                            <option value="CCS">College of Computing Studies</option>
                        </select>
                    </div>
                </div>
                <div>
                    <label class="block text-gray-700 text-sm font-medium mb-2" for="Amount">
                        Amount Received
                    </label>
                    <input class="shadow appearance-none border-gray-300 rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" 
                           id="Amount" type="number" placeholder="Enter Amount Received">
                </div>
                <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Date Received
                </label>
                <input type="date" name="dtreceived"
                       class="w-full rounded-lg border-gray-300 focus:border-red-600 focus:ring-red-600"
                       required>
            </div>
            </div>
            <div class="flex justify-end">
                <button type="submit"
                    class="bg-red-700 hover:bg-red-800 text-white px-6 py-2.5 rounded-lg font-medium transition">
               Save
                </button>
            </div>
        </div>
    </div>

    <!-- Records Section -->
    <div class="mb-8 flex justify-between items-center">
        <h2 class="text-2xl font-bold text-gray-800">Records</h2>
        <div class="flex items-center space-x-4">
            <div class="flex items-center">
                <label for="date-filter" class="block text-sm font-medium text-gray-700 mr-2">Filter by Date:</label>
                <input type="date" id="date-filter" class="rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500">
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-md p-6">
        <div class="overflow-x-auto">
            <table class="min-w-full bg-white">
                <thead>
                    <tr class="bg-gray-100">
                        <th class="py-2 px-4 border-b text-left">Code</th>
                        <th class="py-2 px-4 border-b text-left">College</th>
                        <th class="py-2 px-4 border-b text-left">Amount</th>
                        <th class="py-2 px-4 border-b text-left">Date</th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="hover:bg-gray-50">
                        <td class="py-2 px-4 border-b">CAS</td>
                        <td class="py-2 px-4 border-b">College of Arts and Sciences</td>
                        <td class="py-2 px-4 border-b">3,000</td>
                        <td class="py-2 px-4 border-b">12-03-2025</td>
                    </tr>
                    <tr class="hover:bg-gray-50">
                        <td class="py-2 px-4 border-b">CIT</td>
                        <td class="py-2 px-4 border-b">College of Information Technology</td>
                        <td class="py-2 px-4 border-b">6,000</td>
                        <td class="py-2 px-4 border-b">12-03-2025</td>
                    </tr>
                    <tr class="hover:bg-gray-50">
                        <td class="py-2 px-4 border-b">CBM</td>
                        <td class="py-2 px-4 border-b">College of Business and Management</td>
                        <td class="py-2 px-4 border-b">3,500</td>
                        <td class="py-2 px-4 border-b">12-05-2025</td>
                    </tr>
                    <tr class="hover:bg-gray-50">
                        <td class="py-2 px-4 border-b">CCS</td>
                        <td class="py-2 px-4 border-b">College of Computing Studies</td>
                        <td class="py-2 px-4 border-b">2,000</td>
                        <td class="py-2 px-4 border-b">12-04-2025</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection