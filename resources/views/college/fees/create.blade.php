@extends('layouts.dashboard')

@section('title', 'Academic Structure')
@section('page-title', 'Academic Structure')

@section('content')
<form method="POST" action="{{ route('college.fees.store') }}">
    @csrf

    <input name="fee_name" placeholder="Fee Name" required>
    <input name="purpose" placeholder="Purpose" required>
    <textarea name="description"></textarea>
    <input name="amount" type="number" step="0.01">
    
    <select name="requirement_level">
        <option value="mandatory">Mandatory</option>
        <option value="optional">Optional</option>
    </select>

    <button type="submit">Submit for Approval</button>
</form>
@endsection