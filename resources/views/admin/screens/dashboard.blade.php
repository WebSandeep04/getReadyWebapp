@extends('admin.layouts.app')

@section('title', 'Admin Dashboard')
@section('page_title', 'Admin Dashboard')

@push('styles')
<style>
*, ::before, ::after { border-radius: 0 !important; }
.approve-btn:disabled,
.reject-btn:disabled { opacity:.4; cursor:not-allowed; }
.table img { border:none; transition:.2s; box-shadow: 0 4px 8px rgba(0,0,0,0.15); }
.table img:hover { transform:scale(1.05); box-shadow: 0 8px 16px rgba(0,0,0,0.25); }
.table th { background:#f8fafc; color: #000; border-bottom:1px solid #ddd; font-weight:700; font-size:.65rem; letter-spacing:normal; text-transform: none; }
.table td { vertical-align:middle; font-size: .65rem; padding: 0.25rem 0.4rem !important; border-bottom: 1px solid #eee; color: #000; }
.badge { font-size:.6rem; padding:.15rem .4rem; font-weight:600; border: none; color: #000; background: #fff; box-shadow: 0 3px 6px rgba(0,0,0,0.1); }
.badge.bg-success { background:#fff; color: #000; border: 1px solid #000; }
.badge.bg-danger { background:#000; color: #fff; border: 1px solid #000; }
.badge.bg-warning { background:#f1f1f1; color: #000; border: 1px solid #000; }
.badge.bg-info { background:#e1e1e1; color: #000; border: 1px solid #000; }
.dashboard-hero { background:#fff; padding:1.25rem; color:#000; margin-bottom:1.5rem; border: none; box-shadow: 0 15px 45px rgba(0,0,0,0.12); }
.dashboard-hero__title { font-size:1.1rem; font-weight:700; color: #000; }
.dashboard-hero__subtitle { margin:0; opacity:0.8; font-size: .8rem; color: #000; }
.stat-card { position:relative; padding:1rem; color:#000; background: #fff; border: none; box-shadow: 0 8px 25px rgba(0,0,0,0.1); overflow:hidden; height: 100%; display: flex; flex-direction: column; justify-content: center; min-height: 100px; transition: all 0.3s ease; }
.stat-card:hover { transform: translateY(-4px); box-shadow: 0 15px 35px rgba(0,0,0,0.15); }
.stat-card__label { text-transform:none; letter-spacing:normal; font-size:.7rem; font-weight: 600; color: #333; }
.stat-card__value { font-size:1.75rem; font-weight:800; margin:.1rem 0; color: #000; }
.stat-card__icon { position:absolute; top:0.75rem; right:0.75rem; font-size:1.5rem; opacity:.08; color: #000; }
.stat-pending { background:#fff; }
.stat-approved { background:#fff; }
.stat-reapproval { background:#fff; }
.stat-rejected { background:#fff; }
.admin-table th,.admin-table td { font-size:.65rem; padding:.35rem .5rem !important; }
.admin-table th,.admin-table td { font-size:.65rem; padding:.35rem .5rem !important; }
.admin-table tr:hover { background-color: #fbfbfb !important; }
.admin-table .btn-sm { padding: 0.25rem 0.5rem; font-size: 0.75rem; }
.card { border: none; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); background: #fff; }
.card-header { padding: 1rem; background: #fff !important; color: #000 !important; border-bottom: 1px solid #eee; }
.card-header h5 { font-size: 0.95rem; color: #000; font-weight: 700; }
.form-select, .form-control { border: 1px solid #eee !important; background: #fff !important; box-shadow: none; color: #000 !important; font-size: 0.75rem; }
.btn { border-radius: 4px; }
.modal-content, .modal-header, .btn-close { border-radius: 0 !important; }
</style>
@endpush

@section('content')
<div class="container mt-4">
    <div class="dashboard-hero d-flex flex-column flex-lg-row justify-content-between align-items-lg-center">
        <div class="mb-3 mb-lg-0">
            <div class="dashboard-hero__title">Welcome back, {{ Auth::user()->name ?? 'Admin' }} 👋</div>
            <p class="dashboard-hero__subtitle">Keep track of submissions, approvals and storefront health at a glance.</p>
        </div>
        <!-- <div class="d-flex gap-4 text-center">
            <div>
                <div class="text-uppercase small text-white-50">Total Listings</div>
                <div class="fs-3 fw-semibold" id="totalCount">0</div>
            </div>
            <div>
                <div class="text-uppercase small text-white-50">Rent Volume</div>
                <div class="fs-3 fw-semibold" id="totalRentSum">₹0</div>
            </div>
            <div>
                <div class="text-uppercase small text-white-50">Purchase Volume</div>
                <div class="fs-3 fw-semibold" id="totalPurchaseSum">₹0</div>
            </div>
            <div>
                <div class="text-uppercase small text-white-50">Security Held</div>
                <div class="fs-3 fw-semibold" id="totalDepositSum">₹0</div>
            </div>
        </div> -->
    </div>

    <div class="row g-3 mb-4">
        <div class="col-lg-3 col-sm-6">
            <div class="stat-card stat-pending">
                <div class="stat-card__icon"><i class="bi bi-hourglass-split"></i></div>
                <div class="stat-card__label">Pending</div>
                <div class="stat-card__value" id="pendingCount">-</div>
                <small>Awaiting review</small>
            </div>
        </div>
        <div class="col-lg-3 col-sm-6">
            <div class="stat-card stat-approved">
                <div class="stat-card__icon"><i class="bi bi-check2-circle"></i></div>
                <div class="stat-card__label">Approved</div>
                <div class="stat-card__value" id="approvedCount">-</div>
                <small>Live on storefront</small>
            </div>
        </div>
        <div class="col-lg-3 col-sm-6">
            <div class="stat-card stat-reapproval">
                <div class="stat-card__icon"><i class="bi bi-arrow-repeat"></i></div>
                <div class="stat-card__label">Re-approval</div>
                <div class="stat-card__value" id="reapprovalCount">-</div>
                <small>Need your feedback</small>
            </div>
        </div>
        <div class="col-lg-3 col-sm-6">
            <div class="stat-card stat-rejected">
                <div class="stat-card__icon"><i class="bi bi-x-circle"></i></div>
                <div class="stat-card__label">Rejected</div>
                <div class="stat-card__value" id="rejectedCount">-</div>
                <small>Require revisions</small>
            </div>
        </div>
    </div>



    <div class="card">
        <div class="card-header bg-primary text-white">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="bi bi-shield-check me-2"></i>Clothes Approval Management
                </h5>
                <div class="d-flex align-items-center">
                    <select id="statusFilter" class="form-select form-select-sm me-2" style="width: 140px; font-size: 0.75rem; border-radius: 0;">
                        <option value="">All Statuses</option>
                        <option value="pending">Pending</option>
                        <option value="approved">Approved</option>
                        <option value="re-approval">Re-approval</option>
                        <option value="rejected">Rejected</option>
                    </select>
                    <button class="btn btn-outline-light btn-sm" onclick="loadClothes()" title="Refresh" style="border-radius: 0; padding: 0.25rem 0.6rem;">
                        <i class="bi bi-arrow-clockwise"></i>
                    </button>
                </div>
            </div>
        </div>
        <div class="card-body">
            <table class="table table-sm table-hover admin-table" id="clothesTable">
                <thead class="table-light">
                    <tr>
                        <th>Title</th>
                        <th>Category</th>
                        <th>Owner</th>
                        <th>User Type</th>
                        <th>Size</th>
                        <th>Condition</th>
                        <th>Base Price (₹)</th>
                        <th>Buyer See (₹)</th>
                        <th>Seller See (₹)</th>
                        <th>Security (₹)</th>
                        <th>Status</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Data loaded by jQuery AJAX -->
                </tbody>
            </table>
        <div class="card-footer bg-white border-top-0 text-center py-2">
            <a href="{{ route('admin.cloth-approval') }}" class="btn btn-sm px-4" 
               style="background: #000; color: #fff; box-shadow: 0 4px 10px rgba(0,0,0,0.15);">
                View All Approvals <i class="bi bi-arrow-right ms-1"></i>
            </a>
        </div>
    </div>
</div>

    <!-- Orders Section -->
    <div class="row g-3 mb-4 mt-5">
        <h5 class="fw-bold mb-0">Order Summary</h5>
        <div class="col-lg-3 col-sm-6">
            <div class="stat-card stat-pending">
                <div class="stat-card__icon"><i class="bi bi-box-seam"></i></div>
                <div class="stat-card__label">Processing</div>
                <div class="stat-card__value" id="processingOrderCount">-</div>
                <small>New orders</small>
            </div>
        </div>
        <div class="col-lg-3 col-sm-6">
            <div class="stat-card stat-approved">
                <div class="stat-card__icon"><i class="bi bi-truck"></i></div>
                <div class="stat-card__label">Shipped</div>
                <div class="stat-card__value" id="shippedOrderCount">-</div>
                <small>Out for delivery</small>
            </div>
        </div>
        <div class="col-lg-3 col-sm-6">
            <div class="stat-card stat-reapproval">
                <div class="stat-card__icon"><i class="bi bi-check-circle"></i></div>
                <div class="stat-card__label">Delivered</div>
                <div class="stat-card__value" id="deliveredOrderCount">-</div>
                <small>Completed orders</small>
            </div>
        </div>
        <div class="col-lg-3 col-sm-6">
            <div class="stat-card stat-rejected">
                <div class="stat-card__icon"><i class="bi bi-arrow-counterclockwise"></i></div>
                <div class="stat-card__label">Returned</div>
                <div class="stat-card__value" id="returnedOrderCount">-</div>
                <small>Items returned</small>
            </div>
        </div>
    </div>

    <div class="card mb-5">
        <div class="card-header bg-dark text-white">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="bi bi-bag-check me-2"></i>Order Management
                </h5>
                <div class="d-flex align-items-center">
                    <select id="orderStatusFilter" class="form-select form-select-sm me-2" style="width: 140px; font-size: 0.75rem; border-radius: 0;">
                        <option value="">All Statuses</option>
                        <option value="Processing">Processing</option>
                        <option value="Shipped">Shipped</option>
                        <option value="Delivered">Delivered</option>
                        <option value="Returned">Returned</option>
                        <option value="Cancelled">Cancelled</option>
                    </select>
                    <button class="btn btn-outline-light btn-sm" onclick="loadOrders()" title="Refresh" style="border-radius: 0; padding: 0.25rem 0.6rem;">
                        <i class="bi bi-arrow-clockwise"></i>
                    </button>
                </div>
            </div>
        </div>
        <div class="card-body">
            <table class="table table-sm table-hover admin-table" id="ordersTable">
                <thead class="table-light">
                    <tr>
                        <th>Order ID</th>
                        <th>Customer</th>
                        <th>Date</th>
                        <th>Items</th>
                        <th>Total (₹)</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Data loaded by jQuery AJAX -->
                </tbody>
            </table>
            <div class="card-footer bg-white border-top-0 text-center py-2">
                <a href="{{ route('admin.orders') }}" class="btn btn-sm px-4" 
                   style="background: #000; color: #fff; box-shadow: 0 4px 10px rgba(0,0,0,0.15);">
                    View All Orders <i class="bi bi-arrow-right ms-1"></i>
                </a>
            </div>
    </div>

    </div>

    <!-- Payments Section -->
    <div class="row g-3 mb-4 mt-5">
        <h5 class="fw-bold mb-0">Payment Summary</h5>
        <div class="col-lg-3 col-sm-6">
            <div class="stat-card stat-approved">
                <div class="stat-card__icon"><i class="bi bi-check-circle-fill"></i></div>
                <div class="stat-card__label">Confirmed</div>
                <div class="stat-card__value" id="paidPaymentCount">-</div>
                <small id="paidPaymentAmount">Total: ₹0</small>
            </div>
        </div>
        <div class="col-lg-3 col-sm-6">
            <div class="stat-card stat-pending">
                <div class="stat-card__icon"><i class="bi bi-clock-history"></i></div>
                <div class="stat-card__label">Pending</div>
                <div class="stat-card__value" id="pendingPaymentCount">-</div>
                <small id="pendingPaymentAmount">Total: ₹0</small>
            </div>
        </div>
        <div class="col-lg-3 col-sm-6">
            <div class="stat-card stat-rejected">
                <div class="stat-card__icon"><i class="bi bi-x-circle-fill"></i></div>
                <div class="stat-card__label">Failed</div>
                <div class="stat-card__value" id="failedPaymentCount">-</div>
                <small id="failedPaymentAmount">Total: ₹0</small>
            </div>
        </div>
        <div class="col-lg-3 col-sm-6">
            <div class="stat-card stat-reapproval">
                <div class="stat-card__icon"><i class="bi bi-arrow-counterclockwise"></i></div>
                <div class="stat-card__label">Refund</div>
                <div class="stat-card__value" id="refundPaymentCount">-</div>
                <small id="refundPaymentAmount">Total: ₹0</small>
            </div>
        </div>
    </div>



    <div class="card mb-5">
        <div class="card-header bg-white">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold">
                    <i class="bi bi-credit-card me-2"></i>Payment Management
                </h5>
                <div class="d-flex align-items-center">
                    <select id="paymentStatusFilter" class="form-select form-select-sm me-2" style="width: 140px; font-size: 0.75rem; border-radius: 0;">
                        <option value="">All Statuses</option>
                        <option value="Paid">Paid</option>
                        <option value="Pending">Pending</option>
                        <option value="Refunded">Refunded</option>
                        <option value="Partially Refunded">Partially Refunded</option>
                        <option value="Failed">Failed</option>
                    </select>
                    <button class="btn btn-outline-light btn-sm text-dark border-0" onclick="loadPayments()" title="Refresh" style="border-radius: 0; padding: 0.25rem 0.6rem;">
                        <i class="bi bi-arrow-clockwise"></i>
                    </button>
                </div>
            </div>
        </div>
        <div class="card-body">
            <table class="table table-sm table-hover admin-table" id="paymentsTable">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Order</th>
                        <th>Payer</th>
                        <th>Total (₹)</th>
                        <th>Base Amt</th>
                        <th>Buyer Comm</th>
                        <th>Seller Comm</th>
                        <th>Item Tax/GST</th>
                        <th>Buyer GST</th>
                        <th>Seller GST</th>
                        <th>Total GST</th>
                        <th>Security</th>
                        <th>Seller Net</th>
                        <th>Method</th>
                        <th>Date</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Data loaded by jQuery AJAX -->
                </tbody>
            </table>
            <div class="card-footer bg-white border-top-0 text-center py-2">
                <a href="{{ route('admin.payments') }}" class="btn btn-sm px-4" 
                   style="background: #000; color: #fff; box-shadow: 0 4px 10px rgba(0,0,0,0.15);">
                    View All Payments <i class="bi bi-arrow-right ms-1"></i>
                </a>
            </div>
        </div>
    </div>

    <!-- Security Deposit Section -->
    <div class="row g-3 mb-4">
        <div class="col-lg-4 col-sm-6">
            <div class="stat-card stat-pending">
                <div class="stat-card__icon"><i class="bi bi-shield-lock-fill"></i></div>
                <div class="stat-card__label">Total Security Held</div>
                <div class="stat-card__value" id="totalSecurityHeld">₹0</div>
                <small>Currently with platform</small>
            </div>
        </div>
        <div class="col-lg-4 col-sm-6">
            <div class="stat-card stat-approved">
                <div class="stat-card__icon"><i class="bi bi-arrow-return-left"></i></div>
                <div class="stat-card__label">Returned Security</div>
                <div class="stat-card__value" id="returnedSecurity">₹0</div>
                <small>Refunded to users</small>
            </div>
        </div>
        <div class="col-lg-4 col-sm-6">
            <div class="stat-card stat-rejected">
                <div class="stat-card__icon"><i class="bi bi-exclamation-circle-fill"></i></div>
                <div class="stat-card__label">Need to Return</div>
                <div class="stat-card__value" id="needToReturnSecurity">₹0</div>
                <small>Pending refunds</small>
            </div>
        </div>
    </div>

    <!-- Security Deposit Table -->
    <div class="card mb-5">
        <div class="card-header bg-white">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold">
                    <i class="bi bi-shield-lock me-2"></i>Security Deposits
                </h5>
                <div class="d-flex align-items-center">
                    <select id="securityStatusFilter" class="form-select form-select-sm me-2" style="width: 140px; font-size: 0.75rem; border-radius: 0;">
                        <option value="">All Statuses</option>
                        <option value="held">Held</option>
                        <option value="returned">Pending Return</option>
                        <option value="completed">Returned</option>
                    </select>
                    <button class="btn btn-outline-light btn-sm text-dark border-0" onclick="loadSecurityDeposits()" title="Refresh" style="border-radius: 0; padding: 0.25rem 0.6rem;">
                        <i class="bi bi-arrow-clockwise"></i>
                    </button>
                </div>
            </div>
        </div>
        <div class="card-body">
            <table class="table table-sm table-hover admin-table" id="securityTable">
                <thead class="table-light">
                    <tr>
                        <th>Order ID</th>
                        <th>Customer</th>
                        <th>Amount (₹)</th>
                        <th>Created At</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Data loaded by jQuery AJAX -->
                </tbody>
            </table>
            <div class="card-footer bg-white border-top-0 text-center py-2">
                <a href="{{ route('admin.security') }}" class="btn btn-sm px-4" 
                   style="background: #000; color: #fff; box-shadow: 0 4px 10px rgba(0,0,0,0.15);">
                    View All Security Deposits <i class="bi bi-arrow-right ms-1"></i>
                </a>
            </div>
        </div>
    </div>

    <!-- Seller Payouts Section -->
    <div class="row g-3 mb-4">
        <div class="col-lg-4 col-sm-6">
            <div class="stat-card stat-pending">
                <div class="stat-card__icon"><i class="bi bi-cash-stack"></i></div>
                <div class="stat-card__label">Total Seller Amount Held</div>
                <div class="stat-card__value" id="totalSellerNetVal">₹0</div>
                <small>Gross earnings (Unpaid)</small>
            </div>
        </div>
        <div class="col-lg-4 col-sm-6">
            <div class="stat-card stat-approved">
                <div class="stat-card__icon"><i class="bi bi-check2-circle"></i></div>
                <div class="stat-card__label">Paid to Sellers</div>
                <div class="stat-card__value" id="paidToSellersVal">₹0</div>
                <small>Transferred to accounts</small>
            </div>
        </div>
        <div class="col-lg-4 col-sm-6">
            <div class="stat-card stat-rejected">
                <div class="stat-card__icon"><i class="bi bi-hourglass-split"></i></div>
                <div class="stat-card__label">Need to Pay</div>
                <div class="stat-card__value" id="needToPaySellerVal">₹0</div>
                <small>Returned & Ready for Payout</small>
            </div>
        </div>
    </div>

    <!-- Seller Payouts Table -->
    <div class="card mb-5">
        <div class="card-header bg-white">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold">
                    <i class="bi bi-wallet2 me-2"></i>Seller Payouts
                </h5>
                <div class="d-flex align-items-center">
                    <select id="payoutStatusFilter" class="form-select form-select-sm me-2" style="width: 140px; font-size: 0.75rem; border-radius: 0;">
                        <option value="">All Statuses</option>
                        <option value="processing">In Progress Order</option>
                        <option value="pending">Eligible for Payout</option>
                        <option value="completed">Paid to Seller</option>
                    </select>
                    <button class="btn btn-outline-light btn-sm text-dark border-0" onclick="loadSellerPayouts()" title="Refresh" style="border-radius: 0; padding: 0.25rem 0.6rem;">
                        <i class="bi bi-arrow-clockwise"></i>
                    </button>
                </div>
            </div>
        </div>
        <div class="card-body">
            <table class="table table-sm table-hover admin-table" id="payoutsTable">
                <thead class="table-light">
                    <tr>
                        <th>Order ID</th>
                        <th>Seller Name</th>
                        <th>Net Amount (₹)</th>
                        <th>Created At</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Data loaded by jQuery AJAX -->
                </tbody>
            </table>
            <div class="card-footer bg-white border-top-0 text-center py-2">
                <a href="{{ route('admin.payouts') }}" class="btn btn-sm px-4" 
                   style="background: #000; color: #fff; box-shadow: 0 4px 10px rgba(0,0,0,0.15);">
                    View All Payouts <i class="bi bi-arrow-right ms-1"></i>
                </a>
            </div>
        </div>
    </div>

    <!-- Action Confirmation Modal -->
    <div class="modal fade" id="dashboardActionConfirmModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header border-bottom-0 pb-0">
                    <h5 class="modal-title fw-bold" id="dashboardActionConfirmTitle">Confirm Action</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body pt-2">
                    <p class="text-muted mb-0" id="dashboardActionConfirmMsg">Are you sure you want to proceed?</p>
                </div>
                <div class="modal-footer border-top-0 pt-0">
                    <button type="button" class="btn btn-link text-decoration-none text-muted" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-dark px-4" id="dashboardConfirmActionBtn">
                        Confirm
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Return Review Modal (Same as Orders Page) -->
    <div class="modal fade" id="returnReviewModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 0 !important;">
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
                        <textarea id="rejectionReasonText" class="form-control mb-2" rows="2" placeholder="Tell the buyer why the request was rejected..." style="border-radius: 0;"></textarea>
                        <button class="btn btn-sm btn-dark" id="submitRejectBtn" style="border-radius: 0;">Confirm Rejection</button>
                        <button class="btn btn-sm btn-link text-muted" onclick="$('#rejectionSection').hide();">Cancel</button>
                    </div>
                </div>
                <div class="modal-footer border-top-0">
                    <button type="button" class="btn btn-link text-decoration-none text-muted" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-outline-danger btn-sm" id="rejectReturnBtn" style="border-radius: 0;">Reject Request</button>
                    <button type="button" class="btn btn-success btn-sm" id="approveReturnBtn" style="border-radius: 0;">Approve & Generate AWB</button>
                </div>
            </div>
        </div>
    </div>

<!-- Rejection Reason Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1" aria-labelledby="rejectModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="rejectModalLabel">
                    <i class="bi bi-exclamation-triangle me-2"></i>Reject Item
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="rejectForm">
                    <div class="mb-3">
                        <label for="rejectReason" class="form-label fw-bold">Rejection Reason *</label>
                        <textarea class="form-control" id="rejectReason" name="reject_reason" rows="4" required 
                                  placeholder="Please provide a detailed reason for rejection..."></textarea>
                        <div class="form-text">This reason will be sent to the item owner.</div>
                    </div>
                    <input type="hidden" id="rejectClothId" name="cloth_id">
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle me-1"></i>Cancel
                </button>
                <button type="button" class="btn btn-danger" id="confirmReject">
                    <i class="bi bi-check-circle me-1"></i>Reject Item
                </button>
            </div>
        </div>
    </div>
</div>
<!-- View Reason Modal -->
<div class="modal fade" id="reasonModal" tabindex="-1" aria-labelledby="reasonModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="reasonModalLabel"><i class="bi bi-eye me-2"></i>Rejection Reason</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="reasonList"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
    
</div>
@endsection

@push('scripts')
<script>
    const defaultImageUrl = "{{ asset('images/1.jpg') }}";
$(function() {
    function loadClothes() {
        let status = $('#statusFilter').val();
        let url = "{{ route('clothes.fetch') }}";
        
        if (status) {
            url += `?status=${status}&limit=5`;
        } else {
            url += `?limit=5`;
        }
        
        $.get(url, function(response) {
            let rows = '';
            const clothes = response.clothes || [];
            const stats = response.stats || {};
            
            // Update statistics from backend
            if (stats) {
                $('#totalCount').text(stats.total || '0');
                $('#approvedCount').text(stats.approved || '0');
                $('#pendingCount').text(stats.pending || '0');
                $('#reapprovalCount').text(stats.reapproval || '0');
                $('#rejectedCount').text(stats.rejected || '0');
                $('#totalRentSum').text(`₹${Math.round(stats.total_rent || 0).toLocaleString('en-IN')}`);
                $('#totalPurchaseSum').text(`₹${Math.round(stats.total_purchase || 0).toLocaleString('en-IN')}`);
                $('#totalDepositSum').text(`₹${Math.round(stats.total_security || 0).toLocaleString('en-IN')}`);
            }

            if (clothes.length === 0) {
                $('#clothesTable tbody').html('<tr><td colspan="11" class="text-center">No clothes found</td></tr>');
                return;
            }
            
            clothes.forEach(function(cloth) {
                // Determine status badge and button states
                let statusBadge = '';
                let approveDisabled = false;
                let rejectDisabled = false;
                
                if (cloth.is_approved === 1 || cloth.is_approved === true) {
                    statusBadge = '<span class="badge bg-success">Approved</span>';
                    approveDisabled = true;
                    rejectDisabled = true;
                } else if (cloth.is_approved === -1) {
                    statusBadge = '<span class="badge bg-danger">Rejected</span>';
                    approveDisabled = false;
                    rejectDisabled = false;
                } else if (cloth.is_approved === null) {
                    if (cloth.resubmission_count > 0) {
                        statusBadge = '<span class="badge bg-info">Re-approval</span>';
                    } else {
                        statusBadge = '<span class="badge bg-warning text-dark">Pending</span>';
                    }
                    approveDisabled = false;
                    rejectDisabled = false;
                }
                
                // Accessor for Display Prices
                const rentPrice = cloth.rent_price ? `<div><small class="text-muted">Rent:</small> ₹${cloth.rent_price}</div>` : '';
                const buyPrice = cloth.base_selling_price ? `<div><small class="text-primary">Buy:</small> ₹${cloth.base_selling_price}</div>` : '';
                
                const buyerRent = cloth.buyer_see_rent ? `<div><small class="text-muted">Rent:</small> ₹${cloth.buyer_see_rent}</div>` : '';
                const buyerBuy = cloth.display_selling_price ? `<div><small class="text-primary">Buy:</small> ₹${cloth.display_selling_price}</div>` : '';
                
                const sellerRent = cloth.seller_see_rent ? `<div><small class="text-muted">Rent:</small> ₹${cloth.seller_see_rent}</div>` : '';
                const sellerBuy = cloth.seller_selling_price ? `<div><small class="text-primary">Buy:</small> ₹${cloth.seller_selling_price}</div>` : '';

                rows += `<tr>
                    <td>${cloth.title}</td>
                    <td>${cloth.category}</td>
                    <td>${cloth.user_name}</td>
                    <td>${cloth.gender}</td>
                    <td>${cloth.size}</td>
                    <td>${cloth.condition}</td>
                    <td>${rentPrice}${buyPrice}</td>
                    <td class="fw-bold">${buyerRent}${buyerBuy}</td>
                    <td class="fw-bold">${sellerRent}${sellerBuy}</td>
                    <td>₹${cloth.security_deposit}</td>
                    <td>${statusBadge}</td>
                    <td class="text-center">
                        <div class="btn-group btn-group-sm" role="group">
                            <button class="btn btn-outline-success approve-btn" data-id="${cloth.id}" ${approveDisabled ? 'disabled' : ''} title="${approveDisabled ? 'Approved' : 'Approve'}">
                                <i class="bi bi-check-lg"></i>
                            </button>
                            <button class="btn btn-outline-danger reject-btn" data-id="${cloth.id}" ${rejectDisabled ? 'disabled' : ''} title="${rejectDisabled ? 'Rejected' : 'Reject'}">
                                <i class="bi bi-x-lg"></i>
                            </button>
                            <button class="btn btn-outline-secondary view-reason-btn" data-id="${cloth.id}" title="View Reason">
                                <i class="bi bi-eye"></i>
                            </button>   
                        </div>
                    </td>
                </tr>`;
            });
            
            $('#clothesTable tbody').html(rows);

        }).fail(function(xhr, status, error) {
            $('#clothesTable tbody').html('<tr><td colspan="12" class="text-center text-danger">Error loading clothes data</td></tr>');
        });
    }
    loadClothes();

    // Status filter change handler
    $('#statusFilter').on('change', function() {
        loadClothes();
    });

    // Approve
    $(document).on('click', '.approve-btn', function() {
        let id = $(this).data('id');
        let $btn = $(this);
        $btn.prop('disabled', true).html('<i class="bi bi-hourglass-split me-1"></i>Processing...');
        
        $.post(`{{ url('/admin/clothes/approve') }}/${id}`, {_token: '{{ csrf_token() }}'}, function(res) {
            if (res.success) {
                loadClothes();
                // Show success message
                showAlert('Item approved successfully!', 'success');
            }
        }).fail(function() {
            $btn.prop('disabled', false).html('<i class="bi bi-check-circle me-1"></i>Approve');
            showAlert('Failed to approve item. Please try again.', 'danger');
        });
    });

    // Reject - Show modal
    $(document).on('click', '.reject-btn', function() {
        let id = $(this).data('id');
        let $btn = $(this);
        
        // Check if the item is already approved
        let $row = $btn.closest('tr');
        let statusCell = $row.find('td:eq(10)').text().trim(); // Status is in the 11th column (index 10)
        
        if (statusCell === 'Approved') {
            showAlert('Cannot reject an approved item. Please approve it first.', 'warning');
            return;
        }
        
        // Allow rejecting pending, rejected, and re-approval items
        $('#rejectClothId').val(id);
        $('#rejectReason').val('');
        $('#rejectModal').modal('show');
    });

    // Confirm reject
    $('#confirmReject').click(function() {
        let clothId = $('#rejectClothId').val();
        let reason = $('#rejectReason').val();
        
        if (!reason.trim()) {
            $('#rejectReason').addClass('is-invalid');
            return;
        }
        
        $('#rejectReason').removeClass('is-invalid');
        let $btn = $(this);
        $btn.prop('disabled', true).html('<i class="bi bi-hourglass-split me-1"></i>Processing...');

        $.post(`{{ url('/admin/clothes/reject') }}/${clothId}`, {
            _token: '{{ csrf_token() }}',
            reject_reason: reason
        }, function(res) {
            if (res.success) {
                $('#rejectModal').modal('hide');
                loadClothes();
                showAlert('Item rejected successfully!', 'success');
            }
        }).fail(function() {
            $btn.prop('disabled', false).html('<i class="bi bi-check-circle me-1"></i>Reject Item');
            showAlert('Failed to reject item. Please try again.', 'danger');
        });
    });

    // View rejection reason
    $(document).on('click', '.view-reason-btn', function() {
        const id = $(this).data('id');
        $('#reasonList').html('<p>Loading...</p>');
        $('#reasonModal').modal('show');
        $.get(`{{ url('/admin/clothes/reject-reason') }}/${id}`, function(res) {
            if (res.success && res.reasons && res.reasons.length) {
                let html = '<ul class="list-group">';
                res.reasons.forEach(function(r) {
                    html += `<li class="list-group-item">
                        <div class="fw-semibold">${r.reason || 'No reason provided.'}</div>
                        <small class="text-muted">Rejected on ${r.rejected_at}</small>
                    </li>`;
                });
                html += '</ul>';
                $('#reasonList').html(html);
            } else {
                $('#reasonList').html('<p>No rejection reasons found.</p>');
            }
        }).fail(function() {
            $('#reasonList').html('<p>Failed to load rejection reasons.</p>');
        });
    });

    // Remove validation class when user starts typing
    $('#rejectReason').on('input', function() {
        $(this).removeClass('is-invalid');
    });



    // Orders Logic
    function loadOrders() {
        let status = $('#orderStatusFilter').val();
        let url = "{{ route('admin.dashboard.orders.fetch') }}";
        if (status) {
            url += `?status=${status}`;
        }
        
        $.get(url, function(response) {
            let rows = '';
            const orders = response.orders || [];
            const stats = response.stats || {};

            // Update Stats from backend
            if (stats) {
                $('#processingOrderCount').text(stats.processing || '0');
                $('#shippedOrderCount').text(stats.shipped || '0');
                $('#deliveredOrderCount').text(stats.delivered || '0');
                $('#returnedOrderCount').text(stats.returned || '0');
            }
            
            if (orders.length === 0) {
                $('#ordersTable tbody').html('<tr><td colspan="7" class="text-center">No orders found</td></tr>');
                return;
            }
            
            orders.forEach(function(order) {
                const statusLower = (order.status || '').toLowerCase();
                
                let statusBadge = '';
                if (order.status === 'Returned') statusBadge = '<span class="badge bg-success">Returned</span>';
                else if (order.status === 'Cancelled') statusBadge = '<span class="badge bg-secondary">Cancelled</span>';
                else if (order.status === 'Shipped') statusBadge = '<span class="badge bg-info">Shipped</span>';
                else if (order.status === 'Delivered') statusBadge = '<span class="badge bg-success">Delivered</span>';
                else statusBadge = '<span class="badge bg-warning text-dark">' + (order.status || 'Processing') + '</span>';

                let typeLabel = '';
                if (order.is_rental && order.is_purchase) typeLabel = '<span class="badge bg-dark fw-normal" style="font-size:0.6rem;">Mixed</span>';
                else if (order.is_rental) typeLabel = '<span class="badge bg-info text-dark fw-normal" style="font-size:0.6rem;">Rental</span>';
                else if (order.is_purchase) typeLabel = '<span class="badge bg-primary fw-normal" style="font-size:0.6rem;">Purchase</span>';

                if (order.shipment_missing) {
                    statusBadge += ' <i class="bi bi-exclamation-triangle-fill text-danger ms-1" title="Shipment missing"></i>';
                }
                // Action buttons building (Mirroring components.orders-rows)
                let actionBtn = `<div class="d-flex gap-1 justify-content-end">`;
                
                // 1. Move to Next Status
                if (['Pending', 'Confirmed', 'Order Confirmed & Shipment Created'].includes(order.status)) {
                    let nextStatus = (order.status === 'Pending') ? 'Confirmed' : 'Delivered';
                    let nextIcon = (order.status === 'Pending') ? 'bi-check-circle' : 'bi-truck';
                    actionBtn += `<button class="btn btn-sm btn-outline-success update-status-btn" data-order-id="${order.id}" data-status="${nextStatus}" title="Move to ${nextStatus}" style="padding: 0.15rem 0.4rem; font-size: 0.7rem;"><i class="bi ${nextIcon}"></i></button>`;
                }

                // 2. Mark as Returned
                if (order.is_rental && !['Returned', 'Cancelled', 'Return Requested'].includes(order.status) && ['Delivered', 'Return In Progress', 'Order Confirmed & Shipment Created', 'Confirmed', 'Shipped'].includes(order.status)) {
                    actionBtn += `<button class="btn btn-sm btn-outline-primary mark-returned-btn" data-order-id="${order.id}" title="Mark as Returned" style="padding: 0.15rem 0.4rem; font-size: 0.7rem;"><i class="bi bi-box-arrow-in-left"></i></button>`;
                }

                // 3. Retry Shipment
                if (order.shipment_missing) {
                     actionBtn += `<button class="btn btn-sm btn-outline-warning retry-shipment-btn" data-order-id="${order.id}" title="Retry Shipment" style="padding: 0.15rem 0.4rem; font-size: 0.7rem;"><i class="bi bi-arrow-clockwise"></i></button>`;
                }

                // 4. Review Return Request
                if (order.status === 'Return Requested') {
                    actionBtn += `<button class="btn btn-sm btn-danger review-return-btn" data-order-id="${order.id}" data-reason="${order.return_reason || ''}" data-details="${order.return_details || ''}" data-images='${JSON.stringify(order.return_images || [])}' title="Review Return" style="padding: 0.15rem 0.4rem; font-size: 0.7rem;"><i class="bi bi-eye"></i></button>`;
                }

                // 5. Process Issue Refund (Full Refund)
                if (order.status === 'Returned' && order.return_reason && !order.is_security_returned) {
                    actionBtn += `<button class="btn btn-sm btn-dark process-issue-refund-btn" data-order-id="${order.id}" title="Refund All" style="padding: 0.15rem 0.4rem; font-size: 0.7rem;"><i class="bi bi-wallet2 me-1"></i>Refund All</button>`;
                }

                actionBtn += `</div>`;

                rows += `<tr>
                    <td>#${order.id}</td>
                    <td>${order.user_name}</td>
                    <td>${order.created_at_formatted}</td>
                    <td>${order.items_count} Item(s) ${typeLabel}</td>
                    <td>₹${order.total_amount}</td>
                    <td>${statusBadge}</td>
                    <td class="text-end">${actionBtn}</td>
                </tr>`;
            });
            
            $('#ordersTable tbody').html(rows);

        }).fail(function(xhr, status, error) {
            console.error("Error loading orders:", error);
            $('#ordersTable tbody').html('<tr><td colspan="7" class="text-center text-danger">Error loading orders data</td></tr>');
        });
    }

    // Expose to global scope for onclick attributes
    window.loadOrders = loadOrders;
    window.loadSecurityDeposits = loadSecurityDeposits;
    
    function loadSecurityDeposits() {
        let status = $('#securityStatusFilter').val();
        let tbody = $('#securityTable tbody');
        
        tbody.html('<tr><td colspan="6" class="text-center py-3"><div class="spinner-border spinner-border-sm text-primary" role="status"></div> Loading...</td></tr>');

        $.ajax({
            url: "{{ route('admin.dashboard.security.fetch') }}", 
            method: 'GET',
            data: { 
                status: status,
                limit: 5
            }, 
            success: function(response) {
                tbody.empty();
                
                // Handle new response structure { orders: [], stats: {} }
                const orders = response.orders || [];
                const stats = response.stats || {};

                // Update Stats
                if (stats) {
                    $('#totalSecurityHeld').text('₹' + Number(stats.total_held || 0).toLocaleString('en-IN'));
                    $('#needToReturnSecurity').text('₹' + Number(stats.need_to_return || 0).toLocaleString('en-IN'));
                    $('#returnedSecurity').text('₹' + Number(stats.returned || 0).toLocaleString('en-IN'));
                }

                if (orders.length === 0) {
                    tbody.html('<tr><td colspan="6" class="text-center py-3 text-muted">No security deposits found.</td></tr>');
                    return;
                }

                orders.forEach(function(item) {
                    let statusBadge = '';
                    if (item.is_security_returned) {
                         statusBadge = '<span class="badge bg-success">Refunded</span>';
                    } else if (item.status === 'Returned') {
                         statusBadge = '<span class="badge bg-warning text-dark">Pending Return</span>';
                    } else {
                         statusBadge = '<span class="badge bg-warning text-dark">Held</span>';
                    }

                    let actionBtn = '';
                    if (!item.is_security_returned) {
                        if (item.status === 'Returned') {
                            actionBtn = `<button class="btn btn-dark btn-sm btn-action mark-returned-btn" data-id="${item.id}" style="font-size: 0.75rem; padding: 0.2rem 0.5rem;">Mark Returned</button>`;
                        } else {
                            actionBtn = `<button class="btn btn-outline-secondary btn-sm btn-action" disabled style="font-size: 0.75rem; padding: 0.2rem 0.5rem;">Wait for Return</button>`;
                        }
                    } else {
                        actionBtn = '<span class="text-success small fw-bold"><i class="bi bi-check-all me-1"></i>Completed</span>';
                    }

                    let row = `
                        <tr>
                            <td><a href="{{ route('admin.orders') }}?search=${item.id}" class="text-decoration-none fw-bold text-dark">#${item.id}</a></td>
                            <td>${item.buyer_name}</td>
                            <td class="fw-bold">₹${Number(item.amount).toLocaleString()}</td>
                            <td>${item.created_at}</td>
                            <td>${statusBadge}</td>
                            <td class="text-end">${actionBtn}</td>
                        </tr>
                    `;
                    tbody.append(row);
                });
            },
            error: function() {
                tbody.html('<tr><td colspan="7" class="text-center py-3 text-danger">Failed to load data.</td></tr>');
            }
        });
    }

    // --- ORDERS ACTION HANDLERS (Same as Orders Page) ---

    // 1. Update Status
    $(document).on('click', '#ordersTable .update-status-btn', function() {
        const orderId = $(this).data('order-id');
        const newStatus = $(this).data('status');
        
        showDashboardConfirmModal(
            'Update Status',
            `Are you sure you want to change order status to ${newStatus}?`,
            () => {
                return new Promise((resolve) => {
                    $.post(`/admin/orders/${orderId}/status`, {
                        _token: '{{ csrf_token() }}',
                        status: newStatus
                    }, function(res) {
                        if(res.success) {
                            loadOrders();
                            window.showAlert(res.message || 'Status updated successfully.', 'success');
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

    // 2. Mark as Returned (Order Management Table)
    $(document).on('click', '#ordersTable .mark-returned-btn', function() {
        const orderId = $(this).data('order-id');
        
        showDashboardConfirmModal(
            'Confirm Return',
            'Are you sure you want to mark this order as Returned? This will increment stock for rented items.',
            () => {
                return new Promise((resolve) => {
                    $.post(`/admin/orders/${orderId}/return`, {
                        _token: '{{ csrf_token() }}'
                    }, function(res) {
                        if(res.success) {
                            loadOrders();
                            loadSecurityDeposits(); // Security might change
                             window.showAlert(res.message || 'Order marked as returned.', 'success');
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

    // 3. Retry Shipment
    $(document).on('click', '#ordersTable .retry-shipment-btn', function() {
        const orderId = $(this).data('order-id');
        
        showDashboardConfirmModal(
            'Retry Shipment',
            'Attempt to create shipment for this order again?',
            () => {
                return new Promise((resolve) => {
                    $.post(`/admin/orders/${orderId}/retry-shipment`, {
                        _token: '{{ csrf_token() }}'
                    }, function(res) {
                        if(res.success) {
                            loadOrders();
                            window.showAlert(res.message || 'Shipment retry initiated.', 'success');
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

    // 4. Process Issue Refund (Full Refund)
    $(document).on('click', '#ordersTable .process-issue-refund-btn', function() {
        const orderId = $(this).data('order-id');
        
        showDashboardConfirmModal(
            'Process Full Refund',
            `This item was returned due to an issue. Are you sure you want to process a FULL REFUND (Rent + Security) for Order #${orderId}?`,
            () => {
                return new Promise((resolve) => {
                    $.post(`/admin/orders/${orderId}/process-issue-refund`, {
                        _token: '{{ csrf_token() }}'
                    }, function(res) {
                        if(res.success) {
                            loadOrders();
                            loadSecurityDeposits();
                            window.showAlert(res.message || 'Full refund processed.', 'success');
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

    // 5. Review Return Logic
    let reviewingOrderId = null;

    $(document).on('click', '#ordersTable .review-return-btn', function() {
        const $btn = $(this);
        reviewingOrderId = $btn.data('order-id');
        $('#rr_reason').text($btn.data('reason'));
        $('#rr_details').text($btn.data('details'));
        
        let imagesHtml = '';
        const images = $btn.data('images'); // Already parsed by JSON.stringify/parse via jQuery data()? 
                                          // Actually data-images is a stringified array. 
                                          // jQuery .data() automatically parses JSON if it looks like it.
        
        let imagesArray = images;
        if (typeof images === 'string') {
            try { imagesArray = JSON.parse(images); } catch(e) { imagesArray = []; }
        }

        if (imagesArray && imagesArray.length > 0) {
            imagesArray.forEach(path => {
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
                    loadOrders();
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
                window.showAlert('Error: ' + (xhr.responseJSON?.message || 'Something went wrong'), 'danger');
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
                    loadOrders();
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

    // Security & General Actions handling
    let dashboardPendingAction = null;

    function showDashboardConfirmModal(title, message, action) {
        $('#dashboardActionConfirmTitle').text(title);
        $('#dashboardActionConfirmMsg').text(message);
        dashboardPendingAction = action;
        $('#dashboardActionConfirmModal').modal('show');
    }

    $('#dashboardConfirmActionBtn').on('click', function() {
        if (dashboardPendingAction) {
            const $btn = $(this);
            const originalHtml = $btn.html();
            $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status"></span>');
            
            Promise.resolve(dashboardPendingAction()).finally(() => {
                $('#dashboardActionConfirmModal').modal('hide');
                $btn.prop('disabled', false).html(originalHtml);
                dashboardPendingAction = null;
            });
        }
    });

    $(document).on('click', '#securityTable .mark-returned-btn', function() {
        const id = $(this).data('id');
        
        showDashboardConfirmModal(
            'Confirm Return',
            'Are you sure you want to mark this security deposit as Returned? This will record the refund date and update statistics.',
            () => {
                return new Promise((resolve) => {
                    $.post(`{{ url('/admin/security/mark-returned') }}/${id}`, {
                        _token: '{{ csrf_token() }}'
                    }, function(res) {
                        if(res.success) {
                            loadSecurityDeposits(); // Refresh table
                            loadPayments(); 
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

    // Payments Logic
    window.loadPayments = loadPayments;

    function loadPayments() {
        let status = $('#paymentStatusFilter').val();
        let url = "{{ route('admin.dashboard.payments.fetch') }}";
        if (status) {
            url += `?status=${status}`;
        }
        
        $.get(url, function(response) {
            let rows = '';
            const payments = response.payments || [];
            const stats = response.stats || {};
            
            // Update Stats from backend
            if (stats) {
                $('#paidPaymentCount').text(stats.paid_count || '0');
                $('#paidPaymentAmount').text(`Total: ₹${Math.round(stats.paid_amount || 0).toLocaleString('en-IN')}`);

                $('#pendingPaymentCount').text(stats.pending_count || '0');
                $('#pendingPaymentAmount').text(`Total: ₹${Math.round(stats.pending_amount || 0).toLocaleString('en-IN')}`);

                $('#failedPaymentCount').text(stats.failed_count || '0');
                $('#failedPaymentAmount').text(`Total: ₹${Math.round(stats.failed_amount || 0).toLocaleString('en-IN')}`);

                $('#refundPaymentCount').text(stats.refund_count || '0');
                $('#refundPaymentAmount').text(`Total: ₹${Math.round(stats.refund_amount || 0).toLocaleString('en-IN')}`);
            }

            if (payments.length === 0) {
                $('#paymentsTable tbody').html('<tr><td colspan="16" class="text-center">No payments found</td></tr>');
                return;
            }
            
            payments.forEach(function(payment) {
                const statusLower = (payment.payment_status || '').toLowerCase();
                const amount = parseFloat(payment.amount || 0);
                
                let statusBadge = '';
                if (statusLower === 'paid' || statusLower === 'success') statusBadge = '<span class="badge bg-success">Paid</span>';
                else if (statusLower === 'pending') statusBadge = '<span class="badge bg-warning text-dark">Pending</span>';
                else if (statusLower === 'refunded') statusBadge = '<span class="badge bg-info text-dark">Refunded</span>';
                else if (statusLower === 'partially refunded') statusBadge = '<span class="badge bg-info text-dark">Partially Refunded</span>';
                else statusBadge = '<span class="badge bg-danger">Failed</span>';

                rows += `<tr>
                    <td>#${payment.id}</td>
                    <td><a href="{{ route('admin.orders') }}?search=${payment.order_id}" target="_blank">#${payment.order_id}</a></td>
                    <td><small>${payment.payer_name}</small></td>
                    <td class="fw-bold">₹${amount}</td>
                    <td>₹${payment.base_rent_total}</td>
                    <td class="text-primary">₹${payment.buyer_comm_total}</td>
                    <td class="text-purple" style="color: #6f42c1;">₹${payment.seller_comm_total}</td>
                    <td class="text-secondary small">₹${payment.rent_gst_total}</td>
                    <td class="text-secondary small">₹${payment.buyer_comm_gst_total}</td>
                    <td class="text-secondary small">₹${payment.seller_comm_gst_total}</td>
                    <td class="text-dark fw-bold small">₹${payment.gst_total}</td>
                    <td class="text-info fw-bold small">₹${payment.security_amount}</td>
                    <td class="text-success fw-bold">₹${payment.seller_net_payout}</td>
                    <td><small>${payment.payment_method || '-'}</small></td>
                    <td><small>${payment.paid_at_formatted}</small></td>
                    <td>${statusBadge}</td>
                </tr>`;
            });
            
            $('#paymentsTable tbody').html(rows);

        }).fail(function(xhr, status, error) {
            console.error("Error loading payments:", error);
            $('#paymentsTable tbody').html('<tr><td colspan="16" class="text-center text-danger">Error loading payments data</td></tr>');
        });
    }

    // Seller Payouts Logic
    window.loadSellerPayouts = function() {
        let status = $('#payoutStatusFilter').val();
        let url = "{{ route('admin.dashboard.payouts.fetch') }}";
        let params = { limit: 5 };
        if (status) params.status = status;
        
        $.get(url, params, function(response) {
            let rows = '';
            const orders = response.orders || [];
            const stats = response.stats || {};
            
            // Update Stats
            if (stats) {
                $('#totalSellerNetVal').text(`₹${Math.round(stats.total_held || 0).toLocaleString('en-IN')}`);
                $('#needToPaySellerVal').text(`₹${Math.round(stats.need_to_pay || 0).toLocaleString('en-IN')}`);
                $('#paidToSellersVal').text(`₹${Math.round(stats.paid_to_sellers || 0).toLocaleString('en-IN')}`);
            }

            if (orders.length === 0) {
                $('#payoutsTable tbody').html('<tr><td colspan="6" class="text-center">No payouts found</td></tr>');
                return;
            }
            
            orders.forEach(function(order) {
                let statusBadge = '';
                let actionBtn = '';
                
                if (order.is_seller_paid) {
                    statusBadge = '<span class="badge bg-success">Paid to Seller</span>';
                    actionBtn = `<small class="text-muted">Paid on ${order.seller_paid_at}</small>`;
                } else if (order.status === 'Returned' || (order.is_purchase && order.status === 'Delivered')) {
                    statusBadge = '<span class="badge bg-warning text-dark">Eligible for Payout</span>';
                    actionBtn = `
                        <button class="btn btn-sm btn-dark" onclick="confirmSellerPayout(${order.id}, ${order.amount}, '${order.seller_name}')" style="font-size: 0.7rem; border-radius: 0;">
                            Mark as Paid
                        </button>`;
                } else {
                    statusBadge = `<span class="badge bg-light text-dark border">Order ${order.status}</span>`;
                    actionBtn = '<small class="text-muted">Awaiting Return</small>';
                }

                rows += `<tr>
                    <td><a href="{{ route('admin.orders') }}?search=${order.id}" target="_blank">#${order.id}</a></td>
                    <td><small>${order.seller_name}</small></td>
                    <td class="fw-bold">₹${order.amount}</td>
                    <td><small>${order.created_at}</small></td>
                    <td>${statusBadge}</td>
                    <td class="text-end">${actionBtn}</td>
                </tr>`;
            });
            
            $('#payoutsTable tbody').html(rows);

        }).fail(function() {
            $('#payoutsTable tbody').html('<tr><td colspan="6" class="text-center text-danger">Error loading payouts</td></tr>');
        });
    }

    // Confirm Seller Payout
    window.confirmSellerPayout = function(orderId, amount, sellerName) {
        $('#dashboardActionConfirmTitle').text('Confirm Seller Payout');
        $('#dashboardActionConfirmMsg').html(`Are you sure you want to mark the payout of <b>₹${amount}</b> for <b>${sellerName}</b> as COMPLETED? <br><small class="text-muted">This will record that you have transferred the amount to the seller.</small>`);
        
        $('#dashboardConfirmActionBtn').off('click').on('click', function() {
            let btn = $(this);
            btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Processing...');
            
            $.post("{{ url('/admin/payouts/mark-paid') }}/" + orderId, {
                _token: "{{ csrf_token() }}"
            }, function(res) {
                $('#dashboardActionConfirmModal').modal('hide');
                btn.prop('disabled', false).text('Confirm');
                if (res.success) {
                    loadSellerPayouts();
                    // Alert success
                    showAlert('Payout marked as completed successfully.', 'success');
                }
            });
        });
        
        $('#dashboardActionConfirmModal').modal('show');
    }

    // Initial Load
    loadOrders();
    loadPayments();
    loadSecurityDeposits();
    loadSellerPayouts();

    // Attach event listeners for filters
    $('#orderStatusFilter').on('change', function() {
        loadOrders();
    });
    
    $('#paymentStatusFilter').on('change', function() {
        loadPayments();
    });

    $('#securityStatusFilter').on('change', function() {
        loadSecurityDeposits();
    });

    $('#payoutStatusFilter').on('change', function() {
        loadSellerPayouts();
    });

    // Auto-refresh every 30 seconds
    setInterval(function() {
        if (!$('.modal.show').length) {
            loadOrders();
            loadPayments();
            loadSecurityDeposits();
            loadSellerPayouts();
        }
    }, 30000);

});
</script>
@endpush
