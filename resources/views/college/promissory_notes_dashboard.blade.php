@extends('layouts.dashboard')

@section('title', 'PN Reports')
@section('page-title', 'Promissory Note Reports')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900">Promissory Note Reports</h1>
            <p class="mt-1 text-sm text-gray-500">Issued, collected, overdue, and defaulted note totals for the selected academic term.</p>
            <p class="mt-1 text-sm text-gray-500">
                {{ optional($selectedSchoolYear)->sy_start ? $selectedSchoolYear->sy_start->format('Y') . ' - ' . $selectedSchoolYear->sy_end->format('Y') : 'All school years' }}
                · {{ optional($selectedSemester)->name ? ucfirst($selectedSemester->name) . ' semester' : 'All semesters' }}
            </p>
        </div>

        <a href="{{ route('college.promissory_notes.export', ['school_year_id' => $selectedSchoolYear?->id, 'semester_id' => $selectedSemester?->id]) }}" class="inline-flex items-center justify-center rounded-xl bg-emerald-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-emerald-500">
            Export CSV
        </a>
    </div>

    <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
        <form method="GET" action="{{ route('college.promissory_notes.dashboard') }}" class="grid gap-4 md:grid-cols-3 md:items-end">
            <div>
                <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">School Year</label>
                <select name="school_year_id" class="h-10 w-full rounded-lg border border-gray-300 px-3 text-sm focus:border-red-500 focus:ring-red-500" onchange="this.form.submit()">
                    @foreach($schoolYears as $schoolYear)
                        <option value="{{ $schoolYear->id }}" {{ optional($selectedSchoolYear)->id === $schoolYear->id ? 'selected' : '' }}>
                            {{ $schoolYear->sy_start->format('Y') }} - {{ $schoolYear->sy_end->format('Y') }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Semester</label>
                <select name="semester_id" class="h-10 w-full rounded-lg border border-gray-300 px-3 text-sm focus:border-red-500 focus:ring-red-500" onchange="this.form.submit()">
                    @foreach($semesters as $semester)
                        <option value="{{ $semester->id }}" {{ optional($selectedSemester)->id === $semester->id ? 'selected' : '' }}>
                            {{ ucfirst($semester->name) }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="flex gap-2">
                <a href="{{ route('college.promissory_notes.dashboard') }}" class="inline-flex h-10 flex-1 items-center justify-center rounded-lg border border-gray-300 px-3 text-sm font-medium text-gray-600 transition hover:bg-gray-100">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Issued</p>
            <p class="mt-2 text-3xl font-semibold text-gray-900">{{ number_format($summary['issued_count'] ?? 0) }}</p>
            <p class="mt-1 text-sm text-gray-500">All promissory notes in scope</p>
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Collected</p>
            <p class="mt-2 text-3xl font-semibold text-emerald-600">₱{{ number_format($summary['total_collected_amount'] ?? 0, 2) }}</p>
            <p class="mt-1 text-sm text-gray-500">Settled against PN balances</p>
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Outstanding</p>
            <p class="mt-2 text-3xl font-semibold text-amber-600">₱{{ number_format($summary['total_remaining_balance'] ?? 0, 2) }}</p>
            <p class="mt-1 text-sm text-gray-500">Uncollected PN balance</p>
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Overdue</p>
            <p class="mt-2 text-3xl font-semibold text-rose-600">{{ number_format($summary['overdue_count'] ?? 0) }}</p>
            <p class="mt-1 text-sm text-gray-500">Past-due collectible notes</p>
        </div>
    </div>

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Defaulted</p>
            <p class="mt-2 text-2xl font-semibold text-orange-600">{{ number_format($summary['defaulted_count'] ?? 0) }}</p>
            <p class="mt-1 text-sm text-gray-500">End-of-semester overdue notes</p>
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Bad Debt</p>
            <p class="mt-2 text-2xl font-semibold text-red-700">{{ number_format($summary['bad_debt_count'] ?? 0) }}</p>
            <p class="mt-1 text-sm text-gray-500">Year-end delinquent notes</p>
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Closed</p>
            <p class="mt-2 text-2xl font-semibold text-green-700">{{ number_format($summary['closed_count'] ?? 0) }}</p>
            <p class="mt-1 text-sm text-gray-500">Fully settled notes</p>
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Voided</p>
            <p class="mt-2 text-2xl font-semibold text-slate-600">{{ number_format($summary['voided_count'] ?? 0) }}</p>
            <p class="mt-1 text-sm text-gray-500">Expired or rejected notes</p>
        </div>
    </div>

    <div class="grid gap-6 xl:grid-cols-2">
        <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
            <div class="border-b border-gray-100 px-5 py-4">
                <h2 class="text-sm font-semibold uppercase tracking-wide text-gray-700">Overdue Notes</h2>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50 text-xs uppercase tracking-wide text-gray-500">
                        <tr>
                            <th class="px-5 py-3 text-left">Student</th>
                            <th class="px-5 py-3 text-left">PN</th>
                            <th class="px-5 py-3 text-left">Due</th>
                            <th class="px-5 py-3 text-right">Balance</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($overdueNotes as $note)
                            <tr class="align-top hover:bg-gray-50">
                                <td class="px-5 py-4">
                                    <div class="font-medium text-gray-900">{{ $note->student?->full_name ?? '—' }}</div>
                                    <div class="text-xs text-gray-500">{{ $note->student?->student_id ?? '—' }}</div>
                                </td>
                                <td class="px-5 py-4">
                                    <div class="font-medium text-gray-900">#{{ $note->id }}</div>
                                    <div class="text-xs text-gray-500">{{ $note->status }}</div>
                                </td>
                                <td class="px-5 py-4 text-gray-700">
                                    <div>{{ optional($note->due_date)->isoFormat('MMM D, YYYY') }}</div>
                                    <div class="text-xs text-rose-600">
                                        {{ optional($note->due_date)->greaterThan(now()) ? 0 : optional($note->due_date)->diffInDays(now()) ?? 0 }} days overdue
                                    </div>
                                </td>
                                <td class="px-5 py-4 text-right font-semibold text-gray-900">₱{{ number_format((float) $note->remaining_balance, 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-5 py-10 text-center text-gray-500">No overdue notes in this scope.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="border-t border-gray-100 px-5 py-3">
                {{ $overdueNotes->links() }}
            </div>
        </div>

        <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
            <div class="border-b border-gray-100 px-5 py-4">
                <h2 class="text-sm font-semibold uppercase tracking-wide text-gray-700">Defaulted Notes</h2>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50 text-xs uppercase tracking-wide text-gray-500">
                        <tr>
                            <th class="px-5 py-3 text-left">Student</th>
                            <th class="px-5 py-3 text-left">PN</th>
                            <th class="px-5 py-3 text-left">Defaulted</th>
                            <th class="px-5 py-3 text-right">Balance</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($defaultedNotes as $note)
                            <tr class="align-top hover:bg-gray-50">
                                <td class="px-5 py-4">
                                    <div class="font-medium text-gray-900">{{ $note->student?->full_name ?? '—' }}</div>
                                    <div class="text-xs text-gray-500">{{ $note->student?->student_id ?? '—' }}</div>
                                </td>
                                <td class="px-5 py-4">
                                    <div class="font-medium text-gray-900">#{{ $note->id }}</div>
                                    <div class="text-xs text-gray-500">{{ $note->status }}</div>
                                </td>
                                <td class="px-5 py-4 text-gray-700">
                                    <div>{{ optional($note->default_date)->isoFormat('MMM D, YYYY') ?? '—' }}</div>
                                    <div class="text-xs text-orange-600">Due {{ optional($note->due_date)->isoFormat('MMM D, YYYY') }}</div>
                                </td>
                                <td class="px-5 py-4 text-right font-semibold text-gray-900">₱{{ number_format((float) $note->remaining_balance, 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-5 py-10 text-center text-gray-500">No defaulted notes in this scope.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="border-t border-gray-100 px-5 py-3">
                {{ $defaultedNotes->links() }}
            </div>
        </div>
    </div>
</div>
@endsection