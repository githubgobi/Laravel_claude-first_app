@extends('layouts.app')

@section('title', 'My Profile')
@section('container-class', 'wide')

@section('content')
<style>
    .profile-container { max-width: 620px; margin: 0 auto; }
    .card + .card { margin-top: 1.5rem; }
    .card h2 { font-size: 1.1rem; font-weight: 600; color: #111; margin-bottom: .35rem; }
    .card p.section-desc { font-size: .85rem; color: #6b7280; margin-bottom: 1.25rem; border-bottom: 1px solid #f3f4f6; padding-bottom: 1rem; }
    .btn-sm { width: auto; padding: .55rem 1.4rem; font-size: .9rem; margin-top: 0; }
    .form-row { display: flex; justify-content: flex-end; margin-top: 1.25rem; }
    .meta { font-size: .8rem; color: #9ca3af; margin-top: 1.25rem; padding-top: 1rem; border-top: 1px solid #f3f4f6; }
</style>

<div class="profile-container">

    {{-- Profile Information --}}
    <div class="card">
        <h2>Profile Information</h2>
        <p class="section-desc">Update your name and email address.</p>

        @if (session('status'))
            <div class="alert alert-success" style="margin-bottom:1rem">{{ session('status') }}</div>
        @endif

        @if ($errors->has('name') || $errors->has('email'))
            <div class="alert alert-error" style="margin-bottom:1rem">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('profile.update') }}">
            @csrf
            @method('PATCH')

            <div class="form-group">
                <label for="name">Full Name</label>
                <input type="text" id="name" name="name"
                    value="{{ old('name', $user->name) }}" required autofocus>
                @error('name') <p class="error-text">{{ $message }}</p> @enderror
            </div>

            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email"
                    value="{{ old('email', $user->email) }}" required>
                @error('email') <p class="error-text">{{ $message }}</p> @enderror
            </div>

            <div class="form-row">
                <button type="submit" class="btn btn-sm">Save Changes</button>
            </div>
        </form>

        <p class="meta">Member since {{ $user->created_at->format('F j, Y') }}</p>
    </div>

    {{-- Update Password --}}
    <div class="card">
        <h2>Update Password</h2>
        <p class="section-desc">Use a strong password of at least 8 characters.</p>

        @if (session('password_status'))
            <div class="alert alert-success" style="margin-bottom:1rem">{{ session('password_status') }}</div>
        @endif

        @if ($errors->has('current_password') || $errors->has('password'))
            <div class="alert alert-error" style="margin-bottom:1rem">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('profile.password') }}">
            @csrf
            @method('PUT')

            <div class="form-group">
                <label for="current_password">Current Password</label>
                <input type="password" id="current_password" name="current_password" required>
                @error('current_password') <p class="error-text">{{ $message }}</p> @enderror
            </div>

            <div class="form-group">
                <label for="password">New Password</label>
                <input type="password" id="password" name="password" required>
                @error('password') <p class="error-text">{{ $message }}</p> @enderror
            </div>

            <div class="form-group">
                <label for="password_confirmation">Confirm New Password</label>
                <input type="password" id="password_confirmation" name="password_confirmation" required>
            </div>

            <div class="form-row">
                <button type="submit" class="btn btn-sm">Update Password</button>
            </div>
        </form>
    </div>

</div>
@endsection
