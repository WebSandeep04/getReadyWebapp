@extends('admin.layouts.app')

@section('title', 'Admin Roles')
@section('page_title', 'Admin Roles')

@section('content')
<div class="container-fluid p-0">
    <!-- Stat Cards Row -->
    <div class="row g-3 mb-4">
        <div class="col-lg-3 col-sm-6">
            <div class="stat-card">
                <div class="stat-card__icon"><i class="bi bi-shield-lock"></i></div>
                <div class="stat-card__label">Total Roles</div>
                <div class="stat-card__value" id="totalRoles">{{ number_format($total ?? 0) }}</div>
                <small>Defined permissions</small>
            </div>
        </div>
    </div>

    <!-- Main Card -->
    <div class="card h-100">
        <div class="card-header bg-white text-dark border-bottom py-3">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold"><i class="bi bi-table me-2"></i>Role Management</h5>
                <div class="d-flex gap-2">
                     <div class="input-group input-group-sm" style="width: 250px;">
                        <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-search"></i></span>
                        <input type="search" id="roleSearch" class="form-control border-start-0 ps-0" placeholder="Search roles...">
                    </div>
                    <button class="btn btn-sm btn-dark" data-bs-toggle="modal" data-bs-target="#roleModal" id="addRoleBtn">
                        <i class="bi bi-plus-lg me-1"></i>Add New
                    </button>
                    <button class="btn btn-sm btn-outline-dark" id="refreshRoles" title="Refresh">
                        <i class="bi bi-arrow-clockwise"></i>
                    </button>
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle admin-table" id="rolesTable">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">ID</th>
                            <th>Role Name</th>
                            <th>Created At</th>
                            <th class="text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <div class="card-footer bg-white border-top border-light py-3 px-4 d-none" id="rolesPagination"></div>
            
            <!-- Empty State -->
            <div class="text-center py-5 d-none" id="rolesEmptyState">
                <div class="mb-3 text-muted opacity-25">
                    <i class="bi bi-search" style="font-size: 3rem;"></i>
                </div>
                <h6 class="fw-bold">No roles match that filter</h6>
                <p class="text-muted small mb-3">Try clearing the search to view all roles.</p>
                <button class="btn btn-sm btn-dark" id="clearRoleFilters">Reset filters</button>
            </div>
        </div>
    </div>

    <!-- Create/Edit Modal -->
    <div class="modal fade" id="roleModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header border-bottom-0 pb-0">
                    <h5 class="modal-title fw-bold" id="roleModalLabel">Add Role</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="roleForm">
                    <div class="modal-body pt-4">
                        <input type="hidden" id="roleId">
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-uppercase text-muted">Role Name</label>
                            <input type="text" class="form-control" id="roleName" required placeholder="e.g. manager" style="text-transform: lowercase;">
                            <small class="text-muted">Enter a simple name like 'editor' or 'support'</small>
                        </div>
                    </div>
                    <div class="modal-footer border-top-0">
                        <button type="button" class="btn btn-sm btn-outline-secondary px-3" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-sm btn-dark px-4">Save Role</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
