@php
    $adminSidebar = [
        [
            'title' => 'Setup',
            'icon' => 'bi-gear',
            'links' => [
                ['label' => 'Users', 'route' => 'user.index', 'icon' => 'bi-people'],
                ['label' => 'Categories', 'route' => 'categories.index', 'icon' => 'bi-tags'],
                ['label' => 'Brands', 'route' => 'brands.index', 'icon' => 'bi-award'],
                ['label' => 'Fabric Types', 'route' => 'fabric_types.index', 'icon' => 'bi-patch-check'],
                ['label' => 'Colors', 'route' => 'colors.index', 'icon' => 'bi-palette'],
                ['label' => 'Bottom Types', 'route' => 'bottom_types.index', 'icon' => 'bi-slack'],
                ['label' => 'Sizes', 'route' => 'sizes.index', 'icon' => 'bi-arrows-expand'],
                ['label' => 'Body Type Fits', 'route' => 'body_type_fits.index', 'icon' => 'bi-person-bounding-box'],
                ['label' => 'Garment Conditions', 'route' => 'garment_conditions.index', 'icon' => 'bi-shield-check'],
                ['label' => 'Frontend Settings', 'route' => 'admin.frontend', 'icon' => 'bi-globe'],
            ],
        ],
        [
            'title' => 'Approval',
            'icon' => 'bi-check2-square',
            'links' => [
                ['label' => 'Clothes Approval', 'route' => 'admin.cloth-approval', 'icon' => 'bi-bag-check'],
            ],
        ],
        [
            'title' => 'Operations',
            'icon' => 'bi-clipboard-data',
            'links' => [
                ['label' => 'Orders', 'route' => 'admin.orders', 'icon' => 'bi-receipt'],
            ],
        ],
    ];
@endphp

<aside class="admin-sidebar d-flex flex-column">
    <div class="admin-sidebar__brand">
        <button class="admin-sidebar__toggle-btn" id="sidebarToggle">
            <i class="bi bi-list"></i>
        </button>
        <span class="admin-sidebar__logo">GR</span>
        <div class="admin-sidebar__brand-text">
            <div class="fw-bold text-dark small">Get Ready</div>
            <small class="text-muted">Admin Panel</small>
        </div>
    </div>

    <nav class="admin-sidebar__nav flex-grow-1">
        <a href="{{ route('admin.dashboard') }}" class="admin-sidebar__link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
            <span>Dashboard</span>
            <i class="bi bi-speedometer2"></i>
        </a>
        @foreach($adminSidebar as $index => $section)
            <div class="admin-sidebar__section">
                <button class="admin-sidebar__toggle" data-bs-toggle="collapse" data-bs-target="#sidebar-section-{{ $index }}" aria-expanded="false">
                    <span><i class="bi {{ $section['icon'] }} me-2"></i>{{ $section['title'] }}</span>
                    <i class="bi bi-chevron-down"></i>
                </button>
                <div class="collapse" id="sidebar-section-{{ $index }}">
                    @foreach($section['links'] as $link)
                        <a href="{{ route($link['route']) }}" class="admin-sidebar__link {{ request()->routeIs($link['route']) ? 'active' : '' }}">
                            <span>{{ $link['label'] }}</span>
                            <i class="bi {{ $link['icon'] }}"></i>
                        </a>
                    @endforeach
                </div>
            </div>
        @endforeach
    </nav>
</aside>

