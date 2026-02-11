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

    <!-- Stats Row -->
    <div class="row g-3 mb-4">
        <div class="col-md-4 col-sm-6">
            <div class="stat-card">
                <div class="stat-card__icon"><i class="bi bi-wallet2 text-success"></i></div>
                <div class="stat-card__label">Total Revenue</div>
                <div class="stat-card__value text-success" id="stat-revenue">₹{{ number_format($stats['total_revenue']) }}</div> <!-- Sum of Paid -->
                <small class="text-muted">Successful payments</small>
            </div>
        </div>
        <div class="col-md-4 col-sm-6">
            <div class="stat-card">
                <div class="stat-card__icon"><i class="bi bi-hourglass-split text-warning"></i></div>
                <div class="stat-card__label">Pending Payments</div>
                <div class="stat-card__value text-warning" id="stat-pending">{{ number_format($stats['pending_count']) }}</div>
                <small class="text-muted">Awaiting confirmation</small>
            </div>
        </div>
         <div class="col-md-4 col-sm-6">
            <div class="stat-card">
                <div class="stat-card__icon"><i class="bi bi-x-circle text-danger"></i></div>
                <div class="stat-card__label">Failed / Cancelled</div>
                <div class="stat-card__value text-danger" id="stat-failed">{{ number_format($stats['failed_count']) }}</div>
                <small class="text-muted">Unsuccessful transactions</small>
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
                            <th>Order ID / Date</th>
                            <th>Customer</th>
                            <th>Amount / Txn ID</th>
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

        $('#paymentTableBody').html('<tr><td colspan="6" class="text-center py-4 text-muted">Loading...</td></tr>');

        $.ajax({
            url: fetchUrl,
            data: params,
            dataType: 'json',
            success: function(res) {
                $('#paymentTableBody').html(res.table_html);
                $('#paymentPagination').html(res.pagination_html);
                
                // Update stats
                if(res.stats) {
                    $('#stat-revenue').text('₹' + Number(res.stats.total_revenue).toLocaleString());
                    $('#stat-pending').text(Number(res.stats.pending_count).toLocaleString());
                    $('#stat-failed').text(Number(res.stats.failed_count).toLocaleString());
                }
            },
            error: function() {
                $('#paymentTableBody').html('<tr><td colspan="6" class="text-center py-4 text-danger">Error loading data.</td></tr>');
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
