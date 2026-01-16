@extends('layouts.dashboard')

@section('title', 'USC Setup')
@section('page-title', 'USC Setup')

@section('content')
    <h2 class="text-2xl font-bold mb-4">Admin Setup</h2>
    <p>Welcome, {{ Auth::user()->name }}. placeholder</p>
@endsection
