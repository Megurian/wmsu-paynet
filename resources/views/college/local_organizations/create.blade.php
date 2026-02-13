@extends('layouts.dashboard')

@section('title', 'Create Organization')
@section('page-title', 'Create College Organization')

@section('content')
<form action="{{ route('college.local_organizations.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
    @csrf

    <h3 class="text-lg font-semibold">Organization Details</h3>
    <div>
        <label>Name</label>
        <input type="text" name="name" class="form-input" required>
    </div>
    <div>
        <label>Organization Code</label>
        <input type="text" name="org_code" class="form-input" required>
    </div>
    <div>
        <label>Logo (optional)</label>
        <input type="file" name="logo" class="form-input">
    </div>

    <h3 class="text-lg font-semibold">Initial Admin Details</h3>
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
    <div>
        <label>Admin Email</label>
        <input type="email" name="admin_email" class="form-input" required>
    </div>
    <div>
        <label>Password</label>
        <input type="password" name="admin_password" class="form-input" required>
    </div>
    <div>
        <label>Confirm Password</label>
        <input type="password" name="admin_password_confirmation" class="form-input" required>
    </div>

    <button type="submit" class="btn btn-primary">Submit Organization</button>
</form>
@endsection
