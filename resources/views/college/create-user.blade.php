@extends('layouts.dashboard')

@section('title', 'Add User')
@section('page-title', 'Add New User')

@section('content')
<div class="flex justify-center py-6">
    <div class="w-full max-w-5xl">
        <div class="mb-4">
            <a href="{{ route('college.users.index', ['tab' => 'accounts']) }}" 
               class="inline-block px-4 py-2 bg-gray-200 text-gray-800 rounded hover:bg-gray-300 transition">
               &larr; Back
            </a>
        </div>

        <form action="{{ route('college.users.store') }}" method="POST" class="bg-white p-6 rounded-lg shadow-md space-y-6">
            @csrf
            <h2 class="text-2xl font-semibold text-gray-800 border-b pb-2 mb-4">User Details</h2>

            <!-- Name Fields -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block mb-1 text-gray-700 font-medium">Last Name <span class="text-red-500">*</span></label>
                    <input type="text" name="last_name" class="w-full border border-gray-300 p-2 rounded focus:ring-1 focus:ring-blue-400" required>
                </div>
                <div>
                    <label class="block mb-1 text-gray-700 font-medium">First Name <span class="text-red-500">*</span></label>
                    <input type="text" name="first_name" class="w-full border border-gray-300 p-2 rounded focus:ring-1 focus:ring-blue-400" required>
                </div>
                <div>
                    <label class="block mb-1 text-gray-700 font-medium">Middle Name</label>
                    <input type="text" name="middle_name" class="w-full border border-gray-300 p-2 rounded focus:ring-1 focus:ring-blue-400">
                </div>
                <div>
                    <label class="block mb-1 text-gray-700 font-medium">Suffix</label>
                    <select name="suffix" class="w-full border border-gray-300 p-2 rounded focus:ring-1 focus:ring-blue-400">
                        <option value="">None</option>
                        <option value="Jr.">Jr.</option>
                        <option value="Sr.">Sr.</option>
                        <option value="III">III</option>
                        <option value="IV">IV</option>
                    </select>
                </div>
            </div>

            <!-- Email -->
            <div>
                <label class="block mb-1 text-gray-700 font-medium">Email <span class="text-red-500">*</span></label>
                <input type="email" name="email" class="w-full border border-gray-300 p-2 rounded focus:ring-1 focus:ring-blue-400" required>
            </div>

            <!-- Role -->
            <div>
                <label class="block mb-1 text-gray-700 font-medium">Role <span class="text-red-500">*</span></label>
                <select name="role" class="w-full border border-gray-300 p-2 rounded focus:ring-1 focus:ring-blue-400" required>
                    <option value="student_coordinator">Student Coordinator</option>
                    <option value="adviser">Adviser</option>
                    <option value="assessor">Assessor</option>
                </select>
            </div>

            <!-- Password -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block mb-1 text-gray-700 font-medium">Password <span class="text-red-500">*</span></label>
                    <input type="password" name="password" class="w-full border border-gray-300 p-2 rounded focus:ring-1 focus:ring-blue-400" required>
                </div>
                <div>
                    <label class="block mb-1 text-gray-700 font-medium">Confirm Password <span class="text-red-500">*</span></label>
                    <input type="password" name="password_confirmation" class="w-full border border-gray-300 p-2 rounded focus:ring-1 focus:ring-blue-400" required>
                </div>
            </div>

            <!-- Submit -->
            <div class="pt-2 border-t mt-2">
                <button type="submit" class="px-6 py-2 bg-red-800 text-white rounded hover:bg-red-800 transition">
                    Create User
                </button>
            </div>
        </form>
    </div>
</div>
@endsection