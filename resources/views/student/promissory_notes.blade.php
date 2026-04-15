@extends('student.layouts.student-dashboard')

@section('title', 'Promissory Notes')
@section('page-title', 'Promissory Notes')

@section('content')
@php
    $statusCounts = [
        'PENDING_SIGNATURE' => $promissoryNotes->where('status', 'PENDING_SIGNATURE')->count(),
        'PENDING_VERIFICATION' => $promissoryNotes->where('status', 'PENDING_VERIFICATION')->count(),
        'ACTIVE' => $promissoryNotes->where('status', 'ACTIVE')->count(),
        'VOIDED' => $promissoryNotes->where('status', 'VOIDED')->count(),
        'CLOSED' => $promissoryNotes->where('status', 'CLOSED')->count(),
        'DEFAULT' => $promissoryNotes->where('status', 'DEFAULT')->count(),
        'BAD_DEBT' => $promissoryNotes->where('status', 'BAD_DEBT')->count(),
    ];

    $badgeClasses = [
        'PENDING_SIGNATURE' => 'bg-amber-100 text-amber-800 border-amber-200',
        'PENDING_VERIFICATION' => 'bg-sky-100 text-sky-800 border-sky-200',
        'ACTIVE' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
        'VOIDED' => 'bg-slate-100 text-slate-700 border-slate-200',
        'CLOSED' => 'bg-green-100 text-green-800 border-green-200',
        'DEFAULT' => 'bg-orange-100 text-orange-800 border-orange-200',
        'BAD_DEBT' => 'bg-red-100 text-red-800 border-red-200',
    ];
@endphp

<div class="mb-8 flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
    <div>
        <h2 class="text-2xl font-bold text-gray-900 sm:text-3xl">Promissory Notes</h2>
        <p class="mt-1 text-sm text-gray-500">Download, sign, upload, and track promissory-note status from one place.</p>
    </div>
    <div class="grid grid-cols-2 gap-2 sm:grid-cols-4 xl:grid-cols-7">
        @foreach($statusCounts as $status => $count)
            <div class="rounded-xl border border-gray-200 bg-white px-3 py-2 shadow-sm">
                <div class="text-[11px] font-semibold uppercase tracking-wide text-gray-500">{{ str_replace('_', ' ', $status) }}</div>
                <div class="text-xl font-bold text-gray-900">{{ $count }}</div>
            </div>
        @endforeach
    </div>
</div>

@if($promissoryNotes->isEmpty())
    <div class="rounded-2xl border border-dashed border-gray-300 bg-white p-10 text-center text-gray-500">
        No promissory notes are available for this account.
    </div>
