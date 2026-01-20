@extends('layouts.dashboard')

@section('title', content: ' Details')
@section('page-title', ' Overview')

@section('content')

<a href="" class="inline-block mb-4 px-4 py-2 bg-gray-200 text-gray-800 rounded hover:bg-gray-300 transition">
    &larr; Back to Offices
</a>

<!-- Organization Header -->
<div class="bg-white rounded shadow p-6 flex items-center space-x-6 mb-6">
    <div class="w-24 h-24 flex-shrink-0">
            <div class="w-full h-full bg-gray-100 flex items-center justify-center rounded-full text-gray-400 italic border">
                No Logo
            </div>
    </div>
    <div>
        <h2 class="text-2xl font-bold">Student Council</h2>
        <p class="text-gray-600"> <span class="font-medium">SC001</span></p>
        <p class="text-gray-600"> <span class="font-medium"> College-based
        </span></p>
    </div>
</div>

<!-- Admin Section -->
<div class="bg-white rounded shadow p-6 mb-6">
    <h3 class="text-xl font-semibold mb-4">Office Admin</h3>
        <div class="border rounded p-4 flex flex-col space-y-1">
            <p class="font-semibold">Naila Taji</p>
            <p class="text-gray-600 text-sm">naila.admin@gmail.com</p>
        </div>
</div>

<div class="bg-white rounded shadow p-6">
    <h3 class="text-xl font-semibold mb-4">Fees</h3>
    <p class="text-gray-500 italic">Fees information for this organization will appear here.</p>

    <table class="w-full text-left border-collapse mt-4">
        <thead>
            <tr class="bg-gray-100">
                <th class="border px-4 py-2">Fee Name</th>
                <th class="border px-4 py-2">Amount</th>
                <th class="border px-4 py-2">Description</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="border px-4 py-2">Tuition Fee</td>
                <td class="border px-4 py-2">₱0.00</td>
                <td class="border px-4 py-2">Placeholder description</td>
            </tr>
            <tr>
                <td class="border px-4 py-2">Miscellaneous Fee</td>
                <td class="border px-4 py-2">₱0.00</td>
                <td class="border px-4 py-2">Placeholder description</td>
            </tr>
        </tbody>
    </table>
</div>

@endsection
