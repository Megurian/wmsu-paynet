@extends('layouts.auth')

@section('title', 'Set Student Password')

@section('content')
    <h2 class="text-2xl font-bold text-center text-gray-800 mb-2">
        Set Up Your Account
    </h2>

    <p class="text-sm text-gray-600 text-center mb-6">
        Enter your registered email address to receive a password setup link.
    </p>

    @if (session('status'))
        <div class="mb-4 text-sm text-green-700 bg-green-50 border border-green-200 rounded-md p-3">
            {{ session('status') }}
        </div>
    @endif

    <form method="POST" action="{{ route('student.password.email') }}" class="space-y-4">
        @csrf

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
            <input
                type="email"
                name="email"
                value="{{ old('email') }}"
                required
                autofocus
                class="w-full rounded-md border-gray-300 focus:border-red-600 focus:ring-red-600"
            >
            @error('email')
                <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
            @enderror
        </div>

        <button type="submit" class="w-full bg-red-700 hover:bg-red-800 text-white py-2.5 rounded-md font-medium transition">
            Email Password Setup Link
        </button>

        <a href="{{ route('student.login') }}" class="block text-center text-sm text-gray-600 hover:text-red-700 transition">
            Back to student login
        </a>
    </form>
@endsection
