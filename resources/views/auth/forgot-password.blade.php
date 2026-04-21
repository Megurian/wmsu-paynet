@extends('layouts.auth')

@section('title', $title ?? 'Reset Password')

@section('content')
    @php
        $formAction = $action ?? route('password.email');
        $backUrl = $backUrl ?? route('login');
        $backText = $backText ?? 'Back to login';
        $heading = $title ?? 'Reset Password';
        $description = $description ?? 'Enter your email address and we will email you a password reset link that will allow you to choose a new one.';
    @endphp

    <h2 class="text-2xl font-bold text-center text-gray-800 mb-2">
        {{ $heading }}
    </h2>

    <p class="text-sm text-gray-600 text-center mb-6">
        {{ $description }}
    </p>

    @if (session('status'))
        <div class="mb-4 text-sm text-green-700 bg-green-50 border border-green-200 rounded-md p-3">
            {{ session('status') }}
        </div>
    @endif

    <form method="POST" action="{{ $formAction }}" class="space-y-4">
        @csrf

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
            <input
                type="email"
                name="email"
                value="{{ old('email') }}"
                required
                autofocus
                autocomplete="email"
                class="w-full rounded-md border-gray-300 focus:border-red-600 focus:ring-red-600"
            >
            @error('email')
                <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
            @enderror
        </div>

        <button type="submit" class="w-full bg-red-700 hover:bg-red-800 text-white py-2.5 rounded-md font-medium transition">
            Email Password Reset Link
        </button>

        <a href="{{ $backUrl }}" class="block text-center text-sm text-gray-600 hover:text-red-700 transition">
            {{ $backText }}
        </a>
    </form>
@endsection
