@extends('layouts.app')

@section('title', 'Register')

@section('content')
<div class="card">
    <h1>Create Account</h1>

    @if ($errors->any())
        <div class="alert alert-error">
            {{ $errors->first() }}
        </div>
    @endif

    <form method="POST" action="{{ route('register') }}">
        @csrf

        <div class="form-group">
            <label for="name">Full Name</label>
            <input
                type="text"
                id="name"
                name="name"
                value="{{ old('name') }}"
                required
                autofocus
            >
            @error('name')
                <p class="error-text">{{ $message }}</p>
            @enderror
        </div>

        <div class="form-group">
            <label for="email">Email Address</label>
            <input
                type="email"
                id="email"
                name="email"
                value="{{ old('email') }}"
                required
            >
            @error('email')
                <p class="error-text">{{ $message }}</p>
            @enderror
        </div>

        <div class="form-group">
            <label for="password">Password</label>
            <input
                type="password"
                id="password"
                name="password"
                required
            >
            @error('password')
                <p class="error-text">{{ $message }}</p>
            @enderror
        </div>

        <div class="form-group">
            <label for="password_confirmation">Confirm Password</label>
            <input
                type="password"
                id="password_confirmation"
                name="password_confirmation"
                required
            >
        </div>

        <button type="submit" class="btn">Create Account</button>
    </form>

    <p class="link-row">
        Already have an account? <a href="{{ route('login') }}">Login</a>
    </p>
</div>
@endsection
