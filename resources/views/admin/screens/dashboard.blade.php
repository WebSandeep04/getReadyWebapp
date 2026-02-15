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
        <div class="d-flex gap-4 text-center">
            <div>
                <div class="text-uppercase small text-white-50">Total Listings</div>
                <div class="fs-3 fw-semibold" id="totalCount">0</div>
            </div>
            <div>
                <div class="text-uppercase small text-white-50">Rent Volume</div>
                <div class="fs-3 fw-semibold" id="totalRentSum">₹0</div>
            </div>
            <div>
                <div class="text-uppercase small text-white-50">Security Held</div>
                <div class="fs-3 fw-semibold" id="totalDepositSum">₹0</div>
            </div>
        </div>
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
                        <th>Base Rent (₹)</th>
                        <th>Buyer See (₹)</th>
                        <th>Seller See (₹)</th>
                        <th>Deposit (₹)</th>
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
                        <!-- <th class="text-center">Actions</th> -->
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
                <div class="stat-card__label">Paid</div>
                <div class="stat-card__value" id="paidPaymentCount">-</div>
                <small>Confirmed payments</small>
            </div>
        </div>
        <div class="col-lg-3 col-sm-6">
            <div class="stat-card stat-pending">
                <div class="stat-card__icon"><i class="bi bi-clock-history"></i></div>
                <div class="stat-card__label">Pending</div>
                <div class="stat-card__value" id="pendingPaymentCount">-</div>
                <small>Awaiting confirmation</small>
            </div>
        </div>
        <div class="col-lg-3 col-sm-6">
            <div class="stat-card stat-rejected">
                <div class="stat-card__icon"><i class="bi bi-x-circle-fill"></i></div>
                <div class="stat-card__label">Failed</div>
                <div class="stat-card__value" id="failedPaymentCount">-</div>
                <small>Transaction failures</small>
            </div>
        </div>
        <div class="col-lg-3 col-sm-6">
            <div class="stat-card stat-reapproval">
                <div class="stat-card__icon"><i class="bi bi-wallet2"></i></div>
                <div class="stat-card__label">Transaction Volume</div>
                <div class="stat-card__value" id="totalVolume">₹0</div>
                <small>Total amount paid by buyers</small>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-lg-2 col-sm-6">
            <div class="stat-card" style="border-left: 4px solid #0d6efd;">
                <div class="stat-card__label">Buyer Comm</div>
                <div class="stat-card__value text-primary small" id="buyerCommTotal">₹0</div>
                <small>Commission only</small>
            </div>
        </div>
        <div class="col-lg-2 col-sm-6">
            <div class="stat-card" style="border-left: 4px solid #6f42c1;">
                <div class="stat-card__label">Seller Comm</div>
                <div class="stat-card__value text-purple small" style="color: #6f42c1;" id="sellerCommTotal">₹0</div>
                <small>Commission only</small>
            </div>
        </div>
        <div class="col-lg-2 col-sm-6">
            <div class="stat-card" style="border-left: 4px solid #000;">
                <div class="stat-card__label">Total Comm</div>
                <div class="stat-card__value fw-bold small" id="totalCommission">₹0</div>
                <small>Platform base</small>
            </div>
        </div>
        <div class="col-lg-2 col-sm-6">
            <div class="stat-card" style="border-left: 4px solid #6c757d;">
                <div class="stat-card__label">Rent GST</div>
                <div class="stat-card__value text-secondary small" id="rentGstTotal">₹0</div>
                <small>Tax on Rent</small>
            </div>
        </div>
        <div class="col-lg-2 col-sm-6">
            <div class="stat-card" style="border-left: 4px solid #adb5bd;">
                <div class="stat-card__label">Buyer GST</div>
                <div class="stat-card__value text-secondary small" id="buyerGstTotal">₹0</div>
                <small>Tax on B.Comm</small>
            </div>
        </div>
        <div class="col-lg-2 col-sm-6">
            <div class="stat-card" style="border-left: 4px solid #dee2e6;">
                <div class="stat-card__label">Seller GST</div>
                <div class="stat-card__value text-secondary small" id="sellerGstTotal">₹0</div>
                <small>Tax on S.Comm</small>
            </div>
        </div>
        <div class="col-lg-2 col-sm-6">
            <div class="stat-card" style="border-left: 4px solid #495057;">
                <div class="stat-card__label fw-bold">Total GST</div>
                <div class="stat-card__value text-dark small fw-bold" id="totalGst">₹0</div>
                <small>Total Tax Amount</small>
            </div>
        </div>
        <div class="col-lg-2 col-sm-6">
            <div class="stat-card" style="border-left: 4px solid #000; background: #f8f9fa;">
                <div class="stat-card__label fw-bold">Platform Earning</div>
                <div class="stat-card__value text-dark small fw-bold" id="totalPlatformEarning">₹0</div>
                <small>Comm + Comm Tax</small>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-12">
            <div class="stat-card" style="border-left: 4px solid #198754;">
                <div class="stat-card__icon"><i class="bi bi-cash-stack"></i></div>
                <div class="stat-card__label">Seller Net Payouts</div>
                <div class="stat-card__value text-success" id="sellerPayouts">₹0</div>
                <small>Total net amount due/paid to sellers across all orders</small>
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
                        <th>Base Rent</th>
                        <th>Buyer Comm</th>
                        <th>Seller Comm</th>
                        <th>Rent GST</th>
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
            <div class="stat-card" style="border-left: 4px solid #6f42c1; background: #f8f0ff;">
                <div class="stat-card__icon" style="color: #6f42c1;"><i class="bi bi-cash-stack"></i></div>
                <div class="stat-card__label" style="color: #6f42c1;">Total Seller Net</div>
                <div class="stat-card__value" id="totalSellerNetVal" style="color: #6f42c1;">₹0</div>
                <small>Gross earnings for sellers</small>
            </div>
        </div>
        <div class="col-lg-4 col-sm-6">
            <div class="stat-card" style="border-left: 4px solid #fd7e14; background: #fffcf0;">
                <div class="stat-card__icon" style="color: #fd7e14;"><i class="bi bi-hourglass-split"></i></div>
                <div class="stat-card__label" style="color: #fd7e14;">Need to Pay</div>
                <div class="stat-card__value" id="needToPaySellerVal" style="color: #fd7e14;">₹0</div>
                <small>Orders returned, not paid</small>
            </div>
        </div>
        <div class="col-lg-4 col-sm-6">
            <div class="stat-card" style="border-left: 4px solid #20c997; background: #e6fffa;">
                <div class="stat-card__icon" style="color: #20c997;"><i class="bi bi-check2-circle"></i></div>
                <div class="stat-card__label" style="color: #20c997;">Paid to Sellers</div>
                <div class="stat-card__value" id="paidToSellersVal" style="color: #20c997;">₹0</div>
                <small>Transferred to accounts</small>
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
            url += `?status=${status}`;
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
                
                rows += `<tr>
                    <td>${cloth.title}</td>
                    <td>${cloth.category}</td>
                    <td>${cloth.user_name}</td>
                    <td>${cloth.gender}</td>
                    <td>${cloth.size}</td>
                    <td>${cloth.condition}</td>
                    <td>₹${cloth.rent_price}</td>
                    <td class="fw-bold">₹${cloth.buyer_see_rent}</td>
                    <td class="fw-bold">₹${cloth.seller_see_rent}</td>
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

    // Show alert function
    function showAlert(message, type) {
        const alertHtml = `
            <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        `;
        $('#alertBox').html(alertHtml);
        
        // Auto-dismiss after 5 seconds
        setTimeout(function() {
            $('.alert').fadeOut();
        }, 5000);
    }

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
                if (statusLower === 'delivered') statusBadge = '<span class="badge bg-success">Delivered</span>';
                else if (statusLower === 'shipped') statusBadge = '<span class="badge bg-info">Shipped</span>';
                else if (statusLower === 'returned') statusBadge = '<span class="badge bg-danger">Returned</span>';
                else if (statusLower === 'cancelled') statusBadge = '<span class="badge bg-secondary">Cancelled</span>';
                else statusBadge = '<span class="badge bg-warning text-dark">Processing</span>';

                rows += `<tr>
                    <td>#${order.id}</td>
                    <td>${order.user_name}</td>
                    <td>${order.created_at_formatted}</td>
                    <td>${order.items_count} Item(s)</td>
                    <td>₹${order.total_amount}</td>
                    <td>${statusBadge}</td>
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
                $('#pendingPaymentCount').text(stats.pending_count || '0');
                $('#failedPaymentCount').text(stats.failed_count || '0');
                $('#totalVolume').text(`₹${Math.round(stats.total_volume || 0).toLocaleString('en-IN')}`);
                $('#buyerCommTotal').text(`₹${Math.round(stats.buyer_commission_total || 0).toLocaleString('en-IN')}`);
                $('#sellerCommTotal').text(`₹${Math.round(stats.seller_commission_total || 0).toLocaleString('en-IN')}`);
                $('#totalCommission').text(`₹${Math.round(stats.total_commission || 0).toLocaleString('en-IN')}`);
                $('#rentGstTotal').text(`₹${Math.round(stats.rent_gst_total || 0).toLocaleString('en-IN')}`);
                $('#buyerGstTotal').text(`₹${Math.round(stats.buyer_comm_gst_total || 0).toLocaleString('en-IN')}`);
                $('#sellerGstTotal').text(`₹${Math.round(stats.seller_comm_gst_total || 0).toLocaleString('en-IN')}`);
                $('#totalGst').text(`₹${Math.round(stats.total_gst || 0).toLocaleString('en-IN')}`);
                $('#sellerPayouts').text(`₹${Math.round(stats.seller_payouts || 0).toLocaleString('en-IN')}`);
                $('#totalPlatformEarning').text(`₹${Math.round(stats.total_platform_earning || 0).toLocaleString('en-IN')}`);
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
                $('#totalSellerNetVal').text(`₹${Math.round(stats.total_net || 0).toLocaleString('en-IN')}`);
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
                } else if (order.status === 'Returned') {
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
                    alert('Payout marked as completed successfully.');
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
