@extends('layouts.dashboard')

@section('title', 'Records')
@section('page-title', 'Records')

@section('content')
    <h2 class="text-2xl font-bold mb-4">Records</h2>
    <p>Welcome, {{ Auth::user()->name }}.</p>
@endsection
