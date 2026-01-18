@extends('layouts.dashboard')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')
    <h2 class="text-2xl font-bold mb-4">Dashboard</h2>
    <p>Welcome, {{ Auth::user()->name }}. </p>
@endsection