<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>@yield('title', 'Dashboard')</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.3/font/bootstrap-icons.css" rel="stylesheet" />
    <!-- Global CSS -->
    <style>
        :root {
            --sidebar-width: 250px;
            --primary-color: #000000;
            --header-height: 64px;
        }
        
        body {
            background-color: #f8fafc;
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            margin: 0;
            overflow-x: hidden;
        }

        /* Sidebar */
        .admin-sidebar {
            width: var(--sidebar-width);
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            background: #ffffff;
            border-right: 1px solid #e2e8f0;
            z-index: 1020;
            transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            overflow-y: auto;
        }
        
        /* Brand */
        .admin-sidebar__brand {
            height: var(--header-height);
            display: flex;
            align-items: center;
            padding: 0 1.5rem;
            border-bottom: 1px solid transparent; /* Optional separator */
        }
        
        /* Navigation Links */
        .nav-link {
            display: flex;
            align-items: center;
            padding: 0.75rem 1.5rem;
            color: #64748b;
            font-weight: 500;
            font-size: 0.9rem;
            transition: all 0.2s;
            text-decoration: none;
            border-left: 3px solid transparent;
        }
        .nav-link:hover {
            color: #1e293b;
            background-color: #f1f5f9;
        }
        .nav-link.active {
            background-color: #f1f5f9; /* Gray bg */
            color: var(--primary-color);
            border-left-color: var(--primary-color);
        }
        .nav-link i {
            width: 1.5rem;
            font-size: 1.1rem;
            margin-right: 0.5rem;
            display: inline-flex;
            justify-content: center;
        }

        /* Submenus */
        .submenu-toggle { cursor: pointer; justify-content: space-between; }
        .submenu-list { background: #f8fafc; padding: 0.25rem 0; }
        .submenu-link {
            display: block;
            padding: 0.5rem 1.5rem 0.5rem 3.7rem;
            color: #64748b;
            font-size: 0.85rem;
            text-decoration: none;
            transition: color 0.15s;
        }
        .submenu-link:hover { color: #0f172a; }
        .submenu-link.active { color: var(--primary-color); font-weight: 600; }
        .rotate-icon { transition: transform 0.2s; }
        .submenu-toggle[aria-expanded="true"] .rotate-icon { transform: rotate(180deg); }

        /* Main Wrapper */
        .page-wrapper {
            margin-left: var(--sidebar-width);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            transition: margin-left 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        /* Header */
        .admin-header {
            height: var(--header-height);
            background: #ffffff;
            border-bottom: 1px solid #e2e8f0;
            padding: 0 1.5rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 1010;
        }
        
        .header-icon-btn {
            background: transparent;
            border: none;
            color: #64748b;
            padding: 0.5rem;
            border-radius: 50%;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
        .header-icon-btn:hover { background-color: #f1f5f9; color: #1e293b; }

        /* Content Area */
        .main-content {
            padding: 1.5rem;
            flex-grow: 1;
        }

        /* Collapsed State (toggle class on body) */
        body.sidebar-closed .admin-sidebar { transform: translateX(-100%); }
        body.sidebar-closed .page-wrapper { margin-left: 0; }

        /* Mobile specific overrides */
        @media (max-width: 992px) {
            .admin-sidebar { transform: translateX(-100%); }
            .page-wrapper { margin-left: 0; }
            
            /* Show sidebar when active class added on mobile */
            body.sidebar-mobile-open .admin-sidebar { transform: translateX(0); }
        }
        
        /* Utils */
        .admin-sidebar__menu-label {
            font-size: 0.75rem;
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            font-weight: 600;
            margin: 1.5rem 1.5rem 0.5rem;
        }
    </style>
    @stack('styles')
</head>
<body class="">

    <!-- Sidebar Component -->
    @include('admin.layouts.sidebar')

    <!-- Main Page Wrapper -->
    <div class="page-wrapper">
        <!-- Header Component -->
        @include('admin.layouts.header')
        
        <!-- Main Content -->
        <main class="main-content">
            <div id="alertBox"></div>
            @yield('content')
        </main>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const toggle = document.getElementById('sidebarToggle');
            if(toggle) {
                toggle.addEventListener('click', function(e) {
                    e.preventDefault();
                    if (window.innerWidth >= 992) {
                        document.body.classList.toggle('sidebar-closed');
                    } else {
                        document.body.classList.toggle('sidebar-mobile-open');
                    }
                });
            }
        });
    </script>
    
    @stack('scripts')
</body>
</html>
