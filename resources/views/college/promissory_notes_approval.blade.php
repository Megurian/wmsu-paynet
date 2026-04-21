@extends('layouts.dashboard')

@section('title', 'Promissory Note Approvals')
@section('page-title', 'Promissory Note Approvals')

@section('content')
@php
    $tabs = [
        'pending_verification' => 'Pending Review',
        'active' => 'Active',
        'closed' => 'Closed',
        'default' => 'Default',
        'bad_debt' => 'Bad Debt',
        'voided' => 'Voided',
        'all' => 'All',
    ];

    $statusLabels = [
        'PENDING_SIGNATURE' => 'Pending Signature',
        'PENDING_VERIFICATION' => 'Pending Review',
        'ACTIVE' => 'Active',
        'VOIDED' => 'Voided',
        'CLOSED' => 'Closed',
        'DEFAULT' => 'Default',
        'BAD_DEBT' => 'Bad Debt',
    ];

    $statusClasses = [
        'PENDING_SIGNATURE' => 'bg-amber-100 text-amber-800 border-amber-200',
        'PENDING_VERIFICATION' => 'bg-sky-100 text-sky-800 border-sky-200',
        'ACTIVE' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
        'VOIDED' => 'bg-slate-100 text-slate-700 border-slate-200',
        'CLOSED' => 'bg-green-100 text-green-800 border-green-200',
        'DEFAULT' => 'bg-orange-100 text-orange-800 border-orange-200',
        'BAD_DEBT' => 'bg-red-100 text-red-800 border-red-200',
    ];
@endphp

<div class="mb-6 flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">
    <div>
        <h1 class="text-2xl font-semibold text-gray-900">Promissory Note Approvals</h1>
        <p class="mt-1 text-sm text-gray-500">Review uploaded signatures and move to the reporting dashboard for balances and delinquency counts.</p>
    </div>

    <a href="{{ route('college.promissory_notes.dashboard') }}" class="inline-flex items-center justify-center rounded-xl bg-red-800 px-4 py-2 text-sm font-semibold text-white transition hover:bg-red-700">
        Open PN Reports
    </a>
</div>

<div class="mb-6 flex flex-wrap gap-2">
    @foreach($tabs as $key => $label)
        <a href="{{ route('college.promissory_notes.index', ['tab' => $key]) }}"
           class="rounded-full px-4 py-2 text-sm font-medium transition {{ $tab === $key ? 'bg-red-800 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }}">
            {{ $label }}
            @if(isset($counts[$key]))
                <span class="ml-2 inline-block rounded-full bg-white px-2 py-0.5 text-xs font-semibold text-red-800">{{ $counts[$key] }}</span>
            @endif
        </a>
    @endforeach
</div>

