@extends('admin.layouts.app')

@section('title', 'Orders')
@section('page_title', 'Orders & Returns')

@push('styles')
<style>
*, ::before, ::after { border-radius: 0 !important; }
.orders-hero {
    background: #fff;
    padding: 1.5rem;
    color: #000;
    margin-bottom: 2rem;
    box-shadow: 0 15px 45px rgba(0,0,0,0.12);
}
.orders-hero__title { font-size: 1.25rem; font-weight: 700; color: #000; }
.orders-hero__subtitle { margin: 0; opacity: 0.8; font-size: 0.85rem; color: #000; }
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
    min-height: 110px;
    transition: all 0.3s ease;
}
.stat-card:hover { transform: translateY(-4px); box-shadow: 0 15px 35px rgba(0,0,0,0.15); }
.stat-card__label {
    text-transform: none;
    letter-spacing: normal;
    font-size: .7rem;
    font-weight: 700;
    color: #333;
}
.stat-card__value {
    font-size: 2rem;
    font-weight: 800;
    margin: .1rem 0;
    color: #000;
}
.stat-card__icon {
    position: absolute;
    top: 0.75rem;
    right: 0.75rem;
    font-size: 1.5rem;
    opacity: 1;
    color: #000;
}
.stat-total, .stat-overdue, .stat-due, .stat-purchase { background: #fff !important; color: #000 !important; }
.table th {
    background: #f8fafc;
    color: #000;
    border-bottom: 1px solid #ddd;
    font-weight: 700;
    font-size: .65rem;
    letter-spacing: normal;
    text-transform: none;
}
.table td { vertical-align: middle; font-size: .65rem; padding: 0.25rem 0.4rem !important; border-bottom: 1px solid #eee; color: #000; }
.order-type-badge {
    font-size: .6rem;
    padding: .15rem .4rem;
    font-weight: 600;
    border: 1px solid #000;
    background: #fff;
    color: #000;
}
.overdue-row { border-left: 3px solid #000; background: #fafafa; }
.timeline-flag {
    display: inline-flex;
    align-items: center;
    gap: .3rem;
    padding: .15rem .4rem;
    font-size: .65rem;
    font-weight: 600;
    border: 1px solid #eee;
}
.timeline-flag.overdue { background:#000; color:#fff; border-color: #000; }
.timeline-flag.due-soon { background:#f1f1f1; color:#000; border-color: #ddd; }
.timeline-flag.completed { background:#fff; color:#000; border-color: #000; }
.filter-card {
    background: #fff;
    padding: 1rem;
    box-shadow: 0 10px 30px rgba(0,0,0,0.05);
}
.filter-form .form-label { font-weight: 700; color: #000; font-size: 0.65rem; margin-bottom: 0.25rem; }
.filter-form .form-control,
.filter-form .form-select {
    border: 1px solid #eee !important;
    background: #fff !important;
    box-shadow: 0 2px 4px rgba(0,0,0,0.03);
    color: #000 !important;
    font-size: 0.75rem;
    padding: 0.35rem 0.5rem;
}
.card { border: none; box-shadow: 0 20px 50px rgba(0,0,0,0.1); background: #fff; }
.card-header { padding: 1rem; background: #fff !important; color: #000 !important; border-bottom: 1px solid #eee; }
.btn { border: none; box-shadow: 0 4px 10px rgba(0,0,0,0.12); transition: all 0.2s; font-size: 0.75rem; padding: 0.4rem 0.8rem; }
.btn:hover { transform: translateY(-1px); box-shadow: 0 8px 18px rgba(0,0,0,0.18); }
.btn-outline-secondary { background: #fff !important; color: #000 !important; border: 1px solid #000 !important; }
.btn-primary, .btn-success { background: #000 !important; color: #fff !important; }
.btn-danger { background: #fff !important; color: #000 !important; border: 1px solid #000 !important; }
.badge { border-radius: 0 !important; }
</style>
@endpush

@section('content')
<div class="container mt-4">
    <div class="row g-3 mb-4">
        <div class="col-md-3 col-sm-6">
            <div class="stat-card stat-total">
                <div class="stat-card__icon text-dark"><i class="bi bi-graph-up"></i></div>
                <div class="stat-card__label fw-bold text-dark">Total orders</div>
                <div class="stat-card__value fw-bold text-dark" id="statTotal">{{ number_format($stats['total']) }}</div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="stat-card stat-overdue">
                <div class="stat-card__icon text-dark"><i class="bi bi-exclamation-octagon"></i></div>
                <div class="stat-card__label fw-bold text-dark">Overdue returns</div>
                <div class="stat-card__value fw-bold text-dark" id="statOverdue">{{ number_format($stats['overdue']) }}</div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="stat-card stat-due">
                <div class="stat-card__icon text-dark"><i class="bi bi-alarm"></i></div>
                <div class="stat-card__label fw-bold text-dark">Due today</div>
                <div class="stat-card__value fw-bold text-dark" id="statDueToday">{{ number_format($stats['due_today']) }}</div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="stat-card stat-purchase">
                <div class="stat-card__icon text-dark"><i class="bi bi-bag-check"></i></div>
                <div class="stat-card__label fw-bold text-dark">Purchase orders</div>
                <div class="stat-card__value fw-bold text-dark" id="statPurchase">{{ number_format($stats['purchase']) }}</div>
            </div>
        </div>
    </div>

    <div class="card mb-4 filter-card">
        <div class="card-body">
            <form method="GET" class="filter-form js-order-filter-form">
                <div class="row g-2 align-items-end">
                    <div class="col-lg-2 col-md-3 col-6">
                        <label class="form-label small text-uppercase fw-bold text-dark">Status</label>
                        <select name="status" class="form-select">
                            <option value="">All</option>
                            @foreach($statuses as $status)
                                <option value="{{ $status }}" {{ ($filters['status'] ?? '') === $status ? 'selected' : '' }}>{{ $status }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-lg-2 col-md-3 col-6">
                        <label class="form-label small text-uppercase fw-bold text-dark">Type</label>
                        <select name="type" class="form-select">
                            <option value="">All</option>
                            <option value="rental" {{ ($filters['type'] ?? '') === 'rental' ? 'selected' : '' }}>Rental only</option>
                            <option value="purchase" {{ ($filters['type'] ?? '') === 'purchase' ? 'selected' : '' }}>Purchase only</option>
                            <option value="mixed" {{ ($filters['type'] ?? '') === 'mixed' ? 'selected' : '' }}>Mixed</option>
                        </select>
                    </div>
                    <div class="col-lg-2 col-md-3 col-6">
                        <label class="form-label small text-uppercase fw-bold text-dark">Return</label>
                        <select name="return_state" class="form-select">
                            <option value="">All</option>
                            <option value="overdue" {{ ($filters['return_state'] ?? '') === 'overdue' ? 'selected' : '' }}>Overdue</option>
                            <option value="due_soon" {{ ($filters['return_state'] ?? '') === 'due_soon' ? 'selected' : '' }}>Due soon</option>
                            <option value="completed" {{ ($filters['return_state'] ?? '') === 'completed' ? 'selected' : '' }}>Returned</option>
                        </select>
                    </div>
                    <div class="col-lg-2 col-md-3 col-6">
                        <label class="form-label small text-uppercase fw-bold text-dark">Payment</label>
                        <select name="payment_status" class="form-select">
                            <option value="">All</option>
                            @foreach($paymentStatuses as $paymentStatus)
                                <option value="{{ $paymentStatus }}" {{ ($filters['payment_status'] ?? '') === $paymentStatus ? 'selected' : '' }}>
                                    {{ ucfirst($paymentStatus) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-lg-2 col-md-3 col-6">
                        <label class="form-label small text-uppercase fw-bold text-dark">From</label>
                        <input type="date" class="form-control" name="placed_from" value="{{ $filters['placed_from'] ?? '' }}">
                    </div>
                    <div class="col-lg-auto col-md-3 col-6">
                        <label class="form-label small text-uppercase fw-bold text-dark">To</label>
                        <input type="date" class="form-control" name="placed_to" value="{{ $filters['placed_to'] ?? '' }}">
                    </div>
                    <!-- <div class="col-auto">
                        <button type="button" class="btn btn-outline-secondary" id="ordersResetFilters" title="Reset Filters">
                            <i class="bi bi-arrow-counterclockwise"></i>
                        </button>
                    </div> -->
                </div>
            </form>
        </div>
    </div>

    <div class="card mb-4 border-0 shadow-sm" style="background: #fff;">
        <div class="card-body p-1">
            <div class="input-group">
                <span class="input-group-text bg-white border-0 ps-3"><i class="bi bi-search text-muted small"></i></span>
                <input type="text" id="orderSearchInput" class="form-control border-0 shadow-none ps-2" 
                       style="font-size: 0.9rem;"
                       value="{{ $filters['search'] ?? '' }}" 
                       placeholder="Search Order ID, Buyer, or Amount...">
            </div>
        </div>
    </div>

    <div class="card order-card">
        <div class="card-body p-0">
            <div class="table-responsive" style="min-height: 300px;">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Buyer</th>
                            <th>Type</th>
                            <th>Total</th>
                            <th>Security</th>
                            <th>Return / Due</th>
                            <th>Status</th>
                            <th>Payment</th>
                            <th>Placed</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="ordersTableBody">
                        @include('admin.components.orders-rows', ['orders' => $orders])
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white" id="ordersPagination">
            @include('admin.components.orders-pagination', ['orders' => $orders])
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

    <!-- Return Review Modal -->
    <div class="modal fade" id="returnReviewModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header border-bottom-0">
                    <h6 class="modal-title fw-bold">Review Return Request</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body pt-0">
                    <div class="alert alert-light border small">
                        <strong>Reason:</strong> <span id="rr_reason"></span><br>
                        <strong>Details:</strong> <span id="rr_details"></span>
                    </div>
                    <h6>Evidence Images:</h6>
                    <div id="rr_images" class="d-flex flex-wrap gap-2 mb-3"></div>

                    <div id="rejectionSection" style="display:none;" class="mt-3 p-3 border-top bg-light">
                        <label class="form-label small fw-bold">Rejection Reason</label>
                        <textarea id="rejectionReasonText" class="form-control mb-2" rows="2" placeholder="Tell the buyer why the request was rejected..."></textarea>
                        <button class="btn btn-sm btn-dark" id="submitRejectBtn">Confirm Rejection</button>
                        <button class="btn btn-sm btn-link text-muted" onclick="$('#rejectionSection').hide();">Cancel</button>
                    </div>
                </div>
                <div class="modal-footer border-top-0">
                    <button type="button" class="btn btn-link text-decoration-none text-muted" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-outline-danger btn-sm" id="rejectReturnBtn">Reject Request</button>
                    <button type="button" class="btn btn-success btn-sm" id="approveReturnBtn">Approve & Generate AWB</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Order Details Modal -->
    <div class="modal fade" id="orderDetailsModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header border-bottom">
                    <h5 class="modal-title fw-bold">Order Details: <span id="mdl_order_id"></span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4 bg-light">
                    <!-- General Info (Full width) -->
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-body">
                            <h6 class="fw-bold text-uppercase small text-muted mb-3">General Info</h6>
                            <div id="shipmentErrorAlert" class="alert alert-danger d-none d-flex align-items-center" role="alert">
                                <div>
                                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                                    <strong>Shipment Failed:</strong> <span id="shipmentErrorText"></span>
                                </div>
                                <button type="button" class="btn btn-sm btn-danger ms-auto" id="retryShipmentBtn">Retry Shipment</button>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="row mb-2"><div class="col-4 text-muted">Status:</div><div class="col-8 fw-semibold" id="mdl_status"></div></div>
                                    <div class="row mb-2"><div class="col-4 text-muted">Type:</div><div class="col-8 fw-semibold" id="mdl_type"></div></div>
                                    <div class="row mb-2"><div class="col-4 text-muted">Placed On:</div><div class="col-8" id="mdl_placed"></div></div>
                                </div>
                                <div class="col-md-6">
                                    <div class="row mb-2"><div class="col-4 text-muted">Total Amount:</div><div class="col-8 fw-bold">₹<span id="mdl_total"></span></div></div>
                                    <div class="row mb-2"><div class="col-4 text-muted">Security:</div><div class="col-8 fw-semibold">₹<span id="mdl_security"></span></div></div>
                                    <div class="row mb-2"><div class="col-4 text-muted">Rental Period:</div><div class="col-8" id="mdl_rental_period"></div></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Buyer & Seller Info -->
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <div class="card h-100 border-0 shadow-sm">
                                <div class="card-body">
                                    <h6 class="fw-bold text-uppercase small text-muted mb-3">Buyer & Delivery</h6>
                                    <div class="row mb-2"><div class="col-4 text-muted">Name:</div><div class="col-8 fw-semibold" id="mdl_buyer_name"></div></div>
                                    <div class="row mb-2"><div class="col-4 text-muted">Email:</div><div class="col-8" id="mdl_buyer_email"></div></div>
                                    <div class="row mb-2"><div class="col-4 text-muted">Phone:</div><div class="col-8" id="mdl_buyer_phone"></div></div>
                                    <div class="row mb-2"><div class="col-4 text-muted">Address:</div><div class="col-8 small" id="mdl_delivery_address"></div></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card h-100 border-0 shadow-sm">
                                <div class="card-body">
                                    <h6 class="fw-bold text-uppercase small text-muted mb-3">Seller Details</h6>
                                    <div id="mdl_seller_info">
                                        <!-- Will populate dynamically -->
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Items List -->
                    <h6 class="fw-bold text-uppercase small text-muted mb-3 mt-4">Order Items</h6>
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0 align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Item</th>
                                        <th>Type</th>
                                        <th>Price</th>
                                        <th>Security Deposit</th>
                                    </tr>
                                </thead>
                                <tbody id="mdl_items_body"></tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Payments and Shipments -->
                    <div class="row g-3">
                        <div class="col-md-6">
                            <h6 class="fw-bold text-uppercase small text-muted mb-3">Payments</h6>
                            <div class="card border-0 shadow-sm">
                                <div class="card-body p-0">
                                    <ul class="list-group list-group-flush" id="mdl_payments_list"></ul>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <h6 class="fw-bold text-uppercase small text-muted mb-3">Shipments</h6>
                            <div class="card border-0 shadow-sm">
                                <div class="card-body p-0">
                                    <ul class="list-group list-group-flush" id="mdl_shipments_list"></ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(function() {
    const $form = $('.js-order-filter-form');
    const $tableBody = $('#ordersTableBody');
    const $pagination = $('#ordersPagination');
    const $resetBtn = $('#ordersResetFilters');
    const $searchInput = $('#orderSearchInput');
    const statsEls = {
        total: $('#statTotal'),
        overdue: $('#statOverdue'),
        dueToday: $('#statDueToday'),
        purchase: $('#statPurchase')
    };
    const endpoint = "{{ route('admin.orders.data') }}";

    let activeRequest = null;
    let pendingAction = null; // Store the action to be confirmed

    function serializeFilters(extra = {}) {
        const data = {};
        // Get form data
        $form.serializeArray().forEach(({ name, value }) => {
            data[name] = value;
        });
        // Include search input manually
        data['search'] = $searchInput.val();
        
        return Object.assign(data, extra);
    }

    function renderLoading() {
        $tableBody.html('<tr><td colspan="10" class="text-center py-4 text-muted">Loading...</td></tr>');
    }

    function updateStats(stats) {
        if (!stats) return;
        statsEls.total.text(Number(stats.total).toLocaleString());
        statsEls.overdue.text(Number(stats.overdue).toLocaleString());
        statsEls.dueToday.text(Number(stats.due_today).toLocaleString());
        statsEls.purchase.text(Number(stats.purchase).toLocaleString());
    }

    function fetchOrders(extra = {}) {
        if (activeRequest) {
            activeRequest.abort();
        }

        const params = serializeFilters(extra);
        renderLoading();

        activeRequest = $.ajax({
            url: endpoint,
            data: params,
            dataType: 'json'
        }).done(function(response) {
            $tableBody.html(response.table_html || '');
            $pagination.html(response.pagination_html || '');
            updateStats(response.stats);
        }).fail(function(xhr, status) {
            if (status !== 'abort') {
                $tableBody.html('<tr><td colspan="10" class="text-center text-danger py-4">Unable to load orders. Please try again.</td></tr>');
            }
        });
    }

    $form.on('change', 'select, input[type="date"]', function() {
        fetchOrders();
    });

    let searchTimer;
    $searchInput.on('input', function() {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(fetchOrders, 500);
    });

    // --- GENERIC MODAL CONFIRMATION HANDLER ---
    
    function showConfirmModal(title, message, actionCallback) {
        $('#actionConfirmTitle').text(title);
        $('#actionConfirmMsg').text(message);
        pendingAction = actionCallback;
        $('#actionConfirmModal').modal('show');
    }

    $('#confirmActionBtn').on('click', function() {
        if (pendingAction) {
            const $btn = $(this);
            const originalHtml = $btn.html();
            $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status"></span>');
            
            // Execute the stored action
            // We pass a 'done' callback to the action so it can reset the modal state
            Promise.resolve(pendingAction()).finally(() => {
                $('#actionConfirmModal').modal('hide');
                $btn.prop('disabled', false).html(originalHtml);
                pendingAction = null;
            });
        }
    });

    // Mark as Returned Logic
    $tableBody.on('click', '.mark-returned-btn', function(e) {
        e.preventDefault();
        const $btn = $(this);
        const orderId = $btn.data('order-id');
        
        showConfirmModal(
            'Confirm Return',
            'Are you sure you want to mark this order as Returned? This will increment stock for rented items.',
            () => {
                return new Promise((resolve) => {
                    $.ajax({
                        url: `/admin/orders/${orderId}/return`,
                        type: 'POST',
                        data: { _token: '{{ csrf_token() }}' },
                        success: function(response) {
                            if (response.success) {
                                fetchOrders();
                                // Optional: Replace generic alerts with toasts later?
                                window.showAlert(response.message || 'Order marked as returned.', 'success'); 
                            } else {
                                window.showAlert(response.message || 'Action failed', 'danger');
                            }
                            resolve();
                        },
                        error: function(xhr) {
                            window.showAlert('Error: ' + (xhr.responseJSON?.message || 'Something went wrong'), 'danger');
                            resolve();
                        }
                    });
                });
            }
        );
    });

    // Update Status Logic (General)
    $tableBody.on('click', '.update-status-btn', function(e) {
        e.preventDefault();
        const $btn = $(this);
        const orderId = $btn.data('order-id');
        const newStatus = $btn.data('status');
        
        showConfirmModal(
            'Update Status',
            `Are you sure you want to change order status to ${newStatus}?`,
            () => {
                return new Promise((resolve) => {
                    $.ajax({
                        url: `/admin/orders/${orderId}/status`,
                        type: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}',
                            status: newStatus
                        },
                        success: function(response) {
                            if (response.success) {
                                fetchOrders();
                                window.showAlert(response.message || 'Status updated successfully.', 'success');
                            } else {
                                window.showAlert(response.message || 'Action failed', 'danger');
                            }
                            resolve();
                        },
                        error: function(xhr) {
                            window.showAlert('Error: ' + (xhr.responseJSON?.message || 'Something went wrong'), 'danger');
                            resolve();
                        }
                    });
                });
            }
        );
    });

    // Retry Shipment Logic
    $tableBody.on('click', '.retry-shipment-btn', function(e) {
        e.preventDefault();
        const $btn = $(this);
        const orderId = $btn.data('order-id');
        
        showConfirmModal(
            'Retry Shipment',
            'Attempt to create shipment for this order again?',
            () => {
                return new Promise((resolve) => {
                    $.ajax({
                        url: `/admin/orders/${orderId}/retry-shipment`,
                        type: 'POST',
                        data: { _token: '{{ csrf_token() }}' },
                        success: function(response) {
                            if (response.success) {
                                fetchOrders();
                                window.showAlert(response.message || 'Shipment retry initiated.', 'success');
                            } else {
                                window.showAlert(response.message || 'Action failed', 'danger');
                            }
                            resolve();
                        },
                        error: function(xhr) {
                            window.showAlert('Error: ' + (xhr.responseJSON?.message || 'Something went wrong'), 'danger');
                            resolve();
                        }
                    });
                });
            }
        );
    });

    // Refund Payment Logic (Manual)
    $tableBody.on('click', '.refund-payment-btn', function(e) {
        e.preventDefault();
        const $btn = $(this);
        const orderId = $btn.data('order-id');
        
        showConfirmModal(
            'Manual Refund',
            'Are you sure you want to mark this transaction as Refunded? This will update payment history and revenue stats.',
            () => {
                return new Promise((resolve) => {
                    $.ajax({
                        url: `/admin/orders/${orderId}/refund-payment`,
                        type: 'POST',
                        data: { _token: '{{ csrf_token() }}' },
                        success: function(response) {
                            if (response.success) {
                                fetchOrders();
                                window.showAlert(response.message || 'Payment marked as refunded.', 'success');
                            } else {
                                window.showAlert(response.message || 'Action failed', 'danger');
                            }
                            resolve();
                        },
                        error: function(xhr) {
                            window.showAlert('Error: ' + (xhr.responseJSON?.message || 'Something went wrong'), 'danger');
                            resolve();
                        }
                    });
                });
            }
        );
    });

    // Process Issue Refund (Full Refund for Request Returns)
    $tableBody.on('click', '.process-issue-refund-btn', function(e) {
        e.preventDefault();
        const $btn = $(this);
        const orderId = $btn.data('order-id');
        
        showConfirmModal(
            'Process Full Refund',
            'This item was returned due to an issue. Are you sure you want to process a FULL REFUND (Rent + Security) for Order #'+orderId+'?',
            () => {
                return new Promise((resolve) => {
                    $.ajax({
                        url: `/admin/orders/${orderId}/process-issue-refund`,
                        type: 'POST',
                        data: { _token: '{{ csrf_token() }}' },
                        success: function(response) {
                            if (response.success) {
                                fetchOrders();
                                window.showAlert(response.message || 'Full refund processed.', 'success');
                            } else {
                                window.showAlert(response.message || 'Action failed', 'danger');
                            }
                            resolve();
                        },
                        error: function(xhr) {
                            window.showAlert('Error: ' + (xhr.responseJSON?.message || 'Something went wrong'), 'danger');
                            resolve();
                        }
                    });
                });
            }
        );
    });

    $form.on('submit', function(e) {
        e.preventDefault();
        fetchOrders();
    });

    $resetBtn.on('click', function() {
        $form[0].reset();
        fetchOrders();
    });

    // --- RETURN REVIEW HANDLERS ---
    let reviewingOrderId = null;

    $tableBody.on('click', '.review-return-btn', function() {
        const $btn = $(this);
        reviewingOrderId = $btn.data('order-id');
        $('#rr_reason').text($btn.data('reason'));
        $('#rr_details').text($btn.data('details'));
        
        let imagesHtml = '';
        const images = $btn.data('images');
        if (images && images.length > 0) {
            images.forEach(path => {
                imagesHtml += `<img src="/storage/${path}" style="width:120px; height:120px; object-fit:cover; border:1px solid #eee; cursor:pointer;" onclick="window.open(this.src)">`;
            });
        } else {
            imagesHtml = '<span class="text-muted italic">No images provided.</span>';
        }
        $('#rr_images').html(imagesHtml);
        $('#rejectionSection').hide();
        $('#returnReviewModal').modal('show');
    });

    $('#approveReturnBtn').on('click', function() {
        const $btn = $(this);
        $btn.prop('disabled', true).html('Processing...');
        
        $.ajax({
            url: `/admin/orders/${reviewingOrderId}/approve-return`,
            type: 'POST',
            data: { _token: '{{ csrf_token() }}' },
            success: function(response) {
                if (response.success) {
                    fetchOrders();
                    $('#returnReviewModal').modal('hide');
                    window.showAlert(response.message, 'success');
                } else {
                    let errMsg = response.message;
                    if (response.errors && response.errors.length > 0) {
                        errMsg += ' Details: ' + response.errors.join(', ');
                    }
                    window.showAlert(errMsg, 'danger');
                }
            },
            error: function(xhr) {
                let errMsg = xhr.responseJSON?.message || 'Something went wrong';
                if (xhr.responseJSON?.errors) {
                    const detailedErrors = Array.isArray(xhr.responseJSON.errors) 
                        ? xhr.responseJSON.errors 
                        : Object.values(xhr.responseJSON.errors).flat();
                    if (detailedErrors.length > 0) {
                        errMsg += ': ' + detailedErrors.join(', ');
                    }
                }
                window.showAlert('Error: ' + errMsg, 'danger');
            },
            complete: function() {
                $btn.prop('disabled', false).html('Approve & Generate AWB');
            }
        });
    });

    $('#rejectReturnBtn').on('click', function() {
        $('#rejectionSection').slideDown();
    });

    $('#submitRejectBtn').on('click', function() {
        const reason = $('#rejectionReasonText').val();
        if (!reason) return showAlert('Please provide a reason for rejection.', 'warning');
        
        const $btn = $(this);
        $btn.prop('disabled', true).html('Rejecting...');

        $.ajax({
            url: `/admin/orders/${reviewingOrderId}/reject-return`,
            type: 'POST',
            data: { 
                _token: '{{ csrf_token() }}',
                reason: reason
            },
            success: function(response) {
                if (response.success) {
                    fetchOrders();
                    $('#returnReviewModal').modal('hide');
                    window.showAlert(response.message, 'success');
                } else {
                    window.showAlert(response.message, 'danger');
                }
            },
            error: function(xhr) {
                window.showAlert('Error: ' + (xhr.responseJSON?.message || 'Something went wrong'), 'danger');
            },
            complete: function() {
                $btn.prop('disabled', false).html('Confirm Rejection');
            }
        });
    });

    $(document).on('click', '#ordersPagination a', function(e) {
        e.preventDefault();
        const page = new URL(this.href).searchParams.get('page') || 1;
        fetchOrders({ page });
    });

    // Show Order Details
    $tableBody.on('click', '.view-order-btn', function(e) {
        e.preventDefault();
        const $btn = $(this);
        const orderId = $btn.data('order-id');
        
        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span>');

        $.ajax({
            url: `/admin/orders/${orderId}/details`,
            type: 'GET',
            success: function(res) {
                if(res.success) {
                    const o = res.order;
                    $('#mdl_order_id').text('GR-' + String(o.id).padStart(5, '0'));
                    $('#mdl_status').text(o.status);
                    $('#mdl_type').text(o.order_type);
                    $('#mdl_total').text(Number(o.total_amount).toLocaleString());
                    $('#mdl_security').text(Number(o.security_amount).toLocaleString());
                    $('#mdl_placed').text(o.formatted_date);
                    
                    if (o.shipment_error || o.status === 'Order Confirmed & Shipment Failed') {
                        $('#shipmentErrorText').text(o.shipment_error || 'Unknown error occurred during shipment creation.');
                        $('#shipmentErrorAlert').removeClass('d-none');
                        $('#retryShipmentBtn').data('order-id', o.id);
                    } else {
                        $('#shipmentErrorAlert').addClass('d-none');
                    }

                    $('#mdl_rental_period').html(o.rental_period + ' <span class="text-danger fw-bold">(Return Date: ' + o.return_date_formatted + ')</span>');
                    
                    $('#mdl_buyer_name').text(o.buyer_name);
                    $('#mdl_buyer_email').text(o.buyer_email);
                    $('#mdl_buyer_phone').text(o.buyer_phone);
                    
                    let buyerFullAddress = o.delivery_address || 'N/A';
                    if (o.delivery_city || o.delivery_pincode) {
                        buyerFullAddress += '<br>' + (o.delivery_city || '') + ' ' + (o.delivery_state || '') + ' ' + (o.delivery_pincode || '');
                    }
                    $('#mdl_delivery_address').html(buyerFullAddress);
                    
                    let itemsHtml = '';
                    let sellersHtml = '';
                    let uniqueSellers = {};
                    
                    if (o.items_list && o.items_list.length > 0) {
                        o.items_list.forEach(item => {
                            // Collect unique sellers
                            if (item.seller_email && item.seller_email !== 'N/A' && !uniqueSellers[item.seller_email]) {
                                uniqueSellers[item.seller_email] = item;
                                sellersHtml += `
                                    <div class="mb-3 ${Object.keys(uniqueSellers).length > 1 ? 'border-top pt-3' : ''}">
                                        <div class="row mb-1"><div class="col-4 text-muted">Name:</div><div class="col-8 fw-semibold">${item.seller_name}</div></div>
                                        <div class="row mb-1"><div class="col-4 text-muted">Email:</div><div class="col-8">${item.seller_email}</div></div>
                                        <div class="row mb-1"><div class="col-4 text-muted">Phone:</div><div class="col-8">${item.seller_phone}</div></div>
                                        <div class="row mb-1"><div class="col-4 text-muted">Address:</div><div class="col-8 small">${item.seller_address}<br>${item.seller_city} ${item.seller_state} ${item.seller_pincode}</div></div>
                                    </div>
                                `;
                            }

                            // Items Table Rows
                            itemsHtml += `
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <img src="${item.image_url}" style="width:40px; height:40px; object-fit:cover; border-radius:4px;">
                                            <span class="fw-semibold">${item.title}</span>
                                        </div>
                                    </td>
                                    <td><span class="badge bg-light text-dark border">${item.purchase_type}</span></td>
                                    <td>₹${Number(item.price).toLocaleString()}</td>
                                    <td>₹${Number(item.security_deposit).toLocaleString()}</td>
                                </tr>
                            `;
                        });
                    } else {
                        itemsHtml = '<tr><td colspan="4" class="text-center text-muted">No items found</td></tr>';
                    }
                    $('#mdl_items_body').html(itemsHtml);
                    
                    if (!sellersHtml) sellersHtml = '<div class="text-muted small">No seller details found</div>';
                    $('#mdl_seller_info').html(sellersHtml);

                    let paymentsHtml = '';
                    if (o.payments_list && o.payments_list.length > 0) {
                        o.payments_list.forEach(p => {
                            paymentsHtml += `
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <div class="fw-bold">₹${Number(p.amount).toLocaleString()}</div>
                                        <small class="text-muted">${p.method} • ${p.date}</small>
                                    </div>
                                    <span class="badge ${p.status === 'Paid' ? 'bg-success' : 'bg-secondary'}">${p.status}</span>
                                </li>
                            `;
                        });
                    } else {
                        paymentsHtml = '<li class="list-group-item text-center text-muted py-3">No payments recorded</li>';
                    }
                    $('#mdl_payments_list').html(paymentsHtml);

                    let shipmentsHtml = '';
                    if (o.shipments_list && o.shipments_list.length > 0) {
                        o.shipments_list.forEach(s => {
                            shipmentsHtml += `
                                <li class="list-group-item">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <span class="fw-bold">${s.type} Shipment</span>
                                        <span class="badge bg-info text-dark">${s.status}</span>
                                    </div>
                                    <div class="small text-muted mb-1">${s.courier_name} • AWB: ${s.waybill_number}</div>
                                    <div class="small text-muted d-flex justify-content-between">
                                        <span>${s.date}</span>
                                        ${s.tracking_url ? `<a href="${s.tracking_url}" target="_blank" class="text-decoration-none">Track</a>` : ''}
                                    </div>
                                </li>
                            `;
                        });
                    } else {
                        shipmentsHtml = '<li class="list-group-item text-center text-muted py-3">No shipments recorded</li>';
                    }
                    $('#mdl_shipments_list').html(shipmentsHtml);

                    $('#orderDetailsModal').modal('show');
                } else {
                    window.showAlert('Error loading order details', 'danger');
                }
            },
            error: function() {
                window.showAlert('Error loading order details', 'danger');
            },
            complete: function() {
                $btn.prop('disabled', false).html('<i class="bi bi-info-circle"></i>');
            }
        });
    });

    // Retry Shipment
    $('#retryShipmentBtn').on('click', function() {
        const orderId = $(this).data('order-id');
        const $btn = $(this);
        
        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Retrying...');
        
        $.ajax({
            url: `/admin/orders/${orderId}/retry-shipment`,
            type: 'POST',
            data: { _token: '{{ csrf_token() }}' },
            success: function(res) {
                if (res.success) {
                    window.showAlert(res.message || 'Shipment successfully retried!', 'success');
                    $('#orderDetailsModal').modal('hide');
                    fetchOrdersData();
                } else {
                    window.showAlert(res.message || 'Failed to retry shipment', 'danger');
                    $btn.prop('disabled', false).text('Retry Shipment');
                }
            },
            error: function(err) {
                window.showAlert(err.responseJSON?.message || 'Error retrying shipment', 'danger');
                $btn.prop('disabled', false).text('Retry Shipment');
            }
        });
    });
});
</script>
@endpush

