@extends('layouts.dashboard')

@section('title', 'Create Organization')
@section('page-title', 'Create College Organization')

@section('content')
<div class="flex justify-center">
<form action="{{ route('college.local_organizations.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
    @csrf
    <h3 class="text-lg font-semibold">Organization Details</h3>
     <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-4">
        <div class="grid grid-cols-1 md:grid-cols-1 gap-5 mb-4">
            <div class="mb-4">
                <label class="block font-medium mb-1">Organization Name</label>
                <input type="text" name="name"  placeholder="Enter Organization Name" class="w-full border border-gray-300 px-4 py-2 rounded focus:outline-none focus:ring-2 focus:ring-blue-400" required>
            </div>

            <div class="mb-4">
                <label class="block font-medium mb-1">Organization Code</label>
                <input type="text" name="org_code" placeholder="Enter Organization Code" class="w-full border border-gray-300 px-4 py-2 rounded focus:outline-none focus:ring-2 focus:ring-blue-400" required>
                <p class="text-xs text-gray-500 mt-1">Unique code for the organization</p>
                <p class="text-xs mt-1"></p>
            </div>
        </div>

        <div class="mb-6 relative">
            <label class="block font-medium px-5 mb-2">Organization Logo (Optional)</label>
            <div class="w-40 h-40 border-2 border-dashed border-gray-300 rounded-lg flex items-center justify-center cursor-pointer relative mx-auto">
                <button type="button" class="hidden absolute -top-7 -right-3 right-0 bg-red-600 text-white rounded-full w-6 h-6 flex items-center justify-center text-xs hover:bg-red-700">×</button>
                <span class="text-gray-400 text-4xl font-bold">+</span>
                <img class="hidden w-full h-full object-cover rounded-lg absolute top-0 left-0" alt="Logo Preview">
                <input type="file" name="logo" id="logoInput" accept="image/*" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer">
            </div>
            <p class="text-xs text-gray-500 mt-1 text-center">Click to upload organization logo</p>
        </div>
    </div>
    
    <h3 class="text-lg font-semibold">Initial Admin Details</h3>
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div>
            <label class="block mb-1 text-gray-700 font-medium">Last Name <span class="text-red-500">*</span></label>
            <input type="text" name="last_name" class="w-full border border-gray-300 px-4 p-2 rounded focus:ring-1 focus:ring-blue-400" required>
        </div>
        <div>
            <label class="block mb-1 text-gray-700 font-medium">First Name <span class="text-red-500">*</span></label>
            <input type="text" name="first_name" class="w-full border border-gray-300 px-4 p-2 rounded focus:ring-1 focus:ring-blue-400" required>
        </div>
        <div>
            <label class="block mb-1 text-gray-700 font-medium">Middle Name</label>
            <input type="text" name="middle_name" class="w-full border border-gray-300 px-4 p-2 rounded focus:ring-1 focus:ring-blue-400">
        </div>
        <div>
            <label class="block mb-1 text-gray-700 font-medium">Suffix</label>
            <select name="suffix" class="w-full border border-gray-300 px-4 p-2 rounded focus:ring-1 focus:ring-blue-400">
                <option value="">None</option>
                <option value="Jr.">Jr.</option>
                <option value="Sr.">Sr.</option>
                <option value="III">III</option>
                <option value="IV">IV</option>
            </select>
        </div>
    </div>
    <div>
        <label class="block font-medium mb-1">Admin Email</label>
        <input type="email" name="admin_email" placeholder="Enter Admin Email" class="w-full border border-gray-300 px-4 p-2 rounded focus:ring-1 focus:ring-blue-400"required>
    </div>
    <div>
        <label class="block font-medium mb-1">Password</label>
        <input type="password" name="admin_password" placeholder="Enter Password" class="w-full border border-gray-300 px-4 p-2 rounded focus:ring-1 focus:ring-blue-400" required>
    </div>
    <div>
        <label class="block font-medium mb-1">Confirm Password</label>
        <input type="password" name="admin_password_confirmation" placeholder="Confirm Password" class="w-full border border-gray-300 px-4 p-2 rounded focus:ring-1 focus:ring-blue-400" required>
    </div>
    <div class="flex justify-end">
    <button type="submit" class="bg-red-700 text-white px-6 py-2 rounded hover:bg-red-800">Submit Organization</button>
    </div>
</div>
</form>
</div>
@endsection
