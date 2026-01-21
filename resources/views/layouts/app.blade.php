<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', frontend_setting('site_title', 'Get Ready'))</title>
    <meta name="description" content="{{ frontend_setting('site_description', 'Your premier destination for fashion rental. Rent designer pieces for special occasions.') }}">
    <meta name="keywords" content="{{ frontend_setting('site_keywords', 'fashion rental, designer clothes, dress rental, formal wear') }}">

    <!-- jQuery (Load first) -->
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

    <!-- Header and Footer CSS -->
    <link rel="stylesheet" href="{{ asset('css/header.css') }}">
    <link rel="stylesheet" href="{{ asset('css/footer.css') }}">
    <link rel="stylesheet" href="{{ asset('css/layout.css') }}">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="{{ asset('css/home.css') }}">
    @yield('styles')
</head>
<body class="d-flex flex-column min-vh-100">
    @include('layouts.header')

    <main class="flex-grow-1">
        @yield('content')
    </main>

    @include('layouts.footer')

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>

    
    <!-- Cart JS -->
    <script src="{{ asset('js/cart.js') }}"></script>
    
    <!-- Notifications JS -->
    <script src="{{ asset('js/notifications.js') }}"></script>
    
    <!-- Custom JS -->
    <script src="{{ asset('js/home.js') }}"></script>
    @yield('scripts')
</body>
</html> 