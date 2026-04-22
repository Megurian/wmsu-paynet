@extends('layouts.dashboard')

@section('title', 'Logs')
@section('page-title', 'System Logs')

@section('content')

<div class="bg-white shadow rounded-lg p-4">

    <h2 class="text-lg font-semibold mb-4">Activity Logs</h2>

    <table class="w-full text-sm border">
        <thead>
            <tr class="bg-gray-100">
                <th class="p-2 text-left">User</th>
                <th class="p-2 text-left">Action</th>
                <th class="p-2 text-left">Student</th>
                <th class="p-2 text-left">Description</th>
                <th class="p-2 text-left">Date</th>
            </tr>
        </thead>

        <tbody>
            @forelse ($logs as $log)
                <tr class="border-t">
                    <td class="p-2">
                        {{ $log->user->first_name ?? '' }} {{ $log->user->middle_name ?? '' }} {{ $log->user->last_name ?? '' }}
                    </td>

                    <td class="p-2 font-semibold">
                        {{ $log->action }}
                    </td>

                    <td class="p-2">
                        {{ $log->student->full_name ?? '-' }}
                    </td>

                    <td class="p-2">
                        {{ $log->description }}
                    </td>

                    <td class="p-2">
                        {{ $log->created_at->format('M d, Y h:i A') }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="p-4 text-center text-gray-500">
                        No logs found.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="mt-4">
        {{ $logs->links() }}
    </div>

</div>

@endsection