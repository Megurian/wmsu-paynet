@extends('layouts.dashboard')

@section('title', 'USC Remittance')
@section('page-title', 'USC Remittance')

@section('content')
    <h2 class="text-2xl font-bold mb-4">USC Remittance</h2>
    <p>Welcome, {{ Auth::user()->name }}. placeholder</p>
@endsection
