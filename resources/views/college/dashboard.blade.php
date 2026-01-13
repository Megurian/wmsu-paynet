@extends('layouts.dashboard')

@section('title', 'College Dashboard')
@section('page-title', 'College Dashboard')

@section('content')
    <h2 class="text-2xl font-bold mb-4">College Dashboard</h2>
    <p>Welcome, {{ Auth::user()->name }}.</p>
@endsection
