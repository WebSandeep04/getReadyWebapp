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
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="stat-card" style="border-left: 4px solid #000;">
                <div class="stat-card__icon"><i class="bi bi-check-circle text-success"></i></div>
                <div class="stat-card__label">Confirmed</div>
                <div class="stat-card__value" id="stat-confirmed">{{ number_format($stats['confirmed_count']) }}</div>
                <small class="text-muted" id="stat-confirmed-amount">Total: ₹{{ number_format($stats['confirmed_amount']) }}</small>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card" style="border-left: 4px solid #f39c12;">
                <div class="stat-card__icon"><i class="bi bi-hourglass-split text-warning"></i></div>
                <div class="stat-card__label">Pending</div>
                <div class="stat-card__value text-warning" id="stat-pending">{{ number_format($stats['pending_count']) }}</div>
                <small class="text-muted" id="stat-pending-amount">Total: ₹{{ number_format($stats['pending_amount']) }}</small>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card" style="border-left: 4px solid #e74c3c;">
                <div class="stat-card__icon"><i class="bi bi-x-circle text-danger"></i></div>
                <div class="stat-card__label">Failed</div>
                <div class="stat-card__value text-danger" id="stat-failed">{{ number_format($stats['failed_count']) }}</div>
                <small class="text-muted" id="stat-failed-amount">Total: ₹{{ number_format($stats['failed_amount']) }}</small>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card" style="border-left: 4px solid #3498db;">
                <div class="stat-card__icon"><i class="bi bi-arrow-counterclockwise text-info"></i></div>
                <div class="stat-card__label">Refund</div>
                <div class="stat-card__value text-info" id="stat-refund">{{ number_format($stats['refund_count']) }}</div>
                <small class="text-muted" id="stat-refund-amount">Total: ₹{{ number_format($stats['refund_amount']) }}</small>
            </div>
        </div>
    </div>

    <!-- Breakdown Stats Row -->
    <div class="row g-3 mb-4">
        <div class="col-md-2">
            <div class="stat-card" style="border-left: 4px solid #6c757d;">
                <div class="stat-card__label">Total Commission</div>
                <div class="stat-card__value" id="stat-total-comm" style="font-size: 1.25rem;">₹{{ number_format($stats['total_comm']) }}</div>
                <small class="text-muted">Incl. Buyer & Seller</small>
            </div>
        </div>
        <div class="col-md-2">
            <div class="stat-card" style="border-left: 4px solid #adb5bd;">
                <div class="stat-card__label">Rent GST</div>
                <div class="stat-card__value" id="stat-rent-gst" style="font-size: 1.25rem;">₹{{ number_format($stats['rent_gst']) }}</div>
                <small class="text-muted">On Rental Portion</small>
            </div>
        </div>
        <div class="col-md-2">
            <div class="stat-card" style="border-left: 4px solid #dee2e6;">
                <div class="stat-card__label">Buyer GST</div>
                <div class="stat-card__value" id="stat-buyer-gst" style="font-size: 1.25rem;">₹{{ number_format($stats['buyer_gst']) }}</div>
                <small class="text-muted">On Buyer Fees</small>
            </div>
        </div>
        <div class="col-md-2">
            <div class="stat-card" style="border-left: 4px solid #ced4da;">
                <div class="stat-card__label">Seller GST</div>
                <div class="stat-card__value" id="stat-seller-gst" style="font-size: 1.25rem;">₹{{ number_format($stats['seller_gst']) }}</div>
                <small class="text-muted">On Seller Fees</small>
            </div>
        </div>
        <div class="col-md-2">
            <div class="stat-card" style="border-left: 4px solid #495057;">
                <div class="stat-card__label">Total GST</div>
                <div class="stat-card__value" id="stat-total-gst" style="font-size: 1.25rem;">₹{{ number_format($stats['total_gst']) }}</div>
                <small class="text-muted">Aggregated Tax</small>
            </div>
        </div>
        <div class="col-md-2">
            <div class="stat-card" style="border-left: 4px solid #000; background: #f8f9fa;">
                <div class="stat-card__label">Platform Earning</div>
                <div class="stat-card__value" id="stat-platform-earning" style="font-size: 1.25rem;">₹{{ number_format($stats['platform_earning']) }}</div>
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
                    $('#stat-confirmed').text(Number(res.stats.confirmed_count).toLocaleString());
                    $('#stat-confirmed-amount').text('Total: ₹' + Math.round(res.stats.confirmed_amount).toLocaleString());
                    
                    $('#stat-pending').text(Number(res.stats.pending_count).toLocaleString());
                    $('#stat-pending-amount').text('Total: ₹' + Math.round(res.stats.pending_amount).toLocaleString());
                    
                    $('#stat-failed').text(Number(res.stats.failed_count).toLocaleString());
                    $('#stat-failed-amount').text('Total: ₹' + Math.round(res.stats.failed_amount).toLocaleString());
                    
                    $('#stat-refund').text(Number(res.stats.refund_count).toLocaleString());
                    $('#stat-refund-amount').text('Total: ₹' + Math.round(res.stats.refund_amount).toLocaleString());

                    // Breakdown stats
                    $('#stat-total-comm').text('₹' + Math.round(res.stats.total_comm).toLocaleString());
                    $('#stat-rent-gst').text('₹' + Math.round(res.stats.rent_gst).toLocaleString());
                    $('#stat-buyer-gst').text('₹' + Math.round(res.stats.buyer_gst).toLocaleString());
                    $('#stat-seller-gst').text('₹' + Math.round(res.stats.seller_gst).toLocaleString());
                    $('#stat-total-gst').text('₹' + Math.round(res.stats.total_gst).toLocaleString());
                    $('#stat-platform-earning').text('₹' + Math.round(res.stats.platform_earning).toLocaleString());
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
