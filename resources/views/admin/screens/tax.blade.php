@extends('admin.layouts.app')

@section('title', 'Manage Taxes')
@section('page_title', 'Manage Taxes')

@section('content')
<div class="container-fluid p-0">
    <!-- Stat Cards Row -->
    <div class="row g-3 mb-4">
        <div class="col-lg-3 col-sm-6">
            <div class="stat-card">
                <div class="stat-card__icon"><i class="bi bi-receipt-cutoff"></i></div>
                <div class="stat-card__label">Total Taxes</div>
                <div class="stat-card__value" id="totalTaxes">{{ number_format($total ?? 0) }}</div>
                <small>Active tax configurations</small>
            </div>
        </div>
    </div>

    <!-- Main Card -->
    <div class="card h-100">
        <div class="card-header bg-white text-dark border-bottom py-3">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold"><i class="bi bi-table me-2"></i>Tax Management</h5>
                <div class="d-flex gap-2">
                     <div class="input-group input-group-sm" style="width: 250px;">
                        <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-search"></i></span>
                        <input type="search" id="taxSearch" class="form-control border-start-0 ps-0" placeholder="Search taxes...">
                    </div>
                    <button class="btn btn-sm btn-dark" id="addTaxBtn">
                        <i class="bi bi-plus-lg me-1"></i>Add New
                    </button>
                    <button class="btn btn-sm btn-outline-dark" id="refreshTaxes" title="Refresh">
                        <i class="bi bi-arrow-clockwise"></i>
                    </button>
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle admin-table" id="taxesTable">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">ID</th>
                            <th>Tax Name</th>
                            <th>Percentage (%)</th>
                            <th>Status</th>
                            <th>Created At</th>
                            <th class="text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <div class="card-footer bg-white border-top border-light py-3 px-4 d-none" id="taxesPagination"></div>
            
            <!-- Empty State -->
            <div class="text-center py-5 d-none" id="taxesEmptyState">
                <div class="mb-3 text-muted opacity-25">
                    <i class="bi bi-search" style="font-size: 3rem;"></i>
                </div>
                <h6 class="fw-bold">No taxes match that filter</h6>
                <p class="text-muted small mb-3">Try clearing the search to view all taxes.</p>
                <button class="btn btn-sm btn-dark" id="clearTaxFilters">Reset filters</button>
            </div>
        </div>
    </div>

    <!-- Create/Edit Modal -->
    <div class="modal fade" id="taxModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 0 !important;">
                <div class="modal-header border-bottom-0 pb-0">
                    <h5 class="modal-title fw-bold" id="taxModalLabel">Add Tax</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="taxForm">
                    <div class="modal-body pt-4">
                        <input type="hidden" id="taxId" name="id">
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-uppercase text-muted">Tax Name</label>
                            <input type="text" class="form-control" id="taxName" name="name" required placeholder="e.g. GST 18%">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-uppercase text-muted">Percentage (%)</label>
                            <input type="number" step="0.01" class="form-control" id="taxPercentage" name="percentage" required placeholder="e.g. 18.00">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-uppercase text-muted">Status</label>
                            <select class="form-select" id="taxStatus" name="status">
                                <option value="1">Active</option>
                                <option value="0">Inactive</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer border-top-0">
                        <button type="button" class="btn btn-sm btn-outline-secondary px-3" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-sm btn-dark px-4">Save Tax</button>
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

