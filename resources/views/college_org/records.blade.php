@extends('layouts.dashboard')

@section('title', 'Records')
@section('page-title', 'Records')

@section('content')
    <div class="mb-8">
        <h2 class="text-3xl font-bold text-gray-800">Records</h2>
        <p class="text-sm text-gray-500 mt-1">
            Welcome, {{ Auth::user()->name }}. Here you can manage the record of your organization.
        </p>
    </div>
 
    <!-- Records Section -->
    <div class="mb-8">
        <div class="mt-4 bg-white p-4 rounded-lg shadow-sm border border-gray-200">
            <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
            <!-- Search Bar -->
            <div class="relative">
                <input type="text" id="search" placeholder="Search..." 
                    class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" />
                    </svg>
                </div>
            </div>

            <!-- Course Dropdown -->
            <div>
                <select class="w-full border border-gray-300 rounded-md py-2 px-3 focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500">
                    <option value="">Course</option>
                    <option value="CS">Computer Science</option>
                    <option value="IT">Information Technology</option>
                    <option value="ACT">Associate in Computer Technology</option>
                    <option value="IS">Information Systems</option>
                </select>
            </div>

            <!-- Year Level Dropdown -->
            <div>
                <select class="w-full border border-gray-300 rounded-md py-2 px-3 focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500">
                    <option value="">Year Level</option>
                    <option value="1">1st Year</option>
                    <option value="2">2nd Year</option>
                    <option value="3">3rd Year</option>
                    <option value="4">4th Year</option>
                    <option value="5">5th Year</option>
                </select>
            </div>

            <!-- Section Dropdown -->
            <div>
                <select class="w-full border border-gray-300 rounded-md py-2 px-3 focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500">
                    <option value="">Section</option>
                    <option value="A">A</option>
                    <option value="B">B</option>
                    <option value="C">C</option>
                    <option value="D">D</option>
                </select>
            </div>

            <!-- Date Picker -->
            <div>
                <input type="date" class="w-full border border-gray-300 rounded-md py-2 px-3 focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500">
            </div>
        </div>
    </div>
</div>

    <div class="bg-white rounded-lg shadow-md p-6">
        <div class="overflow-x-auto">
            <table class="min-w-full bg-white">
                <thead>
                    <tr class="bg-gray-100">
                        <th class="py-2 px-4 border-b text-left">Student ID</th>
                        <th class="py-2 px-4 border-b text-left">Name</th>
                        <th class="py-2 px-4 border-b text-left">Fee</th>
                        <th class="py-2 px-4 border-b text-left">Amount</th>
                        <th class="py-2 px-4 border-b text-left">Course</th>
                        <th class="py-2 px-4 border-b text-left">Year</th>
                        <th class="py-2 px-4 border-b text-left">Section</th>
                        <th class="py-2 px-4 border-b text-left">Date</th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="hover:bg-gray-50">
                        <td class="py-2 px-4 border-b">202187689</td>
                        <td class="py-2 px-4 border-b">Alfaith Mae M. Luzon</td>
                        <td class="py-2 px-4 border-b">Venom</td>
                        <td class="py-2 px-4 border-b">70</td>
                        <td class="py-2 px-4 border-b">CS</td>
                        <td class="py-2 px-4 border-b">2</td>
                        <td class="py-2 px-4 border-b">B</td>
                        <td class="py-2 px-4 border-b">12-12-2025</td>
                    </tr>
                    <tr class="hover:bg-gray-50">
                        <td class="py-2 px-4 border-b">202256789</td>
                        <td class="py-2 px-4 border-b">Veronica B. Sanchez</td>
                        <td class="py-2 px-4 border-b">Venom</td>
                        <td class="py-2 px-4 border-b">70</td>
                        <td class="py-2 px-4 border-b">CS</td>
                        <td class="py-2 px-4 border-b">4</td>
                        <td class="py-2 px-4 border-b">D</td>
                        <td class="py-2 px-4 border-b">12-09-2025</td>
                    </tr>
                    <tr class="hover:bg-gray-50">
                        <td class="py-2 px-4 border-b">202262755</td>
                        <td class="py-2 px-4 border-b">Asshelee Jane F. Alejo</td>
                        <td class="py-2 px-4 border-b">Venom</td>
                        <td class="py-2 px-4 border-b">70</td>
                        <td class="py-2 px-4 border-b">ACT</td>
                        <td class="py-2 px-4 border-b">1</td>
                        <td class="py-2 px-4 border-b">A</td>
                        <td class="py-2 px-4 border-b">12-09-2025</td>
                    </tr>
                    <tr class="hover:bg-gray-50">
                        <td class="py-2 px-4 border-b">201982567</td>
                        <td class="py-2 px-4 border-b">April Rose Alvarez</td>
                        <td class="py-2 px-4 border-b">Venom</td>
                        <td class="py-2 px-4 border-b">70</td>
                        <td class="py-2 px-4 border-b">IT</td>
                        <td class="py-2 px-4 border-b">5</td>
                        <td class="py-2 px-4 border-b">c</td>
                        <td class="py-2 px-4 border-b">12-06-2025</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
@endsection

