@extends('student.layouts.student-dashboard')

@section('title', 'My Profile')
@section('page-title', 'My Profile')

@section('content')
<div class="mb-8 space-y-1">
    <h2 class="text-2xl font-bold text-gray-900 sm:text-3xl">Profile</h2>
    <p class="text-sm text-gray-500 mt-1">Manage your contact details and password.</p>
</div>

<div class="mx-auto max-w-2xl rounded-2xl border border-gray-200 bg-white p-4 shadow-md sm:p-6">
    <form method="POST" action="{{ route('student.profile.update') }}" class="space-y-4">
        @csrf
        @method('PATCH')

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Student ID</label>
            <input type="text" value="{{ $student->student_id }}" disabled class="w-full rounded-lg border-gray-300 bg-gray-100 text-sm sm:text-base">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
            <input type="text" value="{{ trim($student->last_name . ', ' . $student->first_name . ' ' . $student->middle_name . ' ' . $student->suffix) }}" disabled class="w-full rounded-lg border-gray-300 bg-gray-100 text-sm sm:text-base">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
            <input type="email" name="email" value="{{ old('email', $student->email) }}" class="w-full rounded-lg border-gray-300 text-sm focus:border-red-600 focus:ring-red-600 sm:text-base">
            @error('email')
                <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Contact Number</label>
            <input type="text" name="contact" value="{{ old('contact', $student->contact) }}" class="w-full rounded-lg border-gray-300 text-sm focus:border-red-600 focus:ring-red-600 sm:text-base">
            @error('contact')
                <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
            @enderror
        </div>

        <hr class="my-2">

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">New Password (optional)</label>
            <input type="password" name="password" class="w-full rounded-lg border-gray-300 text-sm focus:border-red-600 focus:ring-red-600 sm:text-base">
            @error('password')
                <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Confirm New Password</label>
            <input type="password" name="password_confirmation" class="w-full rounded-lg border-gray-300 text-sm focus:border-red-600 focus:ring-red-600 sm:text-base">
        </div>

        <button type="submit" class="inline-flex w-full justify-center rounded-xl bg-red-700 px-5 py-3 font-medium text-white transition hover:bg-red-800 sm:w-auto">
            Save Changes
        </button>
    </form>
</div>
@endsection
