@extends('layouts.dashboard')

@section('title', 'OSA Dashboard')
@section('page-title', 'OSA Dashboard')

@section('content')
    <h2 class="text-2xl font-bold mb-4">OSA Dashboard</h2>
    <p>Welcome, {{ Auth::user()->name }}. Here you can manage student affairs and payments.</p>
@endsection
