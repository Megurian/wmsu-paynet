@extends('layouts.dashboard')

@section('title', 'Logs')
@section('page-title', 'System Logs')

@section('content')

<div class="bg-white shadow rounded-xl p-6">

    <div class="flex items-center justify-between mb-5">
        <h2 class="text-xl font-semibold text-gray-800">Activity Logs</h2>
        <span class="text-sm text-gray-500">
            Total: {{ $logs->total() }}
        </span>
    </div>

    <div class="overflow-x-auto rounded-lg border">
        <table class="min-w-full text-sm">
            <thead class="bg-gray-100 text-gray-700 text-xs uppercase sticky top-0">
                <tr>
                    <th class="p-3 text-left">User</th>
                    <th class="p-3 text-left">Action</th>
                    <th class="p-3 text-left">Student</th>
                    <th class="p-3 text-left">Description</th>
                    <th class="p-3 text-left">Date</th>
                </tr>
            </thead>

            <tbody class="divide-y divide-gray-200">
                @forelse ($logs as $log)
                    <tr class="hover:bg-gray-50 transition">

                        {{-- USER --}}
                        <td class="p-3 whitespace-nowrap">
                            <div class="font-medium text-gray-800">
                                {{ trim(($log->user->first_name ?? '') . ' ' . ($log->user->last_name ?? '')) ?: '-' }}
                            </div>
                            <div class="text-xs text-gray-500">
                                ID: {{ $log->user_id ?? '-' }}
                            </div>
                        </td>

                        {{-- ACTION --}}
                        <td class="p-3">
                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full
                                bg-blue-100 text-blue-700">
                                {{ $log->action }}
                            </span>
                        </td>

                        {{-- STUDENT --}}
                        <td class="p-3 text-gray-700">
                            {{ $log->student->full_name ?? '-' }}
                        </td>

                        {{-- DESCRIPTION --}}
                        <td class="p-3 text-gray-600 max-w-md break-words">
                            {{ $log->description ?? '-' }}
                        </td>

                        {{-- DATE --}}
                        <td class="p-3 whitespace-nowrap text-gray-500 text-xs">
                            {{ $log->created_at->format('M d, Y h:i A') }}
                        </td>

                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="p-6 text-center text-gray-500">
                            No logs found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4 flex justify-end">
        {{ $logs->links() }}
    </div>

</div>

@endsection