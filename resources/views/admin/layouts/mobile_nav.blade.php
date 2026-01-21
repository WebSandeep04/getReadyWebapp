<nav class="navbar navbar-dark text-white bg-dark border-bottom shadow-sm d-md-none mobile-nav">
    <div class="container-fluid d-flex justify-content-between align-items-center">
        <span class="fw-bold">Get Ready</span>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mobileSidebarMenu" aria-controls="mobileSidebarMenu" aria-expanded="false" aria-label="Toggle navigation">
            <i class="bi bi-list"></i>
        </button>
    </div>

    <div class="collapse" id="mobileSidebarMenu">
        <div class="d-flex flex-column p-3 rounded-3" id="mobileSidebarMenuContent">
            <a href="/" class="d-flex align-items-center text-decoration-none mb-2 text-light">
                <i class="bi bi-house me-2"></i> Home
            </a>
            @if(Auth::check())
                <a href="{{ route('sell') }}" class="d-flex align-items-center text-decoration-none mb-2 text-light">
                    <i class="bi bi-plus-circle me-2"></i> Sell
                </a>
            @else
                <a href="{{ route('login', ['redirect' => route('sell')]) }}" class="d-flex align-items-center text-decoration-none mb-2 text-light">
                    <i class="bi bi-plus-circle me-2"></i> Sell
                </a>
            @endif

            @if(Auth::check())
                <div class="d-flex align-items-center mb-2">
                    <span class="rounded-circle d-inline-block text-center align-middle bg-secondary text-white me-2" style="width:32px; height:32px; line-height:32px; font-size:1.1rem; overflow:hidden; vertical-align:middle;">
                        @if(Auth::user()->profile_image)
                            <img src="{{ asset(Auth::user()->profile_image) }}" alt="Profile" style="width:100%; height:100%; object-fit:cover; border-radius:50%;">
                        @else
                            {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                        @endif
                    </span>
                    <span class="fw-bold">{{ Auth::user()->name }}</span>
                </div>
                <form method="POST" action="{{ route('logout') }}" class="mt-2">
                    @csrf
                    <button type="submit" class="btn btn-logout w-100">
                        <i class="bi bi-box-arrow-right me-2"></i> Logout
                    </button>
                </form>
            @else
                <a href="{{ route('login') }}" class="btn btn-outline-light w-100 mb-2">
                    <i class="bi bi-box-arrow-in-right me-2"></i> Login
                </a>
            @endif
        </div>
    </div>
</nav>
