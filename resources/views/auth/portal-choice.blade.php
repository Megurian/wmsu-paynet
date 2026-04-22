@extends('layouts.auth')

@section('title', 'Choose Portal')

@section('content')
    <div class="space-y-6">
        <div class="text-center">
            <h2 class="text-2xl font-bold text-gray-800 mb-2">Choose Your Portal</h2>
            <p class="mx-auto max-w-xl text-sm text-gray-600">
                This email is registered for both a student account and an organization account.
                Please choose which portal you want to enter today.
            </p>
        </div>

        <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6 md:p-8 space-y-6">
            <div class="text-sm text-gray-600">
                <p><span class="font-semibold">Email:</span> {{ $email }}</p>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <form method="POST" action="{{ route('login.choice.post') }}" class="rounded-xl border border-gray-200 transition hover:shadow-lg">
                    @csrf
                    <input type="hidden" name="portal" value="student">
                    <button type="submit" class="w-full min-h-[72px] rounded-xl bg-red-700 px-6 py-4 text-left text-lg font-semibold text-white transition hover:bg-red-800 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2">
                        Student Portal
                    </button>
                </form>

                <form method="POST" action="{{ route('login.choice.post') }}" class="rounded-xl border border-gray-200 transition hover:shadow-lg">
                    @csrf
                    <input type="hidden" name="portal" value="web">
                    <button type="submit" class="w-full min-h-[72px] rounded-xl bg-gray-900 px-6 py-4 text-left text-lg font-semibold text-white transition hover:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2">
                        Organization Portal
                    </button>
                </form>
            </div>

            <div class="text-center text-sm text-gray-500">
                <a href="{{ route('login') }}" class="text-red-700 hover:underline">Back to login</a>
            </div>
        </div>
    </div>
@endsection
