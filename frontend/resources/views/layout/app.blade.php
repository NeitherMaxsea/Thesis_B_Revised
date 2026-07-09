<!DOCTYPE html>
<html lang="en">
<head>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="{{ request()->is('login', 'register', 'register/verification') ? '' : 'page-entering' }} bg-gray-100">
    <noscript>
        <style>
            .page-entering .site-navbar,
            .page-entering main,
            .page-entering .site-footer {
                opacity: 1 !important;
                transform: none !important;
                filter: none !important;
            }
        </style>
    </noscript>

    <x-navbar />

    <main>
        @yield('content')
    </main>

    <x-footer />

    <x-accessibility />

    <x-loading />

</body>
</html>
