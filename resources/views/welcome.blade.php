@extends('layouts.auth')

@section('title', 'Welcome')

@section('content')
    <h2 class="text-2xl font-bold text-gray-800 text-center mb-3">
        Welcome to WMSU PayNet
    </h2>

    <p class="text-gray-600 text-sm text-center mb-8 leading-relaxed">
        Manage university-related payments securely and efficiently.
        Please log in or create an account to continue.
    </p>

    <div class="space-y-4">
        <a href="{{ route('login') }}"
           class="block text-center bg-red-700 hover:bg-red-800
                  text-white py-2.5 rounded-md font-medium transition">
            Login to Your Account
        </a>

        @if (Route::has('register'))
            <a href="{{ route('register') }}"
               class="block text-center border border-red-700 text-red-700
                      hover:bg-red-50 py-2.5 rounded-md font-medium transition">
                Create a New Account
            </a>
        @endif
    </div>

    <p class="text-xs text-gray-500 text-center mt-6">
        Authorized users only. All transactions are logged and secured.
    </p>
@endsection
