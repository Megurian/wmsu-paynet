@extends('layouts.dashboard')

@section('title', 'Student Details')
@section('page-title', 'Student Details')

@section('content')
<div class="max-w-6xl mx-auto space-y-6">

    <div x-data="{ showModal: false, field: '', value: '' }" class="max-w-6xl mx-auto space-y-6">
        {{-- Back Button --}}
        <div>
            <a href="{{ route('college.students') }}" class="text-blue-600 hover:underline text-sm font-medium">
                ← Back to Directory
            </a>
        </div>

        {{-- Student Info --}}
        <div class="bg-white rounded-2xl shadow-md border p-6 space-y-4">
            <div class="flex flex-col md:flex-row md:justify-between md:items-center">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">
                        {{ $student->last_name }}, {{ $student->first_name }}
                        {{ $student->middle_name }}
                        {{ $student->suffix }}
                        {{-- Edit button --}}
                        <button @click="showModal = true; field='Name'; value='{{ $student->last_name }}, {{ $student->first_name }} {{ $student->middle_name }}. {{ $student->suffix }}'" class="text-blue-600 hover:text-blue-800 text-sm ml-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="red-600" viewBox="0 0 20 20">
                                <path d="M17.414 2.586a2 2 0 00-2.828 0L7 10.172V13h2.828l7.586-7.586a2 2 0 000-2.828zM2 15.5V18h2.5l9.379-9.379-2.5-2.5L2 15.5z" />
                            </svg>
                        </button>
                    </h1>
                    <p class="text-sm text-gray-500 mt-1">
                        Student ID: <span class="font-medium text-gray-700">{{ $student->student_id }}</span>
                    </p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                {{-- Personal Info --}}
                <div class="space-y-4">
                    <div class="bg-gray-100 rounded-xl p-4 shadow-sm flex flex-col justify-center h-20 relative">
                        <span class="text-xs font-semibold text-gray-500">Email</span>
                        <span class="text-sm text-gray-800">{{ $student->email ?? '-' }}</span>
                        <button @click="showModal = true; field='Email'; value='{{ $student->email }}'" class="absolute top-2 right-2 text-blue-600 hover:text-blue-800 text-sm">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="red-600" viewBox="0 0 20 20">
                                <path d="M17.414 2.586a2 2 0 00-2.828 0L7 10.172V13h2.828l7.586-7.586a2 2 0 000-2.828zM2 15.5V18h2.5l9.379-9.379-2.5-2.5L2 15.5z" />
                            </svg>
                        </button>
                    </div>
                    <div class="bg-gray-100 rounded-xl p-4 shadow-sm flex flex-col justify-center h-20 relative">
                        <span class="text-xs font-semibold text-gray-500">Contact</span>
                        <span class="text-sm text-gray-800">{{ $student->contact ?? '-' }}</span>
                        <button @click="showModal = true; field='Contact'; value='{{ $student->contact }}'" class="absolute top-2 right-2 text-blue-600 hover:text-blue-800 text-sm">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="red-600" viewBox="0 0 20 20">
                                <path d="M17.414 2.586a2 2 0 00-2.828 0L7 10.172V13h2.828l7.586-7.586a2 2 0 000-2.828zM2 15.5V18h2.5l9.379-9.379-2.5-2.5L2 15.5z" />
                            </svg>
                        </button>
                    </div>
                </div>
                <div class="space-y-4">
                    <div class="bg-gray-100 rounded-xl p-4 shadow-sm flex flex-col justify-center h-20">
                        <span class="text-xs font-semibold text-gray-500">Course, Year & Section</span>
                        <span class="text-sm text-gray-800">{{ $enrollment?->course?->name ?? '-' }} {{ $enrollment?->yearLevel?->name ?? '-' }} {{ $enrollment?->section?->name ?? '-' }}</span>
                    </div>
                    <div class="bg-gray-100 rounded-xl p-4 shadow-sm flex flex-col justify-center h-20 relative">
                        <span class="text-xs font-semibold text-gray-500">Religion</span>
                        <span class="text-sm text-gray-800">{{ $student->religion ?? '-' }}</span>
                        <button @click="showModal = true; field='Religion'; value='{{ $student->religion ?? '' }}'" class="absolute top-2 right-2 text-blue-600 hover:text-blue-800 text-sm">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="red-600" viewBox="0 0 20 20">
                                <path d="M17.414 2.586a2 2 0 00-2.828 0L7 10.172V13h2.828l7.586-7.586a2 2 0 000-2.828zM2 15.5V18h2.5l9.379-9.379-2.5-2.5L2 15.5z" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Modal --}}
        <div x-show="showModal" x-cloak class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div @click.away="showModal = false" class="bg-white rounded-xl shadow-lg p-6 w-full max-w-md">
                <h3 class="text-lg font-semibold mb-4">Edit <span x-text="field"></span></h3>
                <form :action="`{{ route('college.students.update', $student->id) }}`" method="POST">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="field" :value="field.toLowerCase()">
                    <input type="text" name="value" x-model="value" class="w-full border rounded px-3 py-2 mb-4" required>
                    <div class="flex justify-end space-x-2">
                        <button type="button" @click="showModal = false" class="px-4 py-2 bg-gray-200 rounded">Cancel</button>
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-500">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>


    {{-- Payment & Transaction Details PLACEHOLDEEERRR --}}
    <div class="bg-white rounded-2xl shadow-md border p-6 space-y-6">
        <div class="flex justify-between items-center">
            <h2 class="text-xl font-semibold text-gray-900">Payment & Transaction Details</h2>
            <span class="text-xs text-gray-400 italic">SY & Sem</span>
        </div>

        {{-- Overall Payment Status --}}
        <div class="flex flex-col md:flex-row md:justify-between md:items-center text-sm">
            <p><span class="font-semibold text-gray-800">Overall Payment Status:</span>
                <span class="ml-1 text-yellow-600 font-semibold">Pending</span>
            </p>
            <p><span class="font-semibold text-gray-800">Last Updated:</span> <span class="ml-1 text-gray-600">—</span></p>
        </div>

        <hr class="border-gray-200">

        {{-- Organization Fees --}}
        <div>
            <h3 class="text-gray-800 font-semibold mb-3">Organization Fees</h3>
            <div class="space-y-3">
                {{-- Fee Card Example --}}
                <div class="flex justify-between items-center p-4 border rounded-xl shadow-sm hover:shadow-md transition bg-gray-50">
                    <div class="space-y-1">
                        <p class="font-medium text-gray-900">CSC Fee</p>
                        <p class="text-xs text-gray-500">University Student Council</p>
                    </div>
                    <div class="text-right space-y-1">
                        <p class="text-gray-800 font-semibold">₱ —</p>
                        <p class="text-yellow-600 font-medium text-sm">Unpaid</p>
                        <p class="text-gray-400 text-xs">—</p>
                    </div>
                </div>

                <div class="flex justify-between items-center p-4 border rounded-xl shadow-sm hover:shadow-md transition bg-gray-50">
                    <div class="space-y-1">
                        <p class="font-medium text-gray-900">MSA Fee</p>
                        <p class="text-xs text-gray-500">Muslim Students Association</p>
                    </div>
                    <div class="text-right space-y-1">
                        <p class="text-gray-800 font-semibold">₱ —</p>
                        <p class="text-yellow-600 font-medium text-sm">Unpaid</p>
                        <p class="text-gray-400 text-xs">—</p>
                    </div>
                </div>

                <div class="flex justify-between items-center p-4 border rounded-xl shadow-sm hover:shadow-md transition bg-gray-50">
                    <div class="space-y-1">
                        <p class="font-medium text-gray-900">Membership Fee</p>
                        <p class="text-xs text-gray-500">Venom Publication</p>
                    </div>
                    <div class="text-right space-y-1">
                        <p class="text-gray-800 font-semibold">₱ —</p>
                        <p class="text-gray-400 italic text-sm">Not Set</p>
                        <p class="text-gray-400 text-xs">—</p>
                    </div>
                </div>
            </div>
        </div>

        <hr class="border-gray-200">

        {{-- Transaction History --}}
        <div>
            <h3 class="text-gray-800 font-semibold mb-3">Transaction History</h3>
            <div class="space-y-3">
                {{-- Example Transaction Card --}}
                <div class="flex justify-between items-center p-4 border rounded-xl shadow-sm hover:shadow-md transition bg-gray-50">
                    <div class="space-y-1">
                        <p class="font-medium text-gray-900">Student Council Fee</p>
                        <p class="text-xs text-gray-500">Student Council Org</p>
                        <p class="text-gray-600 text-xs">Transaction ID: #123456</p>
                    </div>
                    <div class="text-right space-y-1">
                        <p class="text-gray-800 font-semibold">₱ 500</p>
                        <p class="text-green-600 font-medium text-sm">Paid</p>
                        <p class="text-gray-400 text-xs">Feb 3, 2026</p>
                    </div>
                </div>

                {{-- Empty placeholder --}}
                <div class="p-4 border rounded-xl shadow-sm text-center text-gray-400 italic">
                    No transactions recorded yet.
                </div>
            </div>
        </div>
    </div>

    <script>
        function openModal(field, value) {
            const modalData = document.querySelector('[x-data]').__x.$data;
            modalData.showModal = true;
            modalData.field = field.charAt(0).toUpperCase() + field.slice(1);
            modalData.value = value || '';
        }

    </script>
</div>

@endsection
