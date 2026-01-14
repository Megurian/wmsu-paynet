@extends('layouts.dashboard')

@section('title', 'USC Fees')
@section('page-title', 'USC Fees')

@section('content')
    <h2 class="text-2xl font-bold mb-4">USC Setup of Fees</h2>
    <p>Welcome, {{ Auth::user()->name }}. placeholder</p>
@endsection