@else
    <div class="space-y-5">
        @foreach($promissoryNotes as $note)
            <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                    <div class="flex-1 space-y-3">
                        <div class="flex flex-wrap items-center gap-3">
                            <h3 class="text-xl font-semibold text-gray-900">PN #{{ $note->id }}</h3>
                            <span class="inline-flex rounded-full border px-3 py-1 text-xs font-semibold {{ $badgeClasses[$note->status] ?? 'bg-gray-100 text-gray-700 border-gray-200' }}">{{ $note->status }}</span>
                            <span class="text-sm text-gray-500">Due {{ optional($note->due_date)->format('M d, Y') }}</span>
                        </div>

                        <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
                            <div class="rounded-xl bg-gray-50 p-3">
                                <div class="text-xs font-semibold uppercase tracking-wide text-gray-500">Original Amount</div>
                                <div class="mt-1 text-lg font-bold text-gray-900">₱{{ number_format((float) $note->original_amount, 2) }}</div>
                            </div>
                            <div class="rounded-xl bg-gray-50 p-3">
                                <div class="text-xs font-semibold uppercase tracking-wide text-gray-500">Remaining Balance</div>
                                <div class="mt-1 text-lg font-bold text-gray-900">₱{{ number_format((float) $note->remaining_balance, 2) }}</div>
                            </div>
                            <div class="rounded-xl bg-gray-50 p-3">
                                <div class="text-xs font-semibold uppercase tracking-wide text-gray-500">Signature Deadline</div>
                                <div class="mt-1 text-sm font-semibold text-gray-900">{{ optional($note->signature_deadline)->format('M d, Y h:i A') }}</div>
                            </div>
                            <div class="rounded-xl bg-gray-50 p-3">
                                <div class="text-xs font-semibold uppercase tracking-wide text-gray-500">Issued By</div>
                                <div class="mt-1 text-sm font-semibold text-gray-900">{{ $note->issuedBy->full_name ?? '—' }}</div>
                            </div>
                        </div>

                        <div class="rounded-xl border border-gray-200">
                            <div class="border-b border-gray-200 bg-gray-50 px-4 py-2 text-sm font-semibold text-gray-700">Deferred Fees</div>
                            <div class="space-y-3 p-4 md:hidden">
                                @foreach($note->fees as $fee)
                                    <div class="rounded-xl border border-gray-200 bg-white p-3">
                                        <div class="flex items-start justify-between gap-3">
                                            <div>
                                                <div class="text-sm font-semibold text-gray-900">{{ $fee->fee_name }}</div>
                                                <div class="mt-1 text-xs text-gray-500">{{ $fee->organization->name ?? ($fee->college->name ?? 'College') }}</div>
                                            </div>
                                            <div class="text-sm font-semibold text-gray-900">₱{{ number_format((float) $fee->pivot->amount_deferred, 2) }}</div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <div class="overflow-x-auto hidden md:block">
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

                        @if($note->status === 'PENDING_SIGNATURE')
                            <div class="rounded-xl border border-amber-200 bg-amber-50 p-4">
                                <p class="font-semibold text-amber-900">Action required</p>
                                <p class="mt-1 text-sm text-amber-800">Download the template, sign it, and upload the signed copy before the deadline.</p>
                            </div>
                        @elseif($note->status === 'PENDING_VERIFICATION')
                            <div class="rounded-xl border border-sky-200 bg-sky-50 p-4">
                                <p class="font-semibold text-sky-900">Awaiting coordinator review</p>
                                <p class="mt-1 text-sm text-sky-800">Your signed note has been uploaded. No further action is needed until review is complete.</p>
                            </div>
                        @elseif(in_array($note->status, ['DEFAULT', 'BAD_DEBT']))
                            <div class="rounded-xl border border-red-200 bg-red-50 p-4">
                                <p class="font-semibold text-red-900">Settlement pending</p>
                                <p class="mt-1 text-sm text-red-800">This note remains settleable even if defaulted. Contact the cashier if you need to clear the balance.</p>
                            </div>
                        @endif
                    </div>

                    <div class="w-full max-w-md rounded-2xl border border-gray-200 bg-gray-50 p-4 lg:w-[28rem]">
                        @if($note->status === 'PENDING_SIGNATURE')
                            <div class="space-y-3">
                                <a href="{{ route('student.promissory_notes.download', $note) }}" class="inline-flex w-full items-center justify-center rounded-xl bg-red-800 px-4 py-2 text-sm font-semibold text-white transition hover:bg-red-700">
                                    Download Template
                                </a>

                                <form method="POST" action="{{ route('student.promissory_notes.sign', $note) }}" enctype="multipart/form-data" class="space-y-3">
                                    @csrf
                                    <label class="block text-sm font-semibold text-gray-700">Upload Signed Copy</label>
                                    <input type="file" name="signed_note" accept=".pdf,.jpg,.jpeg,.png" class="block w-full rounded-xl border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700">
                                    @error('signed_note')
                                        <p class="text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                    <button type="submit" class="inline-flex w-full items-center justify-center rounded-xl bg-emerald-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-emerald-500">
                                        Upload Signed Note
                                    </button>
                                </form>
                            </div>
                        @elseif($note->status === 'PENDING_VERIFICATION')
                            <div class="space-y-3">
                                <div class="rounded-xl border border-sky-200 bg-white p-4 text-sm text-sky-900">
                                    Uploaded on {{ optional($note->signed_at)->format('M d, Y h:i A') ?? '—' }}.
                                </div>
                                <div class="rounded-xl border border-dashed border-gray-300 bg-white p-4 text-sm text-gray-600">
                                    The coordinator is reviewing your submission.
                                </div>
                            </div>
                        @else
                            <div class="rounded-xl border border-gray-200 bg-white p-4 text-sm text-gray-700">
                                Status updates appear here after the note is reviewed.
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endif
@endsection