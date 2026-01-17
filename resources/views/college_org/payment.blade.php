@extends('layouts.dashboard')

@section('title', 'Payment')
@section('page-title', 'Payment')

@section('content')
    <h2 class="text-2xl font-bold mb-4">Payment</h2>
    <p>Welcome, {{ Auth::user()->name }}.</p>
@endsection
