@extends('layouts.dashboard')

@section('title', 'OSA Fees')
@section('page-title', 'OSA Fees')

@section('content')
<div class="mb-8">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-3xl font-bold text-gray-800">OSA Fee Approval</h2>
            <p class="text-sm text-gray-500 mt-1">Welcome, {{ Auth::user()->name }}. Here you can manage the fees associated with different colleges within the university.</p>
        </div>
        <div>
            <a href="{{ route('osa.fees.create') }}" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">Create Fee</a>
        </div>
    </div>
    @if(session('success'))
        <div class="mb-4 p-4 bg-green-100 text-green-800 rounded">
            {{ session('success') }}
        </div>
    @endif
</div>

<!-- Fees Section -->
<div class="bg-gray rounded shadow p-6">
        <div class="mb-6 flex space-x-2">
            <a href="{{ route('osa.fees', ['status' => 'pending']) }}"
            class="px-4 py-2 rounded-full font-medium text-sm transition
            {{ $status === 'pending' ? 'bg-red-800 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }}">
                Pending Fees
                <span class="ml-2 inline-block bg-white text-red-800 font-semibold text-xs px-2 py-0.5 rounded-full">
                    {{ $pendingFees->count() }}
                </span>
            </a>
            <a href="{{ route('osa.fees', ['status' => 'approved']) }}"
            class="px-4 py-2 rounded-full font-medium text-sm transition
            {{ $status === 'approved' ? 'bg-red-800 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }}">
                All
            </a>
        </div>
        
        @if($status === 'pending')
    <h3 class="text-xl font-semibold mb-4">Pending Fees</h3>
    <p class="text-gray-500 italic">Pending fee approval requests for every organization will appear here.</p> <br>

    @forelse($pendingFees as $fee)
            <div class="bg-white shadow rounded-lg p-4 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <!-- Fee Info -->
                <div class="flex-1">
                    <h3 class="text-lg font-semibold text-gray-800">{{ $fee->fee_name }} 
                        @if($fee->appeals->where('status','pending')->count() > 0)
                        <span class="ml-2 inline-block px-2 py-0.5 text-xs font-semibold bg-yellow-100 text-yellow-800 rounded">Appeal Pending</span>
                    @endif
                    </h3>
                    <p class="text-sm text-gray-500">
                        <span class="capitalize font-medium">{{ $fee->requirement_level }}</span>
                    </p>

                    {{-- SOURCE DISPLAY --}}
                    <p class="text-sm text-gray-600 mt-1">
                        From:
                        <span class="font-semibold">
                                {{ $fee->organization->name }} ({{ $fee->organization->org_code }})
                        </span>
                    </p>
                    <p class="text-sm text-gray-400 mt-1">Submitted on: {{ $fee->created_at->format('M d, Y') }}</p>
                </div>

                <div class="flex gap-2 md:gap-3">
                    <form method="GET" action="{{ route('osa.fees.show', $fee->id) }}">
                        @csrf
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-green-700 transition">
                            View
                        </button>
                    </form>
                </div>
            </div>
            <br>
        @empty
            <div class="text-center text-gray-500 py-6">
                No pending fees to review.
            </div>
        @endforelse

    @elseif ($status === 'approved')
    <!-- Approved Fees Section -->
    <div class="mt-8">
        <h3 class="text-xl font-semibold mb-4">Approved Fees</h3>
            @forelse($approvedFees as $fee)
            <div class="bg-white shadow rounded-lg p-4 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div class="flex-1">
                    <h3 class="text-lg font-semibold text-gray-800">{{ $fee->fee_name }}</h3>
                    <p class="text-sm text-gray-500"> 
                        <span class="capitalize font-medium">{{ $fee->requirement_level }}</span>
                    </p>
                    <p class="text-sm text-gray-600 mt-1">
                        From:
                        <span class="font-semibold">
                                {{ $fee->organization->name }} ({{ $fee->organization->org_code }})
                        </span>
                    </p>
                    <p class="text-sm text-gray-400 mt-1">
                        Approved on: {{ optional(\Illuminate\Support\Carbon::parse($fee->approved_at))->format('M d, Y') }}
                    </p>
                </div>
                <div class="flex gap-2 md:gap-3">
                    <form method="GET" action="{{ route('osa.fees.show', $fee->id) }}">
                        @csrf
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-green-700 transition">
                            View
                        </button>
                    </form>
                </div>
            </div>
            <br>
            @empty
                <div class="text-center text-gray-500 py-6">
                    No approved fees yet.
                </div>
            @endforelse
        </div>
    @endif
</div>

<script>
function toggleMenu(menuId) {
    document.querySelectorAll('[id^="menu-"]').forEach(menu => {
        if (menu.id !== menuId) {
            menu.classList.add('hidden');
        }
    });
    
    const menu = document.getElementById(menuId);
    if (menu) {
        menu.classList.toggle('hidden');
    }
}

document.addEventListener('click', function(event) {
    if (!event.target.matches('button') && !event.target.closest('.relative')) {
        document.querySelectorAll('[id^="menu-"]').forEach(menu => {
            menu.classList.add('hidden');
        });
    }
});
</script>

<style>
[id^="menu-"] {
    transition: opacity 0.2s ease-in-out;
}
</style>
@endsection