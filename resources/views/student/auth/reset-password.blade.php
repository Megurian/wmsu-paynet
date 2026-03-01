@extends('layouts.auth')

@section('title', 'Reset Student Password')

@section('content')
    <h2 class="text-2xl font-bold text-center text-gray-800 mb-2">
        Reset Password
    </h2>

    <p class="text-sm text-gray-600 text-center mb-6">
        Create a new password for your student account.
    </p>

    <form method="POST" action="{{ route('student.password.store') }}" class="space-y-4">
        @csrf

        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
            <input
                type="email"
                name="email"
                value="{{ old('email', $request->email) }}"
                required
                autofocus
                autocomplete="username"
                class="w-full rounded-md border-gray-300 focus:border-red-600 focus:ring-red-600"
            >
            @error('email')
                <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">New Password</label>
            <input
                type="password"
                name="password"
                required
                autocomplete="new-password"
                class="w-full rounded-md border-gray-300 focus:border-red-600 focus:ring-red-600"
            >
            @error('password')
                <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Confirm Password</label>
            <input
                type="password"
                name="password_confirmation"
                required
                autocomplete="new-password"
                class="w-full rounded-md border-gray-300 focus:border-red-600 focus:ring-red-600"
            >
            @error('password_confirmation')
                <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
            @enderror
        </div>

        <button type="submit" class="w-full bg-red-700 hover:bg-red-800 text-white py-2.5 rounded-md font-medium transition">
            Reset Password
        </button>
    </form>
@endsection
