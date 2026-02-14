@php
    $adminSidebar = [

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
                ['label' => 'Security Deposits', 'route' => 'admin.security', 'icon' => 'bi-shield-lock'],
                ['label' => 'Payments', 'route' => 'admin.payments', 'icon' => 'bi-wallet2'],
            ],
        ],
        [
            'title' => 'Reports',
            'icon' => 'bi-bar-chart-line',
            'links' => [
                ['label' => 'Financial Report', 'route' => 'admin.reports.financial', 'icon' => 'bi-cash-coin'],
                ['label' => 'Alert Calendar', 'route' => 'admin.reports.calendar', 'icon' => 'bi-calendar3'],
            ],
        ],
    ];

    // Setup Links (Hidden from main array, shown separately)
    $setupLinks = [
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
        ['label' => 'States', 'route' => 'states.index', 'icon' => 'bi-map'],
        ['label' => 'Cities', 'route' => 'cities.index', 'icon' => 'bi-building'],
        ['label' => 'Tax (Management)', 'route' => 'admin.tax', 'icon' => 'bi-receipt-cutoff'],
        ['label' => 'Frontend Settings', 'route' => 'admin.frontend', 'icon' => 'bi-globe'],
    ];

    // Fetch authenticated admin
    $admin = \App\Models\AdminPanelUser::find(Session::get('admin_id'));

    // Check if we are in setup mode (either by query param or active route)
    $isSetupMode = request()->has('setup') || collect($setupLinks)->contains(function($link) {
        return request()->routeIs($link['route']) || request()->routeIs(Str::beforeLast($link['route'], '.index') . '.*');
    });

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
    <nav class="py-2 flex-grow-1" id="sidebarAccordion" style="overflow-y: auto;">
        
        @if($isSetupMode)
            <div class="px-3 mb-2 small text-uppercase text-muted fw-bold">Software Setup</div>
            <div class="mb-3">
                <a href="{{ route('admin.dashboard') }}" class="btn btn-sm btn-light w-100 text-start mb-3 border">
                    <i class="bi bi-arrow-left me-2"></i> Back to Main Menu
                </a>
                
                <div class="d-flex flex-column gap-1">
                    @foreach($setupLinks as $link)
                        @if($admin && $admin->hasPermission($link['route']))
                            <a href="{{ route($link['route']) }}" class="nav-link {{ request()->routeIs($link['route']) || request()->routeIs(Str::beforeLast($link['route'],'.index').'.*') ? 'active bg-light fw-bold text-dark' : 'text-muted' }} py-2 px-3 rounded">
                                <i class="bi {{ $link['icon'] }} me-2"></i> {{ $link['label'] }}
                            </a>
                        @endif
                    @endforeach
                </div>
            </div>
        @else
            <!-- Main Menu content -->
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
        @endif
    </nav>
    
    @if(!$isSetupMode)
        <div class="mt-auto p-3 border-top">
            <a href="{{ route('user.index') }}?setup=true" class="btn btn-outline-dark w-100 d-flex align-items-center justify-content-center gap-2">
                <i class="bi bi-gear-wide-connected"></i>
                <span>Software Setup</span>
            </a>
        </div>
    @endif
</aside>

<style>
/* Ensure sidebar takes full height and flexible layout */
.admin-sidebar {
    display: flex;
    flex-direction: column;
    height: 100vh;
    position: fixed; /* Or sticky depending on your layout requirements */
    top: 0;
    left: 0;
    width: 260px; /* Adjust as needed */
    z-index: 1000;
    background: #fff;
    border-right: 1px solid #eee;
}
</style>

