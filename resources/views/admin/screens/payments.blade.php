@extends('admin.layouts.app')

@section('title', 'Payment History')
@section('page_title', 'Payment History')

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

</style>
@endpush

@section('content')
<div class="container mt-4">

    <!-- Volume & Status Stats Row -->
    <div class="row g-3 mb-3">
        <div class="col-md-3">
            <div class="stat-card" style="border-left: 4px solid #000;">
                <div class="stat-card__icon"><i class="bi bi-wallet2"></i></div>
                <div class="stat-card__label">Transaction Volume</div>
                <div class="stat-card__value" id="stat-volume">₹{{ number_format($stats['total_volume']) }}</div>
                <small class="text-muted">Total amount paid by buyers</small>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card" style="border-left: 4px solid #198754;">
                <div class="stat-card__icon"><i class="bi bi-cash-stack"></i></div>
                <div class="stat-card__label">Seller Net Payouts</div>
                <div class="stat-card__value text-success" id="stat-payouts">₹{{ number_format($stats['seller_payouts']) }}</div>
                <small class="text-muted">Total due to garment owners</small>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-card__icon"><i class="bi bi-hourglass-split text-warning"></i></div>
                <div class="stat-card__label">Pending Payments</div>
                <div class="stat-card__value text-warning" id="stat-pending">{{ number_format($stats['pending_count']) }}</div>
                <small class="text-muted">Awaiting confirmation</small>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-card__icon"><i class="bi bi-x-circle text-danger"></i></div>
                <div class="stat-card__label">Failed / Cancelled</div>
                <div class="stat-card__value text-danger" id="stat-failed">{{ number_format($stats['failed_count']) }}</div>
                <small class="text-muted">Unsuccessful transactions</small>
            </div>
        </div>
    </div>

    <!-- Platform Earnings Breakdown -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="stat-card" style="border-left: 4px solid #0d6efd;">
                <div class="stat-card__label text-primary">Buyer Commissions</div>
                <div class="stat-card__value text-primary" style="font-size: 1.5rem;" id="stat-buyer-comm">₹{{ number_format($stats['buyer_commission_total']) }}</div>
                <small class="text-muted">Total Gross (Comm + GST)</small>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card" style="border-left: 4px solid #6f42c1;">
                <div class="stat-card__label text-purple" style="color: #6f42c1;">Seller Commissions</div>
                <div class="stat-card__value text-purple" style="color: #6f42c1; font-size: 1.5rem;" id="stat-seller-comm">₹{{ number_format($stats['seller_commission_total']) }}</div>
                <small class="text-muted">Total Gross (Comm + GST)</small>
            </div>
        </div>
        <div class="col-md-2">
            <div class="stat-card" style="border-left: 4px solid #212529;">
                <div class="stat-card__label fw-bold">Total Comm.</div>
                <div class="stat-card__value fw-bold" style="font-size: 1.5rem;" id="stat-total-comm">₹{{ number_format($stats['total_commission']) }}</div>
                <small class="text-muted">Platform Total</small>
            </div>
        </div>
        <div class="col-md-2">
            <div class="stat-card" style="border-left: 4px solid #6c757d;">
                <div class="stat-card__label text-secondary">Rent GST</div>
                <div class="stat-card__value text-secondary" style="font-size: 1.25rem;" id="stat-rent-gst">₹{{ number_format($stats['rent_gst_total']) }}</div>
                <small class="text-muted">Tax on Rent</small>
            </div>
        </div>
        <div class="col-md-2">
            <div class="stat-card" style="border-left: 4px solid #adb5bd;">
                <div class="stat-card__label text-secondary">Buyer GST</div>
                <div class="stat-card__value text-secondary" style="font-size: 1.25rem;" id="stat-buyer-gst">₹{{ number_format($stats['buyer_comm_gst_total']) }}</div>
                <small class="text-muted">Tax on B.Comm</small>
            </div>
        </div>
        <div class="col-md-2">
            <div class="stat-card" style="border-left: 4px solid #dee2e6;">
                <div class="stat-card__label text-secondary">Seller GST</div>
                <div class="stat-card__value text-secondary" style="font-size: 1.25rem;" id="stat-seller-gst">₹{{ number_format($stats['seller_comm_gst_total']) }}</div>
                <small class="text-muted">Tax on S.Comm</small>
            </div>
        </div>
        <div class="col-md-2">
            <div class="stat-card" style="border-left: 4px solid #495057;">
                <div class="stat-card__label fw-bold">Total GST</div>
                <div class="stat-card__value text-dark fw-bold" style="font-size: 1.25rem;" id="stat-total-gst">₹{{ number_format($stats['total_gst']) }}</div>
                <small class="text-muted">Combined Tax</small>
            </div>
        </div>
        <div class="col-md-2">
            <div class="stat-card" style="border-left: 4px solid #000; background: #f8f9fa;">
                <div class="stat-card__label fw-bold">Platform Earning</div>
                <div class="stat-card__value text-dark fw-bold" style="font-size: 1.25rem;" id="stat-platform-earning">₹{{ number_format($stats['total_platform_earning']) }}</div>
                <small class="text-muted">Comm + Comm Tax</small>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4 shadow-sm border-0">
        <div class="card-body py-2">
            <form id="paymentFilterForm" class="row g-2 align-items-center">
                <div class="col-auto">
                    <label class="fw-bold small me-2">Show:</label>
                </div>
                <div class="col-auto">
                    <select name="status" class="form-select form-select-sm" id="statusFilter">
                        <option value="">All Statuses</option>
                        <option value="Paid">Paid</option>
                        <option value="Pending">Pending</option>
                        <option value="Failed">Failed</option>
                        <option value="Cancelled">Cancelled</option>
                        <option value="Refunded">Refunded</option>
                        <option value="Partially Refunded">Partially Refunded</option>
                    </select>
                </div>
                 <div class="col-auto ms-auto">
                    <input type="text" name="search" id="searchInput" class="form-control form-control-sm" placeholder="Search Order/Transaction ID...">
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
                            <th>Order / Date</th>
                            <th>Customer</th>
                            <th>Total (₹) / Txn</th>
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
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="paymentTableBody">
                        <tr><td colspan="6" class="text-center py-4 text-muted">Loading...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white" id="paymentPagination">
            <!-- Pagination loaded via AJAX -->
        </div>
    </div>

