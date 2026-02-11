@extends('layouts.dashboard')

@section('title', 'Academic Structure')
@section('page-title', 'Academic Structure')

@section('content')
@foreach($fees as $fee)
    <div>
        <strong>{{ $fee->fee_name }}</strong>
        <form method="POST" action="{{ route('college.fees.approve', $fee) }}">
            @csrf
            <button>Approve</button>
        </form>

        <form method="POST" action="{{ route('college.fees.reject', $fee) }}">
            @csrf
            <button>Reject</button>
        </form>
    </div>
@endforeach
@endsection