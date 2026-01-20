@extends('layouts.dashboard')

@section('title', 'Reports')
@section('page-title', ($organization?->org_code ?? 'Organization') . ' Reports')

@section('content')
    <h2 class="text-2xl font-bold mb-4"> {{ ($organization?->org_code ?? 'Organization') . " Reports" }} </h2>
    <p>Welcome, {{ Auth::user()->name }}. placeholder</p>
@endsection
