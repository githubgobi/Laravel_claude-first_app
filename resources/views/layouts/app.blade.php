<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', config('app.name'))</title>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: system-ui, -apple-system, sans-serif;
            background: #f5f5f5;
            color: #333;
            min-height: 100vh;
        }

        nav {
            background: #fff;
            border-bottom: 1px solid #e5e7eb;
            padding: 0 2rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            height: 60px;
        }

        nav .brand {
            font-weight: 700;
            font-size: 1.2rem;
            color: #4f46e5;
            text-decoration: none;
        }

        nav .nav-links a {
            margin-left: 1.2rem;
            color: #555;
            text-decoration: none;
            font-size: 0.95rem;
        }

        nav .nav-links a:hover { color: #4f46e5; }

        nav .nav-links form { display: inline; }

        nav .nav-links button {
            background: none;
            border: none;
            cursor: pointer;
            color: #555;
            font-size: 0.95rem;
            margin-left: 1.2rem;
        }

        nav .nav-links button:hover { color: #dc2626; }

        .container {
            max-width: 480px;
            margin: 3rem auto;
            padding: 0 1rem;
        }

        .card {
            background: #fff;
            border-radius: 10px;
            padding: 2rem;
            box-shadow: 0 1px 4px rgba(0,0,0,.08);
        }

        .card h1 {
            font-size: 1.5rem;
            margin-bottom: 1.5rem;
            color: #111;
        }

        .form-group { margin-bottom: 1.1rem; }

        label {
            display: block;
            font-size: 0.875rem;
            font-weight: 500;
            margin-bottom: .35rem;
            color: #374151;
        }

        input[type="text"],
        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: .6rem .85rem;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            font-size: 0.95rem;
            transition: border-color .15s;
        }

        input:focus {
            outline: none;
            border-color: #4f46e5;
            box-shadow: 0 0 0 3px rgba(79,70,229,.1);
        }

        .btn {
            width: 100%;
            padding: .7rem;
            background: #4f46e5;
            color: #fff;
            border: none;
            border-radius: 6px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            margin-top: .5rem;
            transition: background .15s;
        }

        .btn:hover { background: #4338ca; }

        .link-row {
            text-align: center;
            margin-top: 1rem;
            font-size: 0.875rem;
            color: #6b7280;
        }

        .link-row a { color: #4f46e5; text-decoration: none; }
        .link-row a:hover { text-decoration: underline; }

        .alert {
            padding: .75rem 1rem;
            border-radius: 6px;
            margin-bottom: 1rem;
            font-size: 0.875rem;
        }

        .alert-error { background: #fef2f2; color: #dc2626; border: 1px solid #fecaca; }
        .alert-success { background: #f0fdf4; color: #16a34a; border: 1px solid #bbf7d0; }

        .error-text { color: #dc2626; font-size: 0.8rem; margin-top: .25rem; }
    </style>
</head>
<body>
    <nav>
        <a href="{{ url('/') }}" class="brand">{{ config('app.name') }}</a>
        <div class="nav-links">
            @guest
                <a href="{{ route('login') }}">Login</a>
                <a href="{{ route('register') }}">Register</a>
            @else
                <span style="font-size:.9rem;color:#555">Hi, {{ Auth::user()->name }}</span>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit">Logout</button>
                </form>
            @endguest
        </div>
    </nav>

    <div class="container">
        @if (session('status'))
            <div class="alert alert-success">{{ session('status') }}</div>
        @endif

        @yield('content')
    </div>
</body>
</html>
