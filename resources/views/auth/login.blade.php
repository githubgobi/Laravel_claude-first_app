@extends('layouts.app')

@section('title', 'Login')

@section('content')
<div class="card">
    <h1>Login</h1>

    @if ($errors->any())
        <div class="alert alert-error">
            {{ $errors->first() }}
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <div class="form-group">
            <label for="email">Email Address</label>
            <input
                type="email"
                id="email"
                name="email"
                value="{{ old('email') }}"
                required
                autofocus
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

        <button type="submit" class="btn">Login</button>
    </form>

    <p class="link-row">
        Don't have an account? <a href="{{ route('register') }}">Register</a>
    </p>
</div>
@endsection
