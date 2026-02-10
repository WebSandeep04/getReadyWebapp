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
                            <th>User Name & Email</th>
                            <th>Phone</th>
                            <th>Address</th>
                            <th class="text-center">User Type</th>
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
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header border-bottom-0 pb-0">
                    <h5 class="modal-title fw-bold" id="editUserModalLabel">Edit Member Profile</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="editUserForm">
                    <div class="modal-body pt-4">
                        <input type="hidden" id="editUserId">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-uppercase text-muted">Name</label>
                                <input type="text" class="form-control" id="editName" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-uppercase text-muted">Email</label>
                                <input type="email" class="form-control" id="editEmail" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-uppercase text-muted">Phone</label>
                                <input type="text" class="form-control" id="editPhone" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-uppercase text-muted">User Type</label>
                                <select class="form-select" id="editGender" required>
                                    <option value="Boy">Boy</option>
                                    <option value="Girl">Girl</option>
                                    <option value="Men">Men</option>
                                    <option value="Women">Women</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-bold small text-uppercase text-muted">Address</label>
                                <input type="text" class="form-control" id="editAddress" placeholder="Street, City, State">
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-bold small text-uppercase text-muted">Business Type</label>
                                <select class="form-select" id="editIsGst">
                                    <option value="0">Individual / Non-Business</option>
                                    <option value="1">Business (GST Available)</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-bold small text-uppercase text-muted">GSTIN</label>
                                <input type="text" class="form-control" id="editGstin" placeholder="Enter 15-digit GSTIN" maxlength="15">
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
            const haystack = `${user.name ?? ''} ${user.email ?? ''} ${user.phone ?? ''}`.toLowerCase();
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
        const email = user.email || '<span class="text-muted text-opacity-50">No email</span>';
        const address = user.address || '<span class="text-muted text-opacity-50">Add address</span>';
        const phone = user.phone || '<span class="text-muted text-opacity-50">No phone</span>';
        const genderLabel = user.gender ? user.gender : '—';
        
        // Monochrome Badge
        let genderBadge = `<span class="badge badge-pill-soft">${genderLabel}</span>`;

        // Action Buttons
        const btns = `
            <div class="btn-group" role="group">
                <button class="btn btn-sm btn-outline-secondary edit-user" data-user='${JSON.stringify(user)}' title="Edit">
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
                <td>
                    <div class="fw-bold text-dark">${user.name ?? 'Guest'}</div>
                    <div class="small text-muted">${email}</div>
                </td>
                <td>${phone}</td>
                <td class="text-truncate" style="max-width: 150px;">${address}</td>
                <td class="text-center">${genderBadge}</td>
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

    $tableBody.on('click', '.edit-user', function() {
        const user = $(this).data('user');
        $('#editUserId').val(user.id);
        $('#editName').val(user.name);
        $('#editEmail').val(user.email);
        $('#editPhone').val(user.phone);
        $('#editAddress').val(user.address);
        $('#editGstin').val(user.gstin || '');
        $('#editIsGst').val(user.is_gst ? '1' : '0');
        $('#editGender').val(user.gender || 'Boy');
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
            email: $('#editEmail').val(),
            phone: $('#editPhone').val(),
            address: $('#editAddress').val(),
            gstin: $('#editGstin').val(),
            is_gst: $('#editIsGst').val(),
            gender: $('#editGender').val(),
            _token: csrf,
        })
        .done(() => {
            const modalEl = document.getElementById('editUserModal');
            const modal = bootstrap.Modal.getInstance(modalEl);
            if (modal) modal.hide();
            fetchUsers(false);
        })
        .fail(xhr => {
             alert('Unable to update user.');
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
        }).done(() => fetchUsers(false))
          .fail(() => alert('Unable to delete user.'));
    });

    $('#downloadUsers').on('click', function() {
        if (!state.data.length) {
            alert('No data to export.');
            return;
        }
        const headers = ['ID','Name','Email','Phone','Address','User Type'];
        const rows = state.data.map(u => [
            u.id,
            `"${(u.name || '').replace(/"/g, '""')}"`,
            `"${(u.email || '').replace(/"/g, '""')}"`,
            `"${(u.phone || '').replace(/"/g, '""')}"`,
            `"${(u.address || '').replace(/"/g, '""')}"`,
            u.gender || '',
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
