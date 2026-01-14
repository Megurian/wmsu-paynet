@extends('layouts.dashboard')

@section('title', 'Add College')
@section('page-title', 'Add New College')

@section('content')
<form action="{{ route('osa.college.store') }}" method="POST" enctype="multipart/form-data" class="max-w-xl bg-white p-6 rounded shadow">
    @csrf

    <div class="mb-4">
        <label class="block font-medium">College Name</label>
        <input type="text" name="name" class="w-full border px-3 py-2 rounded" required>
    </div>

    <div class="mb-4">
        <label class="block font-medium">College Code</label>
        <input type="text" name="college_code" class="w-full border px-3 py-2 rounded" required>
        <p class="text-xs text-gray-500">Unique code for the college (e.g., CCS001)</p>
    </div>

    <div class="mb-4">
        <label class="block font-medium">College Logo (Optional)</label>
        <input type="file" name="logo" class="w-full">
    </div>

    <h3 class="text-lg font-semibold mb-2">Initial College Admin</h3>

    <div class="mb-4">
        <label class="block font-medium">Admin Name</label>
        <input type="text" name="admin_name" class="w-full border px-3 py-2 rounded" required>
    </div>

    <div class="mb-4">
        <label class="block font-medium">Admin Email</label>
        <input type="email" name="admin_email" class="w-full border px-3 py-2 rounded" required>
    </div>

    <div class="mb-4">
        <label class="block font-medium">Admin Password</label>
        <input type="password" name="admin_password" class="w-full border px-3 py-2 rounded" required>
    </div>

    <div class="mb-4">
        <label class="block font-medium">Confirm Password</label>
        <input type="password" name="admin_password_confirmation" class="w-full border px-3 py-2 rounded" required>
    </div>

    <button type="submit" class="bg-red-700 text-white px-4 py-2 rounded">Create College</button>
</form>
@endsection
