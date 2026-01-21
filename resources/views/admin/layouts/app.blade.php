<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>@yield('title', 'Dashboard')</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.3/font/bootstrap-icons.css" rel="stylesheet" />
    <link rel="stylesheet" href="{{ asset('css/layout.css') }}">
    @stack('styles')
</head>
<body>

<div class="d-flex">
    {{-- Desktop Sidebar (hidden on mobile) --}}
    <div class="d-none d-md-block">
        @include('admin/layouts/sidebar')
    </div>

    <div class="content flex-grow-1">
        {{-- Desktop Header (optional: visible only on desktop) --}}
        <div class="d-none d-md-block">
            @include('admin/layouts/header')
        </div>

        {{-- Mobile NavBar (visible only on mobile) --}}
        <div class="d-md-none">
            @include('admin/layouts/mobile_nav')
        </div>

        <div class="main p-3">
            {{-- Alert box area --}}
            <div class="alert-container" id="alertBox"></div>
            
            {{-- Page content --}}
            @yield('content')
        </div>
    </div>
</div>

<script src="{{ asset('js/layout.js') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
@stack('scripts')
</body>
</html>