/* Status Badge */
.badge {
    font-weight: 600;
    padding: 0.35em 0.65em;
    font-size: 0.7rem;
    text-transform: uppercase;
}
.bg-success-subtle { background-color: #dcfce7 !important; color: #166534 !important; }
.bg-danger-subtle { background-color: #fee2e2 !important; color: #991b1b !important; }
.cursor-pointer { cursor: pointer; }
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
        fetch: "{{ route('admin.tax.json') }}",
        store: "{{ route('admin.tax.store') }}",
        update: "{{ url('/admin/tax') }}",
        delete: "{{ url('/admin/tax') }}",
        toggle: "{{ url('/admin/tax/toggle') }}",
    };

    const $tableBody = $('#taxesTable tbody');
    const $emptyState = $('#taxesEmptyState');
    const $search = $('#taxSearch');
    const $refresh = $('#refreshTaxes');
    const taxModal = new bootstrap.Modal(document.getElementById('taxModal'));

    // Open Modal for Create
    $('#addTaxBtn').click(function() {
        $('#taxModalLabel').text('Add Tax');
        $('#taxForm')[0].reset();
        $('#taxId').val('');
        taxModal.show();
    });

    function fetchTaxes(showSpinner = true) {
        if (showSpinner) {
            $refresh.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span>');
        }
        $.getJSON(endpoints.fetch)
            .done(data => {
                state.data = data || [];
                state.page = 1;
                renderTaxes();
                updateStats();
            })
            .fail(() => {
                console.error('Failed to load taxes');
            })
            .always(() => {
                $refresh.prop('disabled', false).html('<i class="bi bi-arrow-clockwise"></i>');
            });
    }

    function updateStats() {
        const total = state.data.length;
        $('#totalTaxes').text(new Intl.NumberFormat().format(total));
    }

    function renderTaxes() {
        const term = state.search.toLowerCase();
        let filtered = state.data.filter(item => {
            const haystack = `${item.name ?? ''}`.toLowerCase();
            return haystack.includes(term);
        });

        if (!filtered.length) {
            $('#taxesTable').addClass('d-none');
            $('#taxesPagination').addClass('d-none');
            $emptyState.removeClass('d-none');
            return;
        }

        $('#taxesTable').removeClass('d-none');
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
        const $pager = $('#taxesPagination');

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
                renderTaxes();
            }
        }).on('click', '.next-page', function() {
            if (state.page < totalPages) {
                state.page++;
                renderTaxes();
            }
        });
    }

    function renderRow(item) {
        const date = new Date(item.created_at).toLocaleDateString('en-IN', {
            year: 'numeric', month: 'short', day: 'numeric'
        });

        const statusBadge = item.status == 1 
            ? '<span class="badge bg-success-subtle status-toggle cursor-pointer" data-id="'+item.id+'">Active</span>' 
            : '<span class="badge bg-danger-subtle status-toggle cursor-pointer" data-id="'+item.id+'">Inactive</span>';

        const btns = `
            <div class="btn-group" role="group">
                <button class="btn btn-sm btn-outline-secondary edit-tax" data-item='${JSON.stringify(item)}' title="Edit">
                    <i class="bi bi-pencil"></i>
                </button>
                <button class="btn btn-sm btn-outline-danger delete-tax" data-id="${item.id}" title="Delete">
                    <i class="bi bi-trash"></i>
                </button>
            </div>
        `;

        return `
            <tr>
                <td class="fw-bold text-muted small ps-4">#${item.id}</td>
                <td><div class="fw-bold text-dark">${item.name}</div></td>
                <td><div class="fw-bold text-dark">${item.percentage}%</div></td>
                <td>${statusBadge}</td>
                <td class="small text-muted">${date}</td>
                <td class="text-end pe-4">${btns}</td>
            </tr>
        `;
    }

    $search.on('input', function() {
        state.search = $(this).val();
        state.page = 1;
        renderTaxes();
    });

    $('#clearTaxFilters').on('click', function() {
        state.search = '';
        state.page = 1;
        $search.val('');
        renderTaxes();
    });

    $refresh.on('click', function() {
        fetchTaxes(false);
    });

    $tableBody.on('click', '.edit-tax', function() {
        const item = $(this).data('item');
        $('#taxId').val(item.id);
        $('#taxName').val(item.name);
        $('#taxPercentage').val(item.percentage);
        $('#taxStatus').val(item.status);
        $('#taxModalLabel').text('Edit Tax');
        taxModal.show();
    });

    $tableBody.on('click', '.status-toggle', function() {
        const id = $(this).data('id');
        $.ajax({
            url: `${endpoints.toggle}/${id}`,
            type: 'POST',
            headers: { 'X-CSRF-TOKEN': csrf }
        }).done(() => fetchTaxes(false));
    });

    $('#taxForm').on('submit', function(e) {
        e.preventDefault();
        const id = $('#taxId').val();
        const isEdit = !!id;
        const url = isEdit ? `${endpoints.update}/${id}` : endpoints.store;
        
        const formData = $(this).serialize();

        const $submit = $(this).find('button[type="submit"]');
        const originalText = $submit.text();
        $submit.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Saving');

        $.ajax({
            url: url,
            type: 'POST',
            data: formData + (isEdit ? '&_method=PUT' : ''),
            headers: {
                'X-CSRF-TOKEN': csrf
            }
        })
        .done(response => {
            if (response.success) {
                taxModal.hide();
                fetchTaxes(false);
            } else {
                alert(response.message || 'Unable to save tax.');
            }
        })
        .fail(xhr => {
            if (xhr.status === 422) {
                const errors = xhr.responseJSON.errors;
                let errorMsg = '';
                for (const key in errors) {
                    errorMsg += errors[key][0] + '\n';
                }
                alert(errorMsg);
            } else {
                alert('Unable to save tax.');
            }
        })
        .always(() => {
            $submit.prop('disabled', false).text(originalText);
        });
    });

    $tableBody.on('click', '.delete-tax', function() {
        const id = $(this).data('id');
        if (!confirm('Delete this tax?')) return;
        
        $.ajax({
            url: `${endpoints.delete}/${id}`,
            method: 'POST',
            data: { _method: 'DELETE' },
            headers: { 'X-CSRF-TOKEN': csrf },
        }).done(() => fetchTaxes(false))
          .fail(() => alert('Unable to delete tax.'));
    });

    fetchTaxes();
});
</script>
@endpush
