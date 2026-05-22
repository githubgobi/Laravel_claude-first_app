@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="card">
    <h1>Dashboard</h1>
    <p style="color:#6b7280;margin-top:.5rem">
        Welcome back, <strong>{{ Auth::user()->name }}</strong>!
        You are logged in as <strong>{{ Auth::user()->email }}</strong>.
    </p>
</div>
@endsection
