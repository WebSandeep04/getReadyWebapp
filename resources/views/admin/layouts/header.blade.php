<header class="admin-header">
    <div class="d-flex align-items-center gap-3">
        <button class="btn btn-link link-dark p-0 border-0 text-decoration-none" id="sidebarToggle">
            <i class="bi bi-list fs-4"></i>
        </button>
        <h5 class="mb-0 fw-bold text-dark">@yield('page_title', 'Dashboard')</h5>
    </div>
    
    <div>
        <!-- Logout Power Button -->
        <form method="POST" action="{{ route('admin.logout') }}" class="d-inline">
            @csrf
            <button type="submit" class="header-icon-btn text-dark" title="Logout">
                <i class="bi bi-power"></i>
            </button>
        </form>
    </div>
</header>
