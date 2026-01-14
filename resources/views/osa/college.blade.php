@extends('layouts.dashboard')

@section('title', 'OSA College')
@section('page-title', 'OSA College')

@section('content')
    <h2 class="text-2xl font-bold mb-4">OSA College Setup</h2>
    <p>Welcome, {{ Auth::user()->name }}. </p>
@endsection