</div>

@endsection

@push('scripts')
<script>
$(function() {
    const fetchUrl = "{{ route('admin.payments.fetch') }}";

    // --- Data Loading ---
    function fetchPaymentData(extra = {}) {
        const params = $('#paymentFilterForm').serializeArray().reduce((obj, item) => {
            obj[item.name] = item.value;
            return obj;
        }, {});

        Object.assign(params, extra);

        $('#paymentTableBody').html('<tr><td colspan="15" class="text-center py-4 text-muted">Loading...</td></tr>');

        $.ajax({
            url: fetchUrl,
            data: params,
            dataType: 'json',
            success: function(res) {
                $('#paymentTableBody').html(res.table_html);
                $('#paymentPagination').html(res.pagination_html);
                
                // Update stats
                if(res.stats) {
                    $('#stat-volume').text('₹' + Math.round(res.stats.total_volume).toLocaleString());
                    $('#stat-payouts').text('₹' + Math.round(res.stats.seller_payouts).toLocaleString());
                    $('#stat-pending').text(Number(res.stats.pending_count).toLocaleString());
                    $('#stat-failed').text(Number(res.stats.failed_count).toLocaleString());
                    $('#stat-buyer-comm').text('₹' + Math.round(res.stats.buyer_commission_total).toLocaleString());
                    $('#stat-seller-comm').text('₹' + Math.round(res.stats.seller_commission_total).toLocaleString());
                    $('#stat-total-comm').text('₹' + Math.round(res.stats.total_commission).toLocaleString());
                    $('#stat-rent-gst').text('₹' + Math.round(res.stats.rent_gst_total).toLocaleString());
                    $('#stat-buyer-gst').text('₹' + Math.round(res.stats.buyer_comm_gst_total).toLocaleString());
                    $('#stat-seller-gst').text('₹' + Math.round(res.stats.seller_comm_gst_total).toLocaleString());
                    $('#stat-total-gst').text('₹' + Math.round(res.stats.total_gst).toLocaleString());
                    $('#stat-platform-earning').text('₹' + Math.round(res.stats.total_platform_earning).toLocaleString());
                }
            },
            error: function() {
                $('#paymentTableBody').html('<tr><td colspan="15" class="text-center py-4 text-danger">Error loading data.</td></tr>');
            }
        });
    }

    // Initial load
    fetchPaymentData();

    // Filter change
    $('#statusFilter').on('change', function() {
        console.log("Filter changed");
        fetchPaymentData({ page: 1 });
    });

    // Search (Debounced)
    let searchTimeout;
    $('#searchInput').on('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            fetchPaymentData({ page: 1 });
        }, 500);
    });

    // Pagination
    $(document).on('click', '#paymentPagination a', function(e) {
        e.preventDefault();
        const page = new URL(this.href).searchParams.get('page');
        fetchPaymentData({ page: page });
    });
});
</script>
@endpush