<div class="mb-6 rounded-2xl border border-gray-200 bg-white p-5">
    <form method="GET" action="{{ route('college.promissory_notes.index') }}" class="grid gap-3 md:grid-cols-2 lg:grid-cols-[1.8fr_1fr_1fr_1fr_auto] items-end">
        <input type="hidden" name="tab" value="{{ $tab }}">

        <div>
            <label for="search" class="sr-only">Search</label>
            <input
                id="search"
                name="search"
                type="text"
                value="{{ $search ?? '' }}"
                placeholder="Search PN #, student name, or student ID"
                class="w-full rounded-2xl border border-gray-300 bg-white px-4 py-2 text-sm text-gray-900 shadow-sm focus:border-red-600 focus:outline-none focus:ring-2 focus:ring-red-100"
            >
        </div>

        <div>
            <label for="school_year_id" class="sr-only">School Year</label>
            <select
                id="school_year_id"
                name="school_year_id"
                class="w-full rounded-2xl border border-gray-300 bg-white px-4 py-2 text-sm text-gray-900 shadow-sm focus:border-red-600 focus:outline-none focus:ring-2 focus:ring-red-100"
            >
                <option value="">All school years</option>
                @foreach($schoolYears as $schoolYear)
                    <option value="{{ $schoolYear->id }}" {{ optional($selectedSchoolYear)->id === $schoolYear->id ? 'selected' : '' }}>
                        {{ $schoolYear->sy_start->format('Y') }} - {{ $schoolYear->sy_end->format('Y') }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label for="semester_id" class="sr-only">Semester</label>
            <select
                id="semester_id"
                name="semester_id"
                class="w-full rounded-2xl border border-gray-300 bg-white px-4 py-2 text-sm text-gray-900 shadow-sm focus:border-red-600 focus:outline-none focus:ring-2 focus:ring-red-100"
            >
                <option value="">All semesters</option>
                @foreach($semesters as $semester)
                    <option value="{{ $semester->id }}" {{ optional($selectedSemester)->id === $semester->id ? 'selected' : '' }}>
                        {{ ucfirst($semester->name) }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label for="document_filter" class="sr-only">Document status</label>
            <select
                id="document_filter"
                name="document_filter"
                class="w-full rounded-2xl border border-gray-300 bg-white px-4 py-2 text-sm text-gray-900 shadow-sm focus:border-red-600 focus:outline-none focus:ring-2 focus:ring-red-100"
            >
                <option value="all" {{ ($documentFilter ?? 'all') === 'all' ? 'selected' : '' }}>All documents</option>
                <option value="has_document" {{ ($documentFilter ?? '') === 'has_document' ? 'selected' : '' }}>Has uploaded document</option>
                <option value="no_document" {{ ($documentFilter ?? '') === 'no_document' ? 'selected' : '' }}>Without document</option>
            </select>
        </div>

        <div class="flex flex-col gap-2 sm:flex-row sm:items-center">
            <button type="submit" class="inline-flex items-center justify-center rounded-2xl bg-red-800 px-4 py-2 text-sm font-semibold text-white transition hover:bg-red-700">
                Apply
            </button>
            <a href="{{ route('college.promissory_notes.index', ['tab' => $tab]) }}" class="inline-flex items-center justify-center rounded-2xl border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 transition hover:bg-gray-50">
                Reset
            </a>
        </div>
    </form>
</div>

@if($notes->isEmpty())
    <div class="rounded-2xl border border-dashed border-gray-300 bg-white p-10 text-center text-gray-500">
        No promissory notes found for this view.
    </div>
@else
    <div class="space-y-6">
        @foreach($notes as $note)
            <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
                <div class="flex flex-col gap-4 xl:flex-row xl:items-start xl:justify-between">
                    <div class="space-y-4 xl:flex-1">
                        <div class="flex flex-wrap items-center gap-3">
                            <h3 class="text-xl font-semibold text-gray-900">PN #{{ $note->id }}</h3>
                            <span class="inline-flex rounded-full border px-3 py-1 text-xs font-semibold {{ $statusClasses[$note->status] ?? 'bg-gray-100 text-gray-700 border-gray-200' }}">
                                {{ $statusLabels[$note->status] ?? $note->status }}
                            </span>
                            <span class="text-sm text-gray-500">Student: {{ $note->student->full_name ?? $note->student?->student_id ?? '—' }}</span>
                        </div>

                        <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
                            <div class="rounded-xl bg-gray-50 p-3">
                                <div class="text-xs font-semibold uppercase tracking-wide text-gray-500">Student ID</div>
                                <div class="mt-1 text-sm font-semibold text-gray-900">{{ $note->student->student_id ?? '—' }}</div>
                            </div>
                            <div class="rounded-xl bg-gray-50 p-3">
                                <div class="text-xs font-semibold uppercase tracking-wide text-gray-500">Remaining Balance</div>
                                <div class="mt-1 text-sm font-semibold text-gray-900">₱{{ number_format((float) $note->remaining_balance, 2) }}</div>
                            </div>
                            <div class="rounded-xl bg-gray-50 p-3">
                                <div class="text-xs font-semibold uppercase tracking-wide text-gray-500">Due Date</div>
                                <div class="mt-1 text-sm font-semibold text-gray-900">{{ optional($note->due_date)->format('M d, Y') }}</div>
                            </div>
                            <div class="rounded-xl bg-gray-50 p-3">
                                <div class="text-xs font-semibold uppercase tracking-wide text-gray-500">Signed At</div>
                                <div class="mt-1 text-sm font-semibold text-gray-900">{{ optional($note->signed_at)->format('M d, Y h:i A') ?? '—' }}</div>
                            </div>
                        </div>

                        <div class="rounded-xl border border-gray-200">
                            <div class="border-b border-gray-200 bg-gray-50 px-4 py-2 text-sm font-semibold text-gray-700">Deferred Fees</div>
                            <div class="overflow-x-auto">
                                <table class="min-w-full text-sm">
                                    <thead class="bg-white">
                                        <tr class="text-left text-xs uppercase tracking-wide text-gray-500">
                                            <th class="px-4 py-3">Fee</th>
                                            <th class="px-4 py-3">Scope</th>
                                            <th class="px-4 py-3 text-right">Deferred Amount</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-200">
                                        @foreach($note->fees as $fee)
                                            <tr>
                                                <td class="px-4 py-3 font-medium text-gray-900">{{ $fee->fee_name }}</td>
                                                <td class="px-4 py-3 text-gray-600">{{ $fee->organization->name ?? ($fee->college->name ?? 'College') }}</td>
                                                <td class="px-4 py-3 text-right font-semibold text-gray-900">₱{{ number_format((float) $fee->pivot->amount_deferred, 2) }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        @if(! empty($note->notes))
                            <div class="rounded-xl border border-gray-200 bg-gray-50 p-4 text-sm text-gray-700">
                                <div class="text-xs font-semibold uppercase tracking-wide text-gray-500">Coordinator Notes</div>
                                <p class="mt-1 whitespace-pre-line">{{ $note->notes }}</p>
                            </div>
                        @endif
                    </div>

                    <div class="w-full max-w-xl rounded-2xl border border-gray-200 bg-gray-50 p-4 xl:w-[32rem]">
                        @if($note->document_path)
                            <div class="mb-3 flex items-center justify-between gap-3">
                                <div>
                                    <div class="text-sm font-semibold text-gray-900">Uploaded Signed Copy</div>
                                    <div class="text-xs text-gray-500">{{ basename($note->document_path) }}</div>
                                </div>
                                <a href="{{ route('college.promissory_notes.document', $note) }}" target="_blank" class="rounded-lg bg-white px-3 py-1.5 text-sm font-medium text-gray-700 shadow-sm ring-1 ring-gray-200 hover:bg-gray-50">Open</a>
                            </div>
                            <div class="overflow-hidden rounded-xl border border-gray-200 bg-white">
                                <iframe src="{{ route('college.promissory_notes.document', $note) }}" class="h-[420px] w-full"></iframe>
                            </div>
                        @else
                            <div class="rounded-xl border border-dashed border-gray-300 bg-white p-4 text-sm text-gray-600">
                                No uploaded document is attached to this note.
                            </div>
                        @endif

                        @if($note->status === 'PENDING_VERIFICATION')
                            <div class="mt-4 space-y-4 rounded-xl border border-sky-200 bg-white p-4">
                                <form method="POST" action="{{ route('college.promissory_notes.approve', $note) }}" class="space-y-3">
                                    @csrf
                                    <label class="flex items-start gap-2 text-sm text-gray-700">
                                        <input type="checkbox" name="review_confirmed" value="1" class="mt-1 rounded border-gray-300 text-red-800 focus:ring-red-500" required>
                                        <span>I have reviewed the uploaded signature and approve this promissory note.</span>
                                    </label>
                                    <textarea name="review_notes" rows="3" class="w-full rounded-xl border border-gray-300 px-3 py-2 text-sm" placeholder="Optional review notes"></textarea>
                                    <button type="submit" class="w-full rounded-xl bg-emerald-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-emerald-500">
                                        Approve Signature
                                    </button>
                                </form>

                                <form method="POST" action="{{ route('college.promissory_notes.reject', $note) }}" class="space-y-3">
                                    @csrf
                                    <textarea name="review_notes" rows="2" class="w-full rounded-xl border border-gray-300 px-3 py-2 text-sm" placeholder="Reason for rejection (optional)"></textarea>
                                    <button type="submit" class="w-full rounded-xl bg-rose-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-rose-500">
                                        Reject and Return for Re-signing
                                    </button>
                                </form>
                            </div>
                        @else
                            <div class="mt-4 rounded-xl border border-gray-200 bg-white p-4 text-sm text-gray-700">
                                This note is not awaiting approval.
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="mt-6">
        {{ $notes->links() }}
    </div>
@endif
@endsection