/* Global Monochrome Overrides */
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
        fetch: "{{ route('roles.json') }}",
        store: "{{ route('roles.store') }}",
        update: "{{ url('/admin/roles') }}",
        delete: "{{ url('/admin/roles') }}",
    };

    const $tableBody = $('#rolesTable tbody');
    const $emptyState = $('#rolesEmptyState');
    const $search = $('#roleSearch');
    const $refresh = $('#refreshRoles');
    const roleModal = new bootstrap.Modal(document.getElementById('roleModal'));

    function fetchRoles(showSpinner = true) {
        if (showSpinner) {
            $refresh.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span>');
        }
        $.getJSON(endpoints.fetch)
            .done(data => {
                state.data = data || [];
                state.page = 1;
                renderRoles();
                updateStats();
            })
            .fail(() => {
                console.error('Failed to load roles');
            })
            .always(() => {
                $refresh.prop('disabled', false).html('<i class="bi bi-arrow-clockwise"></i>');
            });
    }

    function updateStats() {
        const total = state.data.length;
        $('#totalRoles').text(new Intl.NumberFormat().format(total));
    }

    function renderRoles() {
        const term = state.search.toLowerCase();
        let filtered = state.data.filter(item => {
            return (item.name ?? '').toLowerCase().includes(term);
        });

        if (!filtered.length) {
            $('#rolesTable').addClass('d-none');
            $('#rolesPagination').addClass('d-none');
            $emptyState.removeClass('d-none');
            return;
        }

        $('#rolesTable').removeClass('d-none');
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
        const $pager = $('#rolesPagination');

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
                    <button class="btn btn-sm btn-outline-dark border-0 bg-white prev-page" ${state.page === 1 ? 'disabled' : ''} style="width: 32px;">
                        <i class="bi bi-chevron-left"></i>
                    </button>
                    <span class="btn btn-sm btn-outline-dark border-0 bg-white disabled px-3 fw-bold">
                        ${state.page} / ${totalPages}
                    </span>
                    <button class="btn btn-sm btn-outline-dark border-0 bg-white next-page" ${state.page === totalPages ? 'disabled' : ''} style="width: 32px;">
                        <i class="bi bi-chevron-right"></i>
                    </button>
                </div>
            </div>
        `);

        $pager.off('click').on('click', '.prev-page', function() {
            if (state.page > 1) {
                state.page--;
                renderRoles();
            }
        }).on('click', '.next-page', function() {
            if (state.page < totalPages) {
                state.page++;
                renderRoles();
            }
        });
    }

    function renderRow(item) {
        const date = new Date(item.created_at).toLocaleDateString('en-IN', {
            year: 'numeric', month: 'short', day: 'numeric'
        });

        const isSystem = item.name === 'admin' || item.name === 'manager';

        const btns = `
            <div class="btn-group" role="group">
                <button class="btn btn-sm btn-outline-secondary edit-role" data-item='${JSON.stringify(item)}' title="Edit">
                    <i class="bi bi-pencil"></i>
                </button>
                ${isSystem ? '' : `
                <button class="btn btn-sm btn-outline-danger delete-role" data-id="${item.id}" title="Delete">
                    <i class="bi bi-trash"></i>
                </button>
                `}
            </div>
        `;

        return `
            <tr>
                <td class="fw-bold text-muted small ps-4">#${item.id}</td>
                <td>
                    <div class="fw-bold text-dark text-capitalize">${item.name}</div>
                    ${isSystem ? '<span class="badge bg-light text-dark border fw-normal" style="font-size: 0.65rem;">System Role</span>' : ''}
                </td>
                <td class="small text-muted">${date}</td>
                <td class="text-end pe-4">${btns}</td>
            </tr>
        `;
    }

    $search.on('input', function() {
        state.search = $(this).val();
        state.page = 1;
        renderRoles();
    });

    $('#clearRoleFilters').on('click', function() {
        state.search = '';
        state.page = 1;
        $search.val('');
        renderRoles();
    });

    $refresh.on('click', function() {
        fetchRoles(false);
    });

    $('#addRoleBtn').click(function() {
        $('#roleModalLabel').text('Add Role');
        $('#roleForm')[0].reset();
        $('#roleId').val('');
    });

    $tableBody.on('click', '.edit-role', function() {
        const item = $(this).data('item');
        $('#roleId').val(item.id);
        $('#roleName').val(item.name);
        $('#roleModalLabel').text('Edit Role');
        roleModal.show();
    });

    $('#roleForm').on('submit', function(e) {
        e.preventDefault();
        const id = $('#roleId').val();
        const isEdit = !!id;
        const url = isEdit ? `${endpoints.update}/${id}` : endpoints.store;
        const method = isEdit ? 'PUT' : 'POST';
        
        const $submit = $(this).find('button[type="submit"]');
        const originalText = $submit.text();
        $submit.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Saving');

        $.ajax({
            url: url,
            type: method,
            data: {
                name: $('#roleName').val(),
                _token: csrf,
            }
        })
        .done(() => {
            roleModal.hide();
            fetchRoles(false);
            if (window.showAlert) window.showAlert(`Role ${isEdit ? 'updated' : 'added'} successfully`, 'success');
        })
        .fail(xhr => {
            const msg = xhr.responseJSON?.message || 'Unable to save role.';
            if (window.showAlert) window.showAlert(msg, 'danger');
        })
        .always(() => {
            $submit.prop('disabled', false).text(originalText);
        });
    });

    $tableBody.on('click', '.delete-role', function() {
        const id = $(this).data('id');
        if (!confirm('Delete this role? This might affect users assigned to it.')) return;
        
        $.ajax({
            url: `${endpoints.delete}/${id}`,
            type: 'DELETE',
            data: { _token: csrf },
        }).done(() => {
            fetchRoles(false);
            if (window.showAlert) window.showAlert('Role deleted successfully', 'success');
        })
          .fail(xhr => {
              const msg = xhr.responseJSON?.error || 'Unable to delete role.';
              if (window.showAlert) window.showAlert(msg, 'danger');
          });
    });

    fetchRoles();
});
</script>
@endpush
