@extends('admin.layouts.app')

@section('title', 'Admin Dashboard')
@section('page_title', 'Manage Users')

@section('content')
<div class="container-fluid p-0">
    <!-- Stat Cards Row -->
    <div class="row g-3 mb-4">
        <div class="col-lg-3 col-sm-6">
            <div class="stat-card">
                <div class="stat-card__icon"><i class="bi bi-people"></i></div>
                <div class="stat-card__label">Total Users</div>
                <div class="stat-card__value" id="usersTotal">{{ number_format($totalUsers ?? 0) }}</div>
                <small>Registered members</small>
            </div>
        </div>
        <div class="col-lg-3 col-sm-6">
            <div class="stat-card">
                <div class="stat-card__icon"><i class="bi bi-person-plus"></i></div>
                <div class="stat-card__label">New (7 Days)</div>
                <div class="stat-card__value" id="usersLast7">--</div>
                <small>Fresh signups</small>
            </div>
        </div>
        <div class="col-lg-3 col-sm-6">
            <div class="stat-card">
                <div class="stat-card__icon"><i class="bi bi-patch-check"></i></div>
                <div class="stat-card__label">Verified</div>
                <div class="stat-card__value" id="usersVerified">--</div>
                <small>Email confirmed</small>
            </div>
        </div>
        <div class="col-lg-3 col-sm-6">
            <div class="stat-card">
                <div class="stat-card__icon"><i class="bi bi-phone"></i></div>
                <div class="stat-card__label">Phone Only</div>
                <div class="stat-card__value" id="usersPhoneOnly">--</div>
                <small>Needs follow-up</small>
            </div>
        </div>
    </div>

    <!-- Main Card -->
    <div class="card h-100">
        <div class="card-header bg-white text-dark border-bottom py-3">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold"><i class="bi bi-table me-2"></i>User Management</h5>
                <div class="d-flex gap-2">
                     <div class="input-group input-group-sm" style="width: 250px;">
                        <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-search"></i></span>
                        <input type="search" id="userSearch" class="form-control border-start-0 ps-0" placeholder="Search users...">
                    </div>
                    <button class="btn btn-sm btn-outline-dark" id="downloadUsers" title="Download CSV">
                        <i class="bi bi-cloud-arrow-down"></i>
                    </button>
                    <button class="btn btn-sm btn-outline-dark" id="refreshUsers" title="Refresh">
                        <i class="bi bi-arrow-clockwise"></i>
                    </button>
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle admin-table" id="usersTable">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">ID</th>
                            <th>Member Profile</th>
                            <th>Contact Details</th>
                            <th>Personal Info</th>
                            <th>Business Info</th>
                            <th>Activity & Status</th>
                            <th class="text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <div class="card-footer bg-white border-top border-light py-3 px-4 d-none" id="usersPagination"></div>
            
            <!-- Empty State -->
            <div class="text-center py-5 d-none" id="usersEmptyState">
                <div class="mb-3 text-muted opacity-25">
                    <i class="bi bi-search" style="font-size: 3rem;"></i>
                </div>
                <h6 class="fw-bold">No users match that filter</h6>
                <p class="text-muted small mb-3">Try clearing the search to view everyone again.</p>
                <button class="btn btn-sm btn-dark" id="clearUserFilters">Reset filters</button>
            </div>
        </div>
    </div>

    <!-- Edit User Modal -->
    <div class="modal fade" id="editUserModal" tabindex="-1" aria-labelledby="editUserModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header border-bottom-0 pb-0">
                    <h5 class="modal-title fw-bold" id="editUserModalLabel">Edit Member Profile</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="editUserForm">
                    <div class="modal-body pt-4">
                        <input type="hidden" id="editUserId">
                        <div class="row g-3">
                            <!-- Personal Details -->
                            <div class="col-12"><h6 class="text-uppercase small fw-bold text-muted border-bottom pb-2 mb-0">Personal Details</h6></div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-uppercase text-muted">Name</label>
                                <input type="text" class="form-control" id="editName" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-uppercase text-muted">Age</label>
                                <input type="number" class="form-control" id="editAge" min="1" max="120">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-uppercase text-muted">Gender</label>
                                <select class="form-select" id="editGender" required>
                                    <option value="Boy">Boy</option>
                                    <option value="Girl">Girl</option>
                                    <option value="Men">Men</option>
                                    <option value="Women">Women</option>
                                </select>
                            </div>

                            <!-- Contact Details -->
                            <div class="col-12 mt-4"><h6 class="text-uppercase small fw-bold text-muted border-bottom pb-2 mb-0">Contact Information</h6></div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-uppercase text-muted">Email</label>
                                <input type="email" class="form-control" id="editEmail" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-uppercase text-muted">Phone</label>
                                <input type="text" class="form-control" id="editPhone" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-uppercase text-muted">City</label>
                                <input type="text" class="form-control" id="editCity">
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-bold small text-uppercase text-muted">Address</label>
                                <input type="text" class="form-control" id="editAddress" placeholder="Full Street Address">
                            </div>

                            <!-- Business Details -->
                            <div class="col-12 mt-4"><h6 class="text-uppercase small fw-bold text-muted border-bottom pb-2 mb-0">Business Information</h6></div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold small text-uppercase text-muted">Business Type</label>
                                <select class="form-select" id="editIsGst">
                                    <option value="0">Individual</option>
                                    <option value="1">Business (GST)</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold small text-uppercase text-muted">GSTIN</label>
                                <input type="text" class="form-control" id="editGstin" placeholder="15-digit GSTIN" maxlength="15">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold small text-uppercase text-muted">GST Number (Ref)</label>
                                <input type="text" class="form-control" id="editGstNumber" placeholder="Reference No.">
                            </div>
                        </div>
                        <div id="editUserErrors" class="text-danger small mt-2"></div>
                    </div>
                    <div class="modal-footer border-top-0">
                        <button type="button" class="btn btn-sm btn-outline-secondary px-3" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-sm btn-dark px-4">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- View User Modal -->
    <div class="modal fade" id="viewUserModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow-lg overflow-hidden">
                <div class="modal-header border-bottom px-4 py-3 bg-white">
                    <h5 class="modal-title fw-bold">Member Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                
                <div class="modal-body p-0">
                    <!-- Identity Section -->
                    <div class="p-4 bg-light border-bottom">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0 position-relative">
                                <img id="viewAvatar" src="" class="rounded-3 shadow-sm border bg-white" width="96" height="96" style="object-fit:cover;">
                            </div>
                            <div class="flex-grow-1 ms-4">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <h4 class="fw-bold text-dark mb-1" id="viewName"></h4>
                                        <div class="d-flex align-items-center text-muted mb-2">
                                            <i class="bi bi-envelope me-2"></i>
                                            <span id="viewEmail"></span>
                                        </div>
                                        <div id="viewBadges"></div>
                                    </div>
                                    <div class="text-end">
                                        <div class="text-uppercase small fw-bold text-muted mb-1">User ID</div>
                                        <div class="fs-4 fw-bold font-monospace text-dark lh-1" id="viewId"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Details Grid -->
                    <div class="p-4">
                        <div class="row g-4">
                            <!-- Personal -->
                            <div class="col-md-6">
                                <div class="p-3 bg-white border rounded h-100">
                                    <h6 class="text-uppercase small fw-bold text-muted border-bottom pb-2 mb-3">
                                        <i class="bi bi-person me-2"></i>Personal Info
                                    </h6>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span class="text-muted small">Age</span>
                                        <span class="fw-semibold text-dark" id="viewAge">--</span>
                                    </div>
                                    <div class="d-flex justify-content-between mb-0">
                                        <span class="text-muted small">Gender</span>
                                        <span class="fw-semibold text-dark" id="viewGender">--</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Contact -->
                            <div class="col-md-6">
                                <div class="p-3 bg-white border rounded h-100">
                                    <h6 class="text-uppercase small fw-bold text-muted border-bottom pb-2 mb-3">
                                        <i class="bi bi-telephone me-2"></i>Contact Details
                                    </h6>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span class="text-muted small">Phone</span>
                                        <span class="fw-semibold text-dark" id="viewPhone">--</span>
                                    </div>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span class="text-muted small">City</span>
                                        <span class="fw-semibold text-dark" id="viewCity">--</span>
                                    </div>
                                    <div class="mb-0">
                                        <span class="text-muted small d-block mb-1">Address</span>
                                        <span class="fw-semibold text-dark small text-break lh-sm" id="viewAddress">--</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Business -->
                            <div class="col-md-6">
                                <div class="p-3 bg-white border rounded h-100">
                                    <h6 class="text-uppercase small fw-bold text-muted border-bottom pb-2 mb-3">
                                        <i class="bi bi-briefcase me-2"></i>Business Info
                                    </h6>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span class="text-muted small">Type</span>
                                        <span class="fw-semibold text-dark" id="viewBusinessType">--</span>
                                    </div>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span class="text-muted small">GSTIN</span>
                                        <span class="font-monospace small text-dark" id="viewGstin">--</span>
                                    </div>
                                    <div class="d-flex justify-content-between mb-0">
                                        <span class="text-muted small">Ref. No</span>
                                        <span class="font-monospace small text-dark" id="viewGstNumber">--</span>
                                    </div>
                                </div>
                            </div>

                            <!-- System -->
                            <div class="col-md-6">
                                <div class="p-3 bg-white border rounded h-100">
                                    <h6 class="text-uppercase small fw-bold text-muted border-bottom pb-2 mb-3">
                                        <i class="bi bi-cpu me-2"></i>System Activity
                                    </h6>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span class="text-muted small">Joined</span>
                                        <span class="fw-semibold text-dark" id="viewJoined">--</span>
                                    </div>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span class="text-muted small">Last Login</span>
                                        <span class="fw-semibold text-dark" id="viewLastLogin">--</span>
                                    </div>
                                    <div class="d-flex justify-content-between mb-0">
                                        <span class="text-muted small">Status</span>
                                        <span class="fw-semibold" id="viewVerification">--</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light border-top-0 py-3">
                    <button type="button" class="btn btn-dark btn-sm px-4 rounded-1" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
