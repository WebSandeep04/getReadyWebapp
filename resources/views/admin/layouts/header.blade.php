<div class="header d-flex justify-content-between align-items-center px-3 py-2">
    <!-- Page Title -->
    <div>
        <strong class="text-white">@yield('page_title', 'Dashboard')</strong>
    </div>

    <!-- Welcome and Logout Icon -->
    <div class="d-flex align-items-center">
        <span class="text-white me-3">Welcome, {{ Auth::user()->name ?? 'User' }}</span>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="btn btn-sm text-light p-2" title="Logout">
                <i class="bi bi-power"></i>
            </button>
        </form>
    </div>
</div>
