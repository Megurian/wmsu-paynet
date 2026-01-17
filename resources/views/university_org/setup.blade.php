@extends('layouts.dashboard')

@section('title', 'USC Setup')
@section('page-title', 'USC Setup')


@section('content')
<div class="mb-8">
    <h2 class="text-3xl font-bold text-gray-800">USC Setup of Fees</h2>
    <p class="text-sm text-gray-500 mt-1">
        Welcome, {{ Auth::user()->name }}. Here you can manage the fees associated with different colleges within the university.
    </p>
</div>

<div class="container mx-auto px-0">

    <!-- Setup of Fees Section -->
    <div class="bg-white rounded-xl shadow-md border border-gray-200 p-6 mb-10">

        <!-- Add New Fee Section -->
        <div class="border-t pt-4">
            <h3 class="text-lg font-semibold text-gray-800 mb-1">Add New Fee</h3> <br>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                <div>
                    <label class="block text-gray-700 text-sm font-medium mb-2" for="feeName">
                        Fee Name
                    </label>
                    <input class="shadow appearance-none border-gray-300 rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" 
                           id="feeName" type="text" placeholder="Enter fee name">
                </div>
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
                    <label class="block text-gray-700 text-sm font-medium mb-2" for="account">
                        Admin Account
                    </label>
                    <input class="shadow appearance-none border-gray-300 rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" 
                           id="account" type="text" placeholder="Create admin gmail">
                </div>
            </div>
            <div class="flex justify-end">
                <button type="submit"
                    class="bg-red-700 hover:bg-red-800 text-white px-6 py-2.5 rounded-lg font-medium transition">
               New Fee
                </button>
            </div>
        </div>
    </div>

    <!-- Colleges Section -->
    <div class="mb-8">
        <h2 class="text-xl font-semibold text-gray-800 mb-4" >Colleges</h2>
    </div>
    
    <div class="bg-white rounded-lg shadow-md p-6">
        <div class="overflow-x-auto">
            <table class="min-w-full bg-white">
                <thead>
                    <tr class="bg-gray-100">
                        <th class="py-2 px-4 border-b text-left">Code</th>
                        <th class="py-2 px-4 border-b text-left">College</th>
                        <th class="py-2 px-4 border-b text-left">Account</th>
                        <th class="py-2 px-4 border-b text-left">Fee</th>
                        <th class="py-2 px-4 border-b text-left">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="hover:bg-gray-50">
                        <td class="py-2 px-4 border-b">CAS</td>
                        <td class="py-2 px-4 border-b">College of Arts and Sciences</td>
                        <td class="py-2 px-4 border-b">csc.cas.admin.gmail.com</td>
                        <td class="py-2 px-4 border-b">CSC Fee</td>
                        <td class="py-2 px-4 border-b">
                            <span class="px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-800">Inactive</span>
                        </td>
                    </tr>
                    <tr class="hover:bg-gray-50">
                        <td class="py-2 px-4 border-b">CIT</td>
                        <td class="py-2 px-4 border-b">College of Information Technology</td>
                        <td class="py-2 px-4 border-b">csc.cit.admin.gmail.com</td>
                        <td class="py-2 px-4 border-b">CSC Fee</td>
                        <td class="py-2 px-4 border-b">
                            <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800">Active</span>
                        </td>
                    </tr>
                    <tr class="hover:bg-gray-50">
                        <td class="py-2 px-4 border-b">CBM</td>
                        <td class="py-2 px-4 border-b">College of Business and Management</td>
                        <td class="py-2 px-4 border-b">csc.cbm.admin.gmail.com</td>
                        <td class="py-2 px-4 border-b">CSC Fee</td>
                        <td class="py-2 px-4 border-b">
                            <span class="px-2 py-1 text-xs rounded-full bg-yellow-100 text-yellow-800">Pending</span>
                        </td>
                    </tr>
                    <tr class="hover:bg-gray-50">
                        <td class="py-2 px-4 border-b">CCS</td>
                        <td class="py-2 px-4 border-b">College of Computing Studies</td>
                        <td class="py-2 px-4 border-b">ccs.admin.gmail.com</td>
                        <td class="py-2 px-4 border-b">CSC Fee</td>
                        <td class="py-2 px-4 border-b">
                            <span class="px-2 py-1 text-xs rounded-full bg-red-100 text-red-800">End</span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection