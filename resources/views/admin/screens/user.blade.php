@extends('admin.layouts.app')

@section('title', 'Admin Dashboard')
@section('page_title', 'Manage Users')

@include('admin.components.setup-crud-styles')

@section('content')
<div class="container-fluid py-4 category-dashboard" id="usersHub">
    <div class="row g-4">
        <div class="col-12">
            <div class="glass-card hero-card d-flex flex-column flex-lg-row align-items-start align-items-lg-center justify-content-between p-4 p-lg-5">
                <div>
                    <p class="text-uppercase fw-semibold text-white-50 small mb-1">Community · Members</p>
                    <h2 class="display-6 fw-bold text-white mb-3">Know every renter & stylist by heart</h2>
                    <p class="text-white-50 mb-4 mb-lg-0">
                        Search, filter, and action on member profiles with a modern command center. 
                        Keep data fresh before every drop.
                    </p>
                </div>
                <div class="d-flex flex-column flex-sm-row gap-3">
                    <button class="btn btn-light btn-lg shadow-sm" id="downloadUsers">
                        <i class="bi bi-cloud-arrow-down me-2"></i>Download CSV
                    </button>
                    <button class="btn btn-outline-light btn-lg shadow-sm" id="refreshUsers">
                        <i class="bi bi-arrow-repeat me-2"></i>Refresh
                    </button>
                </div>
            </div>
        </div>

        <div class="col-12 col-xl-4">
            <div class="glass-card p-4 h-100">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <p class="text-uppercase text-muted small mb-0">Community pulse</p>
                    <span class="badge rounded-pill bg-gradient text-white">Live</span>
                </div>
                <h1 class="display-4 fw-bold text-primary mb-3" id="usersTotal">{{ number_format($totalUsers ?? 0) }}</h1>
                <div class="stat-stack">
                    <div class="stat-row">
                        <div>
                            <p class="text-muted mb-0 small">Joined in last 7 days</p>
                            <h5 class="fw-semibold mb-0" id="usersLast7">--</h5>
                        </div>
                        <span class="badge badge-pill-soft bg-success-subtle text-success" id="usersLast7Badge">Fresh</span>
                    </div>
                    <div class="stat-row">
                        <div>
                            <p class="text-muted mb-0 small">Verified email</p>
                            <h5 class="fw-semibold mb-0" id="usersVerified">--</h5>
                        </div>
                        <span class="badge badge-pill-soft bg-info-subtle text-info" id="usersVerifiedBadge">Trusted</span>
                    </div>
                    <div class="stat-row">
                        <div>
                            <p class="text-muted mb-0 small">Phone only</p>
                            <h5 class="fw-semibold mb-0" id="usersPhoneOnly">--</h5>
                        </div>
                        <span class="badge badge-pill-soft bg-warning-subtle text-warning">Needs follow-up</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-xl-8">
            <div class="glass-card p-4 h-100 d-flex flex-column">
                <div class="input-group shadow-sm mb-4">
                    <span class="input-group-text bg-white border-0 text-muted"><i class="bi bi-search"></i></span>
                    <input type="search" id="userSearch" class="form-control border-0" placeholder="Search by name, email, or phone...">
                </div>
                <div class="table-responsive flex-grow-1">
                    <table class="table table-hover align-middle mb-0 modern-table" id="usersTable">
                        <thead>
                            <tr>
                                <th class="text-uppercase small fw-semibold text-muted">ID</th>
                                <th class="text-uppercase small fw-semibold text-muted">Profile</th>
                                <th class="text-uppercase small fw-semibold text-muted">Phone</th>
                                <th class="text-uppercase small fw-semibold text-muted">Address</th>
                                <th class="text-uppercase small fw-semibold text-muted text-center">User Type</th>
                                <th class="text-uppercase small fw-semibold text-muted text-end">Actions</th>
                    </tr>
                </thead>
                        <tbody></tbody>
            </table>
                    <div class="d-flex justify-content-between align-items-center mt-3 d-none" id="usersPagination"></div>
                </div>
                <div class="text-center mt-4 d-none" id="usersEmptyState">
                    <img src="https://cdn.jsdelivr.net/gh/ux-illustrations/undraw/void.svg" alt="Empty state" class="empty-illustration mb-3">
                    <h5 class="fw-semibold">No users match that filter</h5>
                    <p class="text-muted mb-3">Try switching filters or clearing the search to view everyone again.</p>
                    <button class="btn btn-gradient" id="clearUserFilters">Reset filters</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit User Modal -->
    <div class="modal fade modal-modern" id="editUserModal" tabindex="-1" aria-labelledby="editUserModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header">
                    <h5 class="modal-title" id="editUserModalLabel">Edit Member Profile</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <form id="editUserForm">
            <div class="modal-body">
                <input type="hidden" id="editUserId">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Name</label>
                                <input type="text" class="form-control" id="editName" required>
                </div>
                            <div class="col-md-6">
                                <label class="form-label">Email</label>
                                <input type="email" class="form-control" id="editEmail" required>
                </div>
                            <div class="col-md-6">
                                <label class="form-label">Phone</label>
                                <input type="text" class="form-control" id="editPhone" required>
                </div>
                            <div class="col-md-6">
                                <label class="form-label">User Type</label>
                                <select class="form-select" id="editGender" required>
                        <option value="Boy">Boy</option>
                        <option value="Girl">Girl</option>
                        <option value="Men">Men</option>
                        <option value="Women">Women</option>
                    </select>
                </div>
                            <div class="col-12">
                                <label class="form-label">Address</label>
                                <input type="text" class="form-control" id="editAddress" placeholder="Street, City, State">
                            </div>
                            <div class="col-12">
                                <label class="form-label">Business Type</label>
                                <select class="form-select" id="editIsGst">
                                    <option value="0">Individual / Non-Business</option>
                                    <option value="1">Business (GST Available)</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label">GSTIN</label>
                                <input type="text" class="form-control" id="editGstin" placeholder="Enter 15-digit GSTIN (e.g., 27AAAAA0000A1Z5)" maxlength="15">
                                <small class="text-muted">Format: 15 characters (e.g., 27AAAAA0000A1Z5)</small>
                            </div>
                        </div>
                        <div id="editUserErrors" class="text-danger small mt-2"></div>
            </div>
            <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-gradient text-uppercase fw-semibold px-4">Save changes</button>
            </div>
          </form>
        </div>
      </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    #usersTable .avatar-circle {
        width: 42px;
        height: 42px;
        border-radius: 50%;
        background: rgba(47, 87, 239, 0.15);
        color: #2f57ef;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
    }
    .stat-stack .stat-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0.85rem 0;
        border-bottom: 1px solid rgba(15, 23, 42, 0.08);
    }
    .stat-stack .stat-row:last-child {
        border-bottom: none;
    }
</style>
@endpush

@push('scripts')
<script>
$(function() {
    const state = {
        data: [],
        search: '',
        page: 1,
        perPage: 5,
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
            $refresh.addClass('disabled').html('<span class="spinner-border spinner-border-sm me-2"></span>Refreshing');
        }
        $.getJSON(endpoints.fetch)
            .done(users => {
                state.data = users || [];
                state.page = 1;
                renderUsers();
                updateStats();
            })
            .fail(() => alert('Unable to load users right now. Please try again.'))
            .always(() => {
                $refresh.removeClass('disabled').html('<i class="bi bi-arrow-repeat me-2"></i>Refresh');
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

    function passesFilter(user) {
        switch (state.filter) {
            case 'verified':
                return Boolean(user.email);
            case 'phone':
                return !user.email;
            case 'Girl':
                return user.gender === 'Girl';
            case 'Boy':
                return user.gender === 'Boy';
            case 'Women':
                return user.gender === 'Women';
            case 'Men':
                return user.gender === 'Men';
            default:
                return true;
        }
    }

    function renderUsers() {
        const term = state.search.toLowerCase();
        let filtered = state.data.filter(user => {
            const haystack = `${user.name ?? ''} ${user.email ?? ''} ${user.phone ?? ''}`.toLowerCase();
            return haystack.includes(term);
        });

        if (!filtered.length) {
            $('#usersTable').addClass('d-none');
            $emptyState.removeClass('d-none');
            $('#usersPagination').addClass('d-none').empty();
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

    function renderPagination(totalItems, totalPages) {
        const $pager = $('#usersPagination');

        if (totalItems <= state.perPage) {
            $pager.addClass('d-none').empty();
            return;
        }

        $pager.removeClass('d-none').html(`
            <button class="btn btn-sm btn-outline-secondary users-prev" ${state.page === 1 ? 'disabled' : ''}>
                <i class="bi bi-chevron-left"></i>
            </button>
            <span class="text-muted small">Page ${state.page} of ${totalPages}</span>
            <button class="btn btn-sm btn-outline-secondary users-next" ${state.page === totalPages ? 'disabled' : ''}>
                <i class="bi bi-chevron-right"></i>
            </button>
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
        const initials = (user.name || 'Guest')
            .split(' ').slice(0, 2).map(chunk => chunk.charAt(0).toUpperCase()).join('') || 'GR';
        const email = user.email || '<span class="text-muted">No email</span>';
        const address = user.address || '<span class="text-muted">Add address</span>';
        const phone = user.phone || '<span class="text-muted">No phone</span>';
        const genderBadgeClass = user.gender === 'Girl' || user.gender === 'Women'
            ? 'bg-pink-50 text-danger'
            : user.gender === 'Boy' || user.gender === 'Men'
                ? 'bg-primary-subtle text-primary'
                : 'bg-secondary-subtle text-secondary';
        const genderLabel = user.gender ? user.gender.charAt(0).toUpperCase() + user.gender.slice(1) : '—';

        return `
            <tr>
                <td class="fw-semibold text-muted">#${user.id}</td>
                <td>
                    <div class="d-flex align-items-center gap-3">
                        <div class="avatar-circle">${initials}</div>
                        <div>
                            <div class="fw-semibold text-dark">${user.name ?? 'Guest'}</div>
                            <small class="text-muted">${email}</small>
                        </div>
                    </div>
                </td>
                <td>${phone}</td>
                <td>${address}</td>
                <td class="text-center">
                    <span class="badge badge-pill-soft ${genderBadgeClass}">${genderLabel}</span>
                </td>
                <td class="text-end">
                    <div class="btn-group" role="group">
                        <button 
                            class="btn btn-sm btn-outline-secondary edit-user btn-icon" 
                            data-user='${JSON.stringify(user)}' title="Edit">
                            <i class="bi bi-pencil-square"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-danger delete-user btn-icon" data-id="${user.id}" title="Delete">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </td>
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

    $('#usersEmptyState').on('click', '#clearUserFilters', function() {
        $('#clearUserFilters').trigger('click');
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
        bootstrap.Modal.getOrCreateInstance(document.getElementById('editUserModal')).show();
    });

    $('#editUserForm').on('submit', function(e) {
        e.preventDefault();
        const id = $('#editUserId').val();
        const $submit = $(this).find('button[type="submit"]');
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
            bootstrap.Modal.getInstance(document.getElementById('editUserModal')).hide();
            fetchUsers(false);
        })
        .fail(xhr => {
            if (xhr.status === 422) {
                const errors = xhr.responseJSON?.errors || {};
                const messages = Object.values(errors).map(arr => arr[0]).join('<br>');
                $('#editUserErrors').html(messages);
            } else {
                alert('Unable to update user right now.');
            }
        })
        .always(() => {
            $submit.prop('disabled', false).text('Save changes');
        });
    });

    $tableBody.on('click', '.delete-user', function() {
        const id = $(this).data('id');
        if (!confirm('Delete this user? This cannot be undone.')) return;
        const $btn = $(this);
        const original = $btn.html();
        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span>');

            $.ajax({
            url: `${endpoints.delete}/${id}`,
                type: 'DELETE',
            data: { _token: csrf },
        }).done(() => fetchUsers(false))
          .fail(() => alert('Unable to delete user right now.'))
          .always(() => $btn.prop('disabled', false).html(original));
    });

    $('#downloadUsers').on('click', function() {
        if (!state.data.length) {
            alert('No data to export yet. Please refresh first.');
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