/* Global Monochrome Overrides for this page */
*, ::before, ::after { border-radius: 0 !important; }

/* Stat Cards */
.stat-card {
    position: relative;
    padding: 1.25rem;
    color: #000;
    background: #fff;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
    height: 100%;
    display: flex;
    flex-direction: column;
    justify-content: center;
    min-height: 110px;
    transition: all 0.3s ease;
    border: 1px solid #f3f4f6;
}
.stat-card:hover { 
    transform: translateY(-2px); 
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
}
.stat-card__label {
    font-size: .7rem;
    font-weight: 700;
    color: #4b5563;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    margin-bottom: 0.25rem;
}
.stat-card__value {
    font-size: 1.8rem;
    font-weight: 800;
    margin: 0;
    color: #111827;
    line-height: 1.2;
}
.stat-card__icon {
    position: absolute;
    top: 1rem;
    right: 1rem;
    font-size: 1.5rem;
    color: #000;
    opacity: 1;
}
.stat-card small { color: #9ca3af; font-weight: 500; font-size: 0.75rem; }

/* Table Styling */
.card { border: none; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); background: #fff; }
.admin-table th {
    background: #f9fafb;
    color: #374151;
    border-bottom: 1px solid #e5e7eb;
    font-weight: 600;
    font-size: .7rem;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    padding-top: 0.75rem;
    padding-bottom: 0.75rem;
    white-space: nowrap;
}
.admin-table td { 
    vertical-align: middle; 
    font-size: .8rem; 
    padding: 0.75rem 0.5rem !important; 
    border-bottom: 1px solid #f3f4f6; 
    color: #111827;
}
.table-hover tbody tr:hover { background-color: #f9fafb; }

/* Form Controls */
.form-control, .form-select {
    border: 1px solid #d1d5db;
    font-size: 0.85rem;
    box-shadow: none !important;
    padding: 0.5rem 0.75rem;
}
.form-control:focus, .form-select:focus {
    border-color: #000;
    background-color: #fff;
}
.input-group-text { border: 1px solid #d1d5db; }

/* Badges */
.badge-pill-soft {
    font-size: .7rem;
    padding: .2rem .6rem;
    font-weight: 600;
    border: 1px solid #e5e7eb; 
    background: #fff;
    color: #374151;
}

/* Buttons */
.btn { font-size: 0.8rem; font-weight: 500; padding: 0.4rem 0.75rem; transition: all 0.2s; }
.btn-dark { background: #111827; border: 1px solid #111827; color: #fff; }
.btn-dark:hover { background: #000; border-color: #000; transform: translateY(-1px); }
.btn-outline-dark { border: 1px solid #d1d5db; color: #374151; background: #fff; }
.btn-outline-dark:hover { background: #f9fafb; color: #000; border-color: #9ca3af; }
.btn-outline-secondary { border: 1px solid #e5e7eb; color: #6b7280; background: #fff; }
.btn-outline-secondary:hover { background: #f3f4f6; color: #111827; border-color: #d1d5db; }
.btn-outline-danger { border: 1px solid #fca5a5; color: #ef4444; background: #fff; }
.btn-outline-danger:hover { background: #fef2f2; color: #dc2626; border-color: #f87171; }

.text-muted { color: #6b7280 !important; }
</style>
@endpush

@push('scripts')
<script>
$(function() {
    const state = {
        data: [],
        search: '',
        page: 1,
        perPage: 20,
    };

    const csrf = $('meta[name="csrf-token"]').attr('content');
    const endpoints = {
        fetch: "{{ route('user.fetch') }}",
        update: "{{ url('/admin/user/update') }}",
        delete: "{{ url('/admin/user/delete') }}",
    };

    const $tableBody = $('#usersTable tbody');
    const $emptyState = $('#usersEmptyState');
    const $search = $('#userSearch');
    const $refresh = $('#refreshUsers');

    function fetchUsers(showSpinner = true) {
        if (showSpinner) {
            $refresh.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span>');
        }
        $.getJSON(endpoints.fetch)
            .done(users => {
                state.data = users || [];
                state.page = 1;
                renderUsers();
                updateStats();
            })
            .fail(() => {
                // Silent fail or simple alert
                console.error('Failed to load users');
            })
            .always(() => {
                $refresh.prop('disabled', false).html('<i class="bi bi-arrow-clockwise"></i>');
            });
    }

    function updateStats() {
        const total = state.data.length;
        $('#usersTotal').text(new Intl.NumberFormat().format(total));

        const sevenDaysAgo = new Date();
        sevenDaysAgo.setDate(sevenDaysAgo.getDate() - 7);

        let last7 = 0;
        let verified = 0;
        let phoneOnly = 0;

        state.data.forEach(user => {
            if (user.created_at && new Date(user.created_at) >= sevenDaysAgo) {
                last7++;
            }
            if (user.email) {
                verified++;
            } else {
                phoneOnly++;
            }
        });

        $('#usersLast7').text(last7);
        $('#usersVerified').text(verified);
        $('#usersPhoneOnly').text(phoneOnly);
    }

    function renderUsers() {
        const term = state.search.toLowerCase();
        let filtered = state.data.filter(user => {
            const haystack = `${user.name ?? ''} ${user.email ?? ''} ${user.phone ?? ''} ${user.gstin ?? ''}`.toLowerCase();
            return haystack.includes(term);
        });

        if (!filtered.length) {
            $('#usersTable').addClass('d-none');
            $('#usersPagination').addClass('d-none');
            $emptyState.removeClass('d-none');
            return;
        }

        $('#usersTable').removeClass('d-none');
        $emptyState.addClass('d-none');

        const totalPages = Math.max(1, Math.ceil(filtered.length / state.perPage));
        if (state.page > totalPages) state.page = totalPages;
        const start = (state.page - 1) * state.perPage;
        const pageItems = filtered.slice(start, start + state.perPage);

        const rows = pageItems.map(renderRow).join('');
        $tableBody.html(rows);
        renderPagination(filtered.length, totalPages);
    }

    function renderPagination(totalEntries, totalPages) {
        const $pager = $('#usersPagination');

        if (totalEntries === 0) {
            $pager.addClass('d-none').empty();
            return;
        }

        const startEntry = ((state.page - 1) * state.perPage) + 1;
        const endEntry = Math.min(state.page * state.perPage, totalEntries);

        $pager.removeClass('d-none').html(`
            <div class="d-flex align-items-center justify-content-between">
                <span class="text-muted small fw-bold">
                    Showing ${startEntry}-${endEntry} of ${totalEntries}
                </span>
                <div class="btn-group shadow-sm">
                    <button class="btn btn-sm btn-outline-dark border-0 bg-white users-prev" ${state.page === 1 ? 'disabled' : ''} style="width: 32px;">
                        <i class="bi bi-chevron-left"></i>
                    </button>
                    <span class="btn btn-sm btn-outline-dark border-0 bg-white disabled px-3 fw-bold">
                        ${state.page} / ${totalPages}
                    </span>
                    <button class="btn btn-sm btn-outline-dark border-0 bg-white users-next" ${state.page === totalPages ? 'disabled' : ''} style="width: 32px;">
                        <i class="bi bi-chevron-right"></i>
                    </button>
                </div>
            </div>
        `);

        $pager.off('click').on('click', '.users-prev', function() {
            if (state.page > 1) {
                state.page--;
                renderUsers();
            }
        }).on('click', '.users-next', function() {
            if (state.page < totalPages) {
                state.page++;
                renderUsers();
            }
        });
    }

    function renderRow(user) {
        // --- Helpers ---
        const avatarUrl = user.profile_image 
            ? `/storage/${user.profile_image}`
            : `https://ui-avatars.com/api/?name=${encodeURIComponent(user.name || 'User')}&background=random&color=fff&size=40`;
        
        const email = user.email || '<span class="text-muted small">No email</span>';
        const phone = user.phone || '<span class="text-muted small">No phone</span>';
        const address = user.address || '<span class="text-muted small">--</span>';
        const city = user.city ? `<span class="fw-semibold text-dark">${user.city}</span>` : '<span class="text-muted small">--</span>';
        
        const joinedDate = user.created_at ? new Date(user.created_at).toLocaleDateString('en-GB') : '--';
        const lastLogin = user.last_login_at ? new Date(user.last_login_at).toLocaleDateString('en-GB') : '<span class="text-muted small">Never</span>';
        
        // --- Info Blocks ---
        // 1. Member Profile
        const profileHtml = `
            <div class="d-flex align-items-center">
                <img src="${avatarUrl}" class="rounded-circle me-3 border" width="36" height="36" style="object-fit:cover;">
                <div>
                    <div class="fw-bold text-dark small">${user.name ?? 'Guest'}</div>
                    <div class="small text-muted" style="font-size:0.7rem;">${email}</div>
                </div>
            </div>
        `;

        // 2. Contact Details
        const contactHtml = `
            <div class="d-flex flex-column small">
                <div class="text-dark"><i class="bi bi-telephone me-1 text-muted"></i>${phone}</div>
                <div class="text-muted text-truncate" style="max-width:140px;" title="${user.address ?? ''}">
                    <i class="bi bi-geo-alt me-1"></i>${city}, ${address}
                </div>
            </div>
        `;

        // 3. Personal Info
        const personalHtml = `
            <div class="small">
                <div class="mb-1"><span class="text-muted">Age:</span> <span class="fw-semibold">${user.age || '--'}</span></div>
                <div><span class="text-muted">Sex:</span> <span class="badge badge-pill-soft">${user.gender || '—'}</span></div>
            </div>
        `;

        // 4. Business Info
        let businessHtml;
        if (user.is_gst == 1) {
            businessHtml = `
                <div class="small">
                    <span class="badge bg-dark text-white mb-1" style="font-size:0.6rem;">GST Business</span>
                    <div title="GSTIN" class="font-monospace text-muted" style="font-size:0.7rem;">${user.gstin || '--'}</div>
                    ${user.gst_number ? `<div title="GST Num" class="font-monospace text-muted" style="font-size:0.7rem;">#${user.gst_number}</div>` : ''}
                </div>
            `;
        } else {
             businessHtml = `<span class="badge badge-pill-soft text-muted">Individual</span>`;
        }

        // 5. Activity & Status
        const isVerified = !!user.email_verified_at;
        const statusHtml = `
            <div class="small">
                <div class="mb-1"><span class="text-muted">Joined:</span> ${joinedDate}</div>
                <div class="mb-1"><span class="text-muted">Active:</span> ${lastLogin}</div>
                ${isVerified 
                    ? '<span class="text-success fw-bold" style="font-size:0.7rem;"><i class="bi bi-patch-check-fill me-1"></i>Verified</span>' 
                    : '<span class="text-muted" style="font-size:0.7rem;">Unverified</span>'}
            </div>
        `;

        // Action Buttons
        const btns = `
            <div class="btn-group" role="group">
                <button class="btn btn-sm btn-outline-dark view-user" data-user='${JSON.stringify(user).replace(/'/g, "&#39;")}' title="View Details">
                    <i class="bi bi-eye"></i>
                </button>
                <button class="btn btn-sm btn-outline-secondary edit-user" data-user='${JSON.stringify(user).replace(/'/g, "&#39;")}' title="Edit">
                    <i class="bi bi-pencil"></i>
                </button>
                <button class="btn btn-sm btn-outline-danger delete-user" data-id="${user.id}" title="Delete">
                    <i class="bi bi-trash"></i>
                </button>
            </div>
        `;

        return `
            <tr>
                <td class="fw-bold text-muted small ps-4">#${user.id}</td>
                <td>${profileHtml}</td>
                <td>${contactHtml}</td>
                <td>${personalHtml}</td>
                <td>${businessHtml}</td>
                <td>${statusHtml}</td>
                <td class="text-end pe-4">${btns}</td>
            </tr>
        `;
    }

    $search.on('input', function() {
        state.search = $(this).val();
        state.page = 1;
        renderUsers();
    });

    $('#clearUserFilters').on('click', function() {
        state.search = '';
        state.page = 1;
        $search.val('');
        renderUsers();
    });

    $refresh.on('click', function() {
        fetchUsers(false);
    });

    $tableBody.on('click', '.view-user', function() {
        const user = $(this).data('user');
        
        // Header
        const avatarUrl = user.profile_image 
            ? `/storage/${user.profile_image}`
            : `https://ui-avatars.com/api/?name=${encodeURIComponent(user.name || 'User')}&background=random&color=fff&size=80`;
        $('#viewAvatar').attr('src', avatarUrl);
        $('#viewName').text(user.name || 'Guest');
        $('#viewEmail').text(user.email || 'No Email');
        
        const isVerified = !!user.email_verified_at;
        $('#viewBadges').html(
            isVerified 
                ? '<span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill">Verified Member</span>'
                : '<span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle rounded-pill">Unverified</span>'
        );

        // Personal
        $('#viewId').text('#' + user.id);
        $('#viewAge').text(user.age || 'Not set');
        $('#viewGender').text(user.gender || 'Not set');

        // Contact
        $('#viewPhone').text(user.phone || 'Not provided');
        $('#viewCity').text(user.city || 'Not provided');
        $('#viewAddress').text(user.address || 'No address on file');

        // Business
        $('#viewBusinessType').html(user.is_gst ? '<span class="badge bg-dark">Business</span>' : 'Individual');
        $('#viewGstin').text(user.gstin || 'N/A');
        $('#viewGstNumber').text(user.gst_number || 'N/A');

        // System
        $('#viewJoined').text(user.created_at ? new Date(user.created_at).toLocaleDateString('en-GB') : '-');
        $('#viewLastLogin').html(user.last_login_at 
            ? new Date(user.last_login_at).toLocaleString('en-GB') 
            : '<span class="text-muted fst-italic">Never</span>'
        );
        $('#viewVerification').html(isVerified
            ? `<span class="text-success"><i class="bi bi-check-circle-fill me-1"></i>${new Date(user.email_verified_at).toLocaleDateString('en-GB')}</span>`
            : '<span class="text-warning"><i class="bi bi-exclamation-circle me-1"></i>Pending</span>'
        );

        const modal = new bootstrap.Modal(document.getElementById('viewUserModal'));
        modal.show();
    });

    $tableBody.on('click', '.edit-user', function() {
        const user = $(this).data('user');
        
        $('#editUserId').val(user.id);
        
        // Personal
        $('#editName').val(user.name);
        $('#editAge').val(user.age || '');
        $('#editGender').val(user.gender || 'Boy');
        
        // Contact
        $('#editEmail').val(user.email);
        $('#editPhone').val(user.phone);
        $('#editCity').val(user.city || '');
        $('#editAddress').val(user.address);
        
        // Business
        $('#editIsGst').val(user.is_gst ? '1' : '0');
        $('#editGstin').val(user.gstin || '');
        $('#editGstNumber').val(user.gst_number || '');
        
        $('#editUserErrors').html('');
        const modal = new bootstrap.Modal(document.getElementById('editUserModal'));
        modal.show();
    });

    $('#editUserForm').on('submit', function(e) {
        e.preventDefault();
        const id = $('#editUserId').val();
        const $submit = $(this).find('button[type="submit"]');
        const originalText = $submit.text();
        $submit.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Saving');

        $.post(`${endpoints.update}/${id}`, {
            name: $('#editName').val(),
            age: $('#editAge').val(),
            gender: $('#editGender').val(),
            email: $('#editEmail').val(),
            phone: $('#editPhone').val(),
            city: $('#editCity').val(),
            address: $('#editAddress').val(),
            is_gst: $('#editIsGst').val(),
            gstin: $('#editGstin').val(),
            gst_number: $('#editGstNumber').val(),
            _token: csrf,
        })
        .done(() => {
            const modalEl = document.getElementById('editUserModal');
            const modal = bootstrap.Modal.getInstance(modalEl);
            if (modal) modal.hide();
            fetchUsers(false);
            if (window.showAlert) window.showAlert('User updated successfully', 'success');
        })
        .fail(xhr => {
             const msg = xhr.responseJSON?.message || 'Unable to update user.';
             if (window.showAlert) window.showAlert(msg, 'danger');
             $('#editUserErrors').text(msg);
        })
        .always(() => {
            $submit.prop('disabled', false).text(originalText);
        });
    });

    $tableBody.on('click', '.delete-user', function() {
        const id = $(this).data('id');
        if (!confirm('Delete this user?')) return;
        
        $.ajax({
            url: `${endpoints.delete}/${id}`,
            type: 'DELETE',
            data: { _token: csrf },
        }).done(() => {
            fetchUsers(false);
            if (window.showAlert) window.showAlert('User deleted successfully', 'success');
        })
          .fail(() => {
              if (window.showAlert) window.showAlert('Unable to delete user', 'danger');
          });
    });

    $('#downloadUsers').on('click', function() {
        if (!state.data.length) {
            showAlert('No data to export.', 'warning');
            return;
        }
        const headers = ['ID','Name','Email','Phone','City','Address','Age','Gender','Business Type','GSTIN','Joined'];
        const rows = state.data.map(u => [
            u.id,
            `"${(u.name || '').replace(/"/g, '""')}"`,
            `"${(u.email || '').replace(/"/g, '""')}"`,
            `"${(u.phone || '').replace(/"/g, '""')}"`,
            `"${(u.city || '').replace(/"/g, '""')}"`,
            `"${(u.address || '').replace(/"/g, '""')}"`,
            u.age || '',
            u.gender || '',
            u.is_gst ? 'Business' : 'Individual',
            `"${(u.gstin || '').replace(/"/g, '""')}"`,
            u.created_at ? new Date(u.created_at).toISOString().slice(0,10) : ''
        ]);
        const csv = [headers.join(','), ...rows.map(row => row.join(','))].join('\n');
        const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
        const url = URL.createObjectURL(blob);
        const link = document.createElement('a');
        link.href = url;
        link.download = `users-${new Date().toISOString().slice(0,10)}.csv`;
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        URL.revokeObjectURL(url);
    });

    fetchUsers();
});
</script>

@endpush
