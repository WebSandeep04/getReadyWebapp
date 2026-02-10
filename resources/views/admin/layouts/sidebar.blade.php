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
                ['label' => 'Role Master', 'route' => 'role_master.index', 'icon' => 'bi-shield-shaded'],
                ['label' => 'Admin Users', 'route' => 'admin_panel_users.index', 'icon' => 'bi-person-badge'],
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

    // Fetch authenticated admin
    $admin = \App\Models\AdminPanelUser::find(Session::get('admin_id'));

    // Filter sidebar links based on permissions
    foreach ($adminSidebar as $sKey => $section) {
        $filteredLinks = [];
        foreach ($section['links'] as $link) {
            // Check if admin has permission for this route
            // The permission name in DB matches the route name
            if ($admin && $admin->hasPermission($link['route'])) {
                $filteredLinks[] = $link;
            }
        }
        
        if (empty($filteredLinks)) {
            unset($adminSidebar[$sKey]);
        } else {
            $adminSidebar[$sKey]['links'] = $filteredLinks;
        }
    }

    // Helper to determine if a route or its children are active
    $isActive = function($route) {
        if (request()->routeIs($route)) {
            return true;
        }
        // Handle resource.* wildcard (e.g. user.index -> user.* matches user.create, user.edit)
        if (\Illuminate\Support\Str::endsWith($route, '.index')) {
            $resource = \Illuminate\Support\Str::beforeLast($route, '.index');
            if (request()->routeIs($resource . '.*')) {
                return true;
            }
        }
        // Handle prefix matches (e.g. admin.orders -> admin.orders.*)
        if (request()->routeIs($route . '.*')) {
            return true;
        }
        return false;
    };
@endphp

<aside class="admin-sidebar">
    <!-- Brand -->
    <div class="admin-sidebar__brand">
        <span class="fw-bold fs-4 text-dark">
            GetReady
        </span>
    </div>

    <!-- Navigation -->
    <nav class="py-2" id="sidebarAccordion">
        <div class="admin-sidebar__menu-label">Main Menu</div>
        
        @if($admin && $admin->hasPermission('admin.dashboard'))
        <a href="{{ route('admin.dashboard') }}" class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
            <i class="bi bi-grid-fill"></i>
            <span>Dashboard</span>
        </a>
        @endif

        @foreach($adminSidebar as $index => $section)
            @php
                // Check if any child link is currently active to open the parent section
                $isActiveSection = false;
                foreach($section['links'] as $link) {
                    if ($isActive($link['route'])) {
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
                
                <div class="collapse {{ $isActiveSection ? 'show' : '' }}" id="menu-{{ $index }}" data-bs-parent="#sidebarAccordion">
                    <div class="submenu-list">
                        @foreach($section['links'] as $link)
                            <a href="{{ route($link['route']) }}" class="submenu-link {{ $isActive($link['route']) ? 'active' : '' }}">
                                {{ $link['label'] }}
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>
        @endforeach
    </nav>
</aside>

