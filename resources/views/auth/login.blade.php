@extends('layouts.auth')

@section('title', 'Login')

@section('content')
    <h2 class="text-2xl font-bold text-center text-gray-800 mb-2">
        Sign In
    </h2>

    <p class="text-sm text-gray-600 text-center mb-6">
        Enter your university account credentials to continue.
    </p>

    @if (session('status'))
        <div class="mb-4 text-sm text-green-700 bg-green-50 border border-green-200 rounded-md p-3">
            {{ session('status') }}
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}" class="space-y-4">
        @csrf

        <!-- Email -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
                Email Address
            </label>
            <input
                type="email"
                name="email"
                value="{{ old('email') }}"
                required
                autocomplete="email"
                autofocus
                class="w-full rounded-md border-gray-300
                       focus:border-red-600 focus:ring-red-600"
            >
            @error('email')
                <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
            @enderror
        </div>

        <!-- Password -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
                Password
            </label>
            <div class="relative">
                <input
                    id="login-password"
                    type="password"
                    name="password"
                    required
                    autocomplete="current-password"
                    class="w-full rounded-md border-gray-300 pr-12
                           focus:border-red-600 focus:ring-red-600"
                >
                <button
                    type="button"
                    data-password-toggle="login-password"
                    class="absolute inset-y-0 right-0 mr-3 flex items-center justify-center text-red-700 hover:text-red-900"
                    aria-label="Toggle password visibility"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                </button>
            </div>
            @error('password')
                <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
            @enderror
        </div>

        <!-- Remember / Forgot -->
        <div class="flex items-center justify-between text-sm">
            <label class="flex items-center gap-2 text-gray-600">
                <input type="checkbox" name="remember">
                Remember me
            </label>

            @if (Route::has('password.request'))
                <a href="{{ route('password.request') }}"
                   class="text-red-700 hover:underline">
                    Forgot password?
                </a>
            @endif
        </div>

        <!-- Submit -->
        <button
            type="submit"
            class="w-full bg-red-700 hover:bg-red-800
                   text-white py-2.5 rounded-md font-medium transition"
        >
            Login
        </button>

        {{-- <p class="text-sm text-center text-gray-600 mt-4">
            Don’t have an account?
            <a href="{{ route('register') }}"
               class="text-red-700 font-medium hover:underline">
                Register here
            </a>
        </p> --}}
    </form>
@endsection
