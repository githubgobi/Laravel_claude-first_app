@extends('layouts.app')

@section('title', 'Forgot Password')

@section('content')
<div class="card">
    <h1>Forgot Password</h1>
    <p style="color:#6b7280;font-size:.9rem;margin-bottom:1.2rem">
        Enter your email address and we'll send you a reset link.
    </p>

    @if (session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    @if ($errors->any())
        <div class="alert alert-error">{{ $errors->first() }}</div>
    @endif

    <form method="POST" action="{{ route('password.email') }}">
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

        <button type="submit" class="btn">Send Reset Link</button>
    </form>

    <p class="link-row">
        <a href="{{ route('login') }}">&larr; Back to Login</a>
    </p>
</div>
@endsection
