@extends('layouts.dashboard')

@section('title', 'Fee Details')
@section('page-title', 'Fee Details')

@section('content')
<div class="mb-6">
    <a href="{{ route('college_org.fees') }}" class="text-sm text-gray-600 hover:underline">&larr; Back to Fees</a>
</div>

<div class="bg-white rounded shadow p-6 max-w-3xl">
    <h2 class="text-2xl font-bold mb-4">{{ $fee->fee_name }}</h2>
    <p class="text-sm text-gray-600 mb-2">Organization: {{ $fee->organization->name }} ({{ $fee->organization->org_code }})</p>
    <p class="text-sm text-gray-600 mb-2">Amount: ₱{{ number_format($fee->amount, 2) }}</p>
    <p class="text-sm text-gray-600 mb-2">Requirement: {{ ucfirst($fee->requirement_level) }}</p>
    <p class="text-sm text-gray-600 mb-4">Status: {{ ucfirst($fee->status) }}</p>

    @if($fee->accreditation_file)
        <p class="mb-2"><a href="{{ \Illuminate\Support\Facades\Storage::url($fee->accreditation_file) }}" target="_blank" class="text-blue-600 hover:underline">View Certificate of Accreditation</a></p>
    @endif
    @if($fee->resolution_file)
        <p class="mb-4"><a href="{{ \Illuminate\Support\Facades\Storage::url($fee->resolution_file) }}" target="_blank" class="text-blue-600 hover:underline">View Resolution of Collection</a></p>
    @endif

    @if($fee->status === 'disabled')
        <div class="mt-6">
            <h3 class="text-lg font-medium">Submit Appeal</h3>
            <form method="POST" action="{{ route('college_org.fees.appeal', $fee->id) }}" enctype="multipart/form-data" class="mt-4">
                @csrf
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">Reason</label>
                    <textarea name="reason" required class="w-full border rounded px-3 py-2" rows="4"></textarea>
                    @error('reason') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">Supporting Files (optional, up to 10)</label>
                    <input type="file" name="supporting_files[]" accept=".pdf,image/*" multiple class="w-full" />
                    @error('supporting_files') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
                </div>

                <button type="submit" class="px-4 py-2 bg-red-700 text-white rounded">Submit Appeal</button>
            </form>
        </div>
    @endif

    <div class="mt-8">
        <h3 class="text-lg font-medium">Appeal History</h3>
        @if($fee->appeals && $fee->appeals->count())
            <ul class="mt-3 space-y-3">
                @foreach($fee->appeals as $appeal)
                    <li class="p-3 border rounded bg-gray-50">
                        <p class="text-sm font-medium">Status: {{ ucfirst($appeal->status) }}</p>
                        <p class="text-sm text-gray-600">Submitted by: {{ $appeal->user->name ?? 'N/A' }} on {{ $appeal->created_at->format('Y-m-d') }}</p>
                        <p class="text-sm mt-2">{{ $appeal->reason }}</p>
                        @if($appeal->supporting_files)
                            <p class="text-sm mt-2">Supporting files:</p>
                            <ul class="list-disc pl-5">
                                @foreach($appeal->supporting_files as $file)
                                    <li><a href="{{ \Illuminate\Support\Facades\Storage::url($file) }}" target="_blank" class="text-blue-600 hover:underline">View</a></li>
                                @endforeach
                            </ul>
                        @endif
                        @if($appeal->review_remark)
                            <p class="mt-2 text-sm text-gray-700">Review: {{ $appeal->review_remark }} (by {{ $appeal->reviewer?->name ?? 'OSA' }} on {{ $appeal->reviewed_at?->format('Y-m-d') }})</p>
                        @endif
                    </li>
                @endforeach
            </ul>
        @else
            <p class="text-sm text-gray-500 mt-2">No appeals yet.</p>
        @endif
    </div>
</div>
@endsection