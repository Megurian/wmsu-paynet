@extends('layouts.dashboard')

@section('title', 'USC Dashboard')
@section('page-title', 'USC Dashboard')

@section('content')
    <h2 class="text-2xl font-bold mb-4">USC Dashboard</h2>
    <p>Welcome, {{ Auth::user()->name }}. Here you can review student requests and approvals.</p>
@endsection
