@extends('layouts.dashboard')

@section('title', 'Manage Users')
@section('page-title', 'User Management')

@section('content')
<a href="{{ route('college.users.create') }}" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
    + Add User
</a>

<div class="mt-4">
    @if($users->count())
        <table class="w-full border rounded">
            <thead class="bg-gray-100">
                <tr>
                    <th class="p-2 border">Name</th>
                    <th class="p-2 border">Email</th>
                    <th class="p-2 border">Role</th>
                    <th class="p-2 border">Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach($users as $user)
                <tr class="text-center">
                    <td class="p-2 border">{{ $user->name }}</td>
                    <td class="p-2 border">{{ $user->email }}</td>
                    <td class="p-2 border capitalize">{{ str_replace('_', ' ', $user->role) }}</td>
                    <td class="p-2 border">
                        <form action="{{ route('college.users.destroy', $user) }}" method="POST" onsubmit="return confirm('Delete this user?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="px-2 py-1 bg-red-600 text-white rounded hover:bg-red-700">Delete</button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p class="text-gray-500 italic mt-4">No users created yet.</p>
    @endif
</div>
@endsection
