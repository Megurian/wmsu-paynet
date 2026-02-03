@extends('layouts.dashboard')

@section('title', 'Add User')
@section('page-title', 'Add New User')

@section('content')
<form action="{{ route('college.users.store') }}" method="POST" class="bg-white p-6 rounded shadow-md max-w-md">
    @csrf
    <div class="mb-4">
        <label class="block mb-1 font-semibold">Name</label>
        <input type="text" name="name" class="w-full border p-2 rounded" required>
    </div>
    <div class="mb-4">
        <label class="block mb-1 font-semibold">Email</label>
        <input type="email" name="email" class="w-full border p-2 rounded" required>
    </div>
    <div class="mb-4">
        <label class="block mb-1 font-semibold">Role</label>
        <select name="role" class="w-full border p-2 rounded" required>
            <option value="student_coordinator">Student Coordinator</option>
            <option value="adviser">Adviser</option>
            <option value="assessor">Assessor</option>
        </select>
    </div>
    <div class="mb-4">
        <label class="block mb-1 font-semibold">Password</label>
        <input type="password" name="password" class="w-full border p-2 rounded" required>
    </div>
    <div class="mb-4">
        <label class="block mb-1 font-semibold">Confirm Password</label>
        <input type="password" name="password_confirmation" class="w-full border p-2 rounded" required>
    </div>
    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Create User</button>
</form>
@endsection
