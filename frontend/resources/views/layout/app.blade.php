<!DOCTYPE html>
<html lang="en">
<head>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
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
