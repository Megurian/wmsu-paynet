@extends('layouts.dashboard')

@section('title', 'USC Reports')
@section('page-title', 'USC Reports')

@section('content')
    <h2 class="text-2xl font-bold mb-4">Reports</h2>
    <p>Welcome, {{ Auth::user()->name }}. placeholder</p>
@endsection
