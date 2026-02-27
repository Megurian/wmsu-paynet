@extends('layouts.auth')

@section('title', 'Student Login')

@section('content')
    <h2 class="text-2xl font-bold text-center text-gray-800 mb-2">
        Student Portal
    </h2>

    <p class="text-sm text-gray-600 text-center mb-6">
        Sign in with your student ID and password.
    </p>

    @if (session('status'))
        <div class="mb-4 text-sm text-green-700 bg-green-50 border border-green-200 rounded-md p-3">
            {{ session('status') }}
        </div>
    @endif

    <form method="POST" action="{{ route('student.login') }}" class="space-y-4">
        @csrf

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Student ID</label>
            <input
                type="text"
                name="student_id"
                value="{{ old('student_id') }}"
                required
                autofocus
                class="w-full rounded-md border-gray-300 focus:border-red-600 focus:ring-red-600"
            >
            @error('student_id')
                <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
            <input
                type="password"
                name="password"
                required
                class="w-full rounded-md border-gray-300 focus:border-red-600 focus:ring-red-600"
            >
            @error('password')
                <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex items-center justify-between text-sm">
            <label class="flex items-center gap-2 text-gray-600">
                <input type="checkbox" name="remember">
                Remember me
            </label>

            <a href="{{ route('student.password.request') }}" class="text-red-700 hover:underline">
                Set up / Forgot password?
            </a>
        </div>

        <button type="submit" class="w-full bg-red-700 hover:bg-red-800 text-white py-2.5 rounded-md font-medium transition">
            Login
        </button>

        <a href="{{ route('login') }}" class="block text-center text-sm text-gray-600 hover:text-red-700 transition">
            Staff/Admin login
        </a>
    </form>
@endsection
