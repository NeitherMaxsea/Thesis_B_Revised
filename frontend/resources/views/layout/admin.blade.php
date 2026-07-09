<!DOCTYPE html>
<html lang="en">
<head>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="page-entering bg-gray-100">
    <header class="dashboard-navbar">
        <a href="{{ route('admin.dashboard') }}" class="dashboard-brand" aria-label="Admin dashboard">
            <img src="{{ asset('images/job-employment-logo.png') }}" alt="Job Employment Personal with Disabilities">
            <span>Admin Dashboard</span>
        </a>

        <div class="dashboard-actions">
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="dashboard-secondary-action px-4">Logout</button>
            </form>
        </div>
    </header>

    <main class="dashboard-main">
        @yield('content')
    </main>

    <x-accessibility />
    <x-loading />
</body>
</html>
