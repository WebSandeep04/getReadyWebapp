@extends('admin.layouts.app')

@section('title', 'Security Deposits')
@section('page_title', 'Security Deposits')

@push('styles')
<style>
/* Reusing core styles for consistency */
*, ::before, ::after { border-radius: 0 !important; }
.stat-card {
    position: relative;
    padding: 1.25rem;
    color: #000;
    background: #fff;
    box-shadow: 0 8px 25px rgba(0,0,0,0.1);
    height: 100%;
    display: flex;
    flex-direction: column;
    justify-content: center;
    transition: all 0.3s ease;
}
.stat-card__label { font-size: .7rem; font-weight: 700; color: #333; text-transform: none; }
.stat-card__value { font-size: 2rem; font-weight: 800; margin: .1rem 0; color: #000; }
.stat-card__icon { position: absolute; top: 0.75rem; right: 0.75rem; font-size: 1.5rem; opacity: 1; color: #000; }

.table th {
    background: #f8fafc;
    color: #000;
    border-bottom: 1px solid #ddd;
    font-weight: 700;
    font-size: .65rem;
    letter-spacing: normal;
}
.table td { vertical-align: middle; font-size: .75rem; padding: 0.5rem !important; border-bottom: 1px solid #eee; color: #000; }

.btn-action { padding: 0.25rem 0.5rem; font-size: 0.75rem; }
.status-badge { font-size: 0.7rem; padding: 0.2rem 0.5rem;  }

.badge-held { background: #ffeeba; color: #856404; }
.badge-returned { background: #d4edda; color: #155724; }
.badge-due { background: #f8d7da; color: #721c24; }

</style>
@endpush

@section('content')
<div class="container mt-4">

    <!-- Stats Row -->
    <div class="row g-3 mb-4">
        <div class="col-md-4 col-sm-6">
            <div class="stat-card">
                <div class="stat-card__icon"><i class="bi bi-shield-lock-fill"></i></div>
                <div class="stat-card__label">Total Security Held</div>
                <div class="stat-card__value" id="stat-total-held">₹{{ number_format($stats['total_held']) }}</div>
                <small class="text-muted">For active rentals</small>
            </div>
        </div>
        <div class="col-md-4 col-sm-6">
            <div class="stat-card">
                <div class="stat-card__icon"><i class="bi bi-exclamation-circle-fill text-danger"></i></div>
                <div class="stat-card__label">Need to Return</div>
                <div class="stat-card__value text-danger" id="stat-need-return">₹{{ number_format($stats['need_to_return']) }}</div>
                <small class="text-muted">Order returned but security pending</small>
            </div>
        </div>
         <div class="col-md-4 col-sm-6">
            <div class="stat-card">
                <div class="stat-card__icon"><i class="bi bi-arrow-return-left text-success"></i></div>
                <div class="stat-card__label">Returned Security</div>
                <div class="stat-card__value text-success" id="stat-returned">₹{{ number_format($stats['returned']) }}</div>
                <small class="text-muted">Total refunded</small>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4 shadow-sm border-0">
        <div class="card-body py-2">
            <form id="securityFilterForm" class="row g-2 align-items-center">
                <div class="col-auto">
                    <label class="fw-bold small me-2">Show:</label>
                </div>
                <div class="col-auto">
                    <select name="status" class="form-select form-select-sm" id="statusFilter">
                        <option value="">All Scenarios</option>
                        <option value="held">Currently Held (Active)</option>
                        <option value="returned">Pending Return (Returned)</option>
                        <option value="completed">Completed (Refunded)</option>
                    </select>
                </div>
            </form>
        </div>
    </div>

    <!-- Table -->
    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th>Order ID</th>
                            <th>Customer</th>
                            <th>Rental Items</th>
                            <th>Security Amount</th>
                            <th>Status</th> <!-- Held, Due, Returned -->
                            <th>Order Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="securityTableBody">
                        <tr><td colspan="7" class="text-center py-4 text-muted">Loading...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white" id="securityPagination">
            <!-- Pagination loaded via AJAX -->
        </div>
    </div>

</div>

    <!-- Action Confirmation Modal -->
    <div class="modal fade" id="actionConfirmModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header border-bottom-0 pb-0">
                    <h5 class="modal-title fw-bold" id="actionConfirmTitle">Confirm Action</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body pt-2">
                    <p class="text-muted mb-0" id="actionConfirmMsg">Are you sure you want to proceed?</p>
                </div>
                <div class="modal-footer border-top-0 pt-0">
                    <button type="button" class="btn btn-link text-decoration-none text-muted" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-dark px-4" id="confirmActionBtn">
                        Confirm
                    </button>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
<script>
$(function() {
    const fetchUrl = "{{ route('admin.security.fetch') }}";

    // --- Data Loading ---
    function fetchSecurityData(extra = {}) {
        const params = $('#securityFilterForm').serializeArray().reduce((obj, item) => {
            obj[item.name] = item.value;
            return obj;
        }, {});

        Object.assign(params, extra);

        $('#securityTableBody').html('<tr><td colspan="7" class="text-center py-4 text-muted">Loading...</td></tr>');

        $.ajax({
            url: fetchUrl,
            data: params,
            dataType: 'json',
            success: function(res) {
                $('#securityTableBody').html(res.table_html);
                $('#securityPagination').html(res.pagination_html);
                
                // Update stats
                if(res.stats) {
                    $('#stat-total-held').text('₹' + Number(res.stats.total_held).toLocaleString());
                    $('#stat-need-return').text('₹' + Number(res.stats.need_to_return).toLocaleString());
                    $('#stat-returned').text('₹' + Number(res.stats.returned).toLocaleString());
                }
            },
            error: function() {
                $('#securityTableBody').html('<tr><td colspan="7" class="text-center py-4 text-danger">Error loading data.</td></tr>');
            }
        });
    }

    // Initial load
    fetchSecurityData();

    // Filter change
    $('#statusFilter').on('change', function() {
        fetchSecurityData({ page: 1 });
    });

    // Pagination
    $(document).on('click', '#securityPagination a', function(e) {
        e.preventDefault();
        const page = new URL(this.href).searchParams.get('page');
        fetchSecurityData({ page: page });
    });

    // --- Action Handling ---
    let pendingAction = null;

    function showConfirmModal(title, message, action) {
        $('#actionConfirmTitle').text(title);
        $('#actionConfirmMsg').text(message);
        pendingAction = action;
        $('#actionConfirmModal').modal('show');
    }

    $('#confirmActionBtn').on('click', function() {
        if (pendingAction) {
            const $btn = $(this);
            const originalHtml = $btn.html();
            $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status"></span>');
            
            Promise.resolve(pendingAction()).finally(() => {
                $('#actionConfirmModal').modal('hide');
                $btn.prop('disabled', false).html(originalHtml);
                pendingAction = null;
            });
        }
    });

    $(document).on('click', '.mark-returned-btn', function() {
        const id = $(this).data('id');
        
        showConfirmModal(
            'Confirm Return',
            'Are you sure you want to mark this security deposit as Returned? This will update the financial stats and order status.',
            () => {
                return new Promise((resolve) => {
                    $.post(`{{ url('/admin/security/mark-returned') }}/${id}`, {
                        _token: '{{ csrf_token() }}'
                    }, function(res) {
                        if(res.success) {
                            fetchSecurityData(); 
                            window.showAlert(res.message || 'Security deposit marked as returned.', 'success');
                        } else {
                            window.showAlert(res.message || 'Action failed', 'danger');
                        }
                        resolve();
                    }).fail(function(xhr) {
                        window.showAlert('Error: ' + (xhr.responseJSON?.message || 'Something went wrong'), 'danger');
                        resolve();
                    });
                });
            }
        );
    });
});
</script>
@endpush
