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
                ['label' => 'Outfit Conditions', 'route' => 'garment_conditions.index', 'icon' => 'bi-shield-check'],
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
<aside class="admin-sidebar">
    <!-- Brand -->
    <div class="admin-sidebar__brand">
        <span class="fw-bold fs-4 text-dark">
            GetReady
        </span>
    </div>

    <!-- Navigation -->
    <nav class="py-2">
        <div class="admin-sidebar__menu-label">Main Menu</div>
        
        <a href="{{ route('admin.dashboard') }}" class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
            <i class="bi bi-grid-fill"></i>
            <span>Dashboard</span>
        </a>

        @foreach($adminSidebar as $index => $section)
            @php
                // Check if any child link is currently active
                $isActiveSection = false;
                foreach($section['links'] as $link) {
                    if (request()->routeIs($link['route']) || request()->is($link['route'])) {
                        $isActiveSection = true;
                        break;
                    }
                }
            @endphp

            <!-- Section Header (Collapsible) -->
            <div class="mt-1">
                <div class="nav-link submenu-toggle {{ $isActiveSection ? 'active' : '' }}" 
                     data-bs-toggle="collapse" 
                     data-bs-target="#menu-{{ $index }}" 
                     aria-expanded="{{ $isActiveSection ? 'true' : 'false' }}">
                    <div class="d-flex align-items-center">
                        <i class="bi {{ $section['icon'] }}"></i>
                        <span>{{ $section['title'] }}</span>
                    </div>
                    <i class="bi bi-chevron-down rotate-icon small"></i>
                </div>
                
                <div class="collapse {{ $isActiveSection ? 'show' : '' }}" id="menu-{{ $index }}">
                    <div class="submenu-list">
                        @foreach($section['links'] as $link)
                            <a href="{{ route($link['route']) }}" class="submenu-link {{ request()->routeIs($link['route']) ? 'active' : '' }}">
                                {{ $link['label'] }}
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>
        @endforeach
    </nav>
</aside>

