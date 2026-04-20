@extends('layouts.landing')

@section('title', 'Welcome to WMSU PayNet')

@section('content')
    <div class="text-center lg:text-left">
        <p class="text-base uppercase tracking-[0.4em] text-red-700 font-semibold mb-5">
            WMSU PayNet
        </p>
        <h1 class="text-4xl sm:text-5xl font-extrabold text-gray-900 leading-tight max-w-3xl">
            Unified payment management for students, staff, and administration.
        </h1>
        <p class="mt-6 max-w-2xl text-gray-600 text-base sm:text-lg leading-8 mx-auto lg:mx-0">
            Streamline university payments, monitor student balances, and access real-time financial reports from one modern portal.
        </p>

        <div class="mt-10 flex flex-col sm:flex-row sm:items-center gap-4 justify-center lg:justify-start">
            <a href="{{ route('login') }}"
               class="js-page-transition inline-flex items-center justify-center rounded-full bg-red-700 px-8 py-3 text-sm font-semibold text-white transition hover:bg-red-800 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2">
                Login to PayNet
            </a>
        </div>
    </div>

    <div class="mt-16 grid gap-6 sm:grid-cols-2 xl:grid-cols-3">
        <div class="rounded-3xl border border-gray-200 bg-white p-6 shadow-sm">
            <h3 class="text-lg font-semibold text-gray-900 mb-3">Intuitive Dashboard</h3>
            <p class="text-gray-600 text-sm leading-6">
                See outstanding balances, payment deadlines, and activity summaries in one easy view.
            </p>
        </div>
        <div class="rounded-3xl border border-gray-200 bg-white p-6 shadow-sm">
            <h3 class="text-lg font-semibold text-gray-900 mb-3">Student Account Tracking</h3>
            <p class="text-gray-600 text-sm leading-6">
                Track enrollments, charges, receipts, and payment history for every student.
            </p>
        </div>
        <div class="rounded-3xl border border-gray-200 bg-white p-6 shadow-sm">
            <h3 class="text-lg font-semibold text-gray-900 mb-3">Reports & Compliance</h3>
            <p class="text-gray-600 text-sm leading-6">
                Generate financial summaries and maintain accountability with clear records.
            </p>
        </div>
        <div class="rounded-3xl border border-gray-200 bg-white p-6 shadow-sm">
            <h3 class="text-lg font-semibold text-gray-900 mb-3">Fast, Reliable Processing</h3>
            <p class="text-gray-600 text-sm leading-6">
                Manage student payments and billing workflows with frictionless updates.
            </p>
        </div>
        <div class="rounded-3xl border border-gray-200 bg-white p-6 shadow-sm">
            <h3 class="text-lg font-semibold text-gray-900 mb-3">Unified Access</h3>
            <p class="text-gray-600 text-sm leading-6">
                One login serves student and staff experiences through a shared portal.
            </p>
        </div>
        <div class="rounded-3xl border border-gray-200 bg-white p-6 shadow-sm">
            <h3 class="text-lg font-semibold text-gray-900 mb-3">Modern Experience</h3>
            <p class="text-gray-600 text-sm leading-6">
                Clean interface designed for fast navigation and consistent workflows.
            </p>
        </div>
    </div>

    <p class="mt-10 text-sm text-gray-500 text-center lg:text-left">
        Designed for WMSU student organization payment operations.
    </p>
@endsection
