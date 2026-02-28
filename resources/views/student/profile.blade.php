@extends('student.layouts.student-dashboard')

@section('title', 'My Profile')
@section('page-title', 'My Profile')

@section('content')
<div class="mb-8">
    <h2 class="text-3xl font-bold text-gray-800">Profile</h2>
    <p class="text-sm text-gray-500 mt-1">Manage your contact details and password.</p>
</div>

<div class="bg-white rounded-lg shadow-md p-6 max-w-2xl">
    <form method="POST" action="{{ route('student.profile.update') }}" class="space-y-4">
        @csrf
        @method('PATCH')

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Student ID</label>
            <input type="text" value="{{ $student->student_id }}" disabled class="w-full rounded-md border-gray-300 bg-gray-100">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
            <input type="text" value="{{ trim($student->last_name . ', ' . $student->first_name . ' ' . $student->middle_name . ' ' . $student->suffix) }}" disabled class="w-full rounded-md border-gray-300 bg-gray-100">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
            <input type="email" name="email" value="{{ old('email', $student->email) }}" class="w-full rounded-md border-gray-300 focus:border-red-600 focus:ring-red-600">
            @error('email')
                <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Contact Number</label>
            <input type="text" name="contact" value="{{ old('contact', $student->contact) }}" class="w-full rounded-md border-gray-300 focus:border-red-600 focus:ring-red-600">
            @error('contact')
                <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
            @enderror
        </div>

        <hr class="my-2">

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">New Password (optional)</label>
            <input type="password" name="password" class="w-full rounded-md border-gray-300 focus:border-red-600 focus:ring-red-600">
            @error('password')
                <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Confirm New Password</label>
            <input type="password" name="password_confirmation" class="w-full rounded-md border-gray-300 focus:border-red-600 focus:ring-red-600">
        </div>

        <button type="submit" class="bg-red-700 hover:bg-red-800 text-white px-5 py-2 rounded-md font-medium transition">
            Save Changes
        </button>
    </form>
</div>
@endsection
