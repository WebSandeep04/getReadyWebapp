@extends('admin.layouts.app')

@section('title', 'Seller Payouts')
@section('page_title', 'Seller Payouts')

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

    <!-- Payout Stats Row -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="stat-card" style="border-left: 4px solid #6f42c1;">
                <div class="stat-card__icon"><i class="bi bi-cash-stack"></i></div>
                <div class="stat-card__label">Total Seller Amount Held</div>
                <div class="stat-card__value" id="stat-total-held">₹{{ number_format($stats['total_held']) }}</div>
                <small class="text-muted">Gross earnings (Unpaid)</small>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card" style="border-left: 4px solid #fd7e14;">
                <div class="stat-card__icon"><i class="bi bi-hourglass-split"></i></div>
                <div class="stat-card__label">Need to Pay</div>
                <div class="stat-card__value" id="stat-need-to-pay">₹{{ number_format($stats['need_to_pay']) }}</div>
                <small class="text-muted">Returned & Ready for Payout</small>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card" style="border-left: 4px solid #20c997;">
                <div class="stat-card__icon"><i class="bi bi-check2-circle"></i></div>
                <div class="stat-card__label">Paid to Sellers</div>
                <div class="stat-card__value" id="stat-paid-to-sellers">₹{{ number_format($stats['paid_to_sellers']) }}</div>
                <small class="text-muted">Transferred to accounts</small>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4 shadow-sm border-0">
        <div class="card-body py-2">
            <form id="payoutFilterForm" class="row g-2 align-items-center">
                <div class="col-auto">
                    <label class="fw-bold small me-2">Show:</label>
                </div>
                <div class="col-auto">
                    <select name="status" class="form-select form-select-sm" id="statusFilter">
                        <option value="">All Payouts</option>
                        <option value="processing">In Progress Order</option>
                        <option value="pending">Eligible for Payout</option>
                        <option value="completed">Paid to Seller</option>
                    </select>
                </div>
                 <div class="col-auto ms-auto">
                    <input type="text" name="search" id="searchInput" class="form-control form-control-sm" placeholder="Search Order ID or Seller...">
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
                            <th>Seller / Buyer</th>
                            <th>Net Amount (₹)</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="payoutTableBody">
                        <tr><td colspan="5" class="text-center py-4 text-muted">Loading...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white" id="payoutPagination">
            <!-- Pagination loaded via AJAX -->
        </div>
    </div>

</div>

<!-- Action Confirmation Modal -->
<div class="modal fade" id="payoutActionConfirmModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 0 !important;">
            <div class="modal-header border-bottom-0 pb-0">
                <h5 class="modal-title fw-bold" id="payoutActionConfirmTitle">Confirm Action</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body pt-2">
                <p class="text-muted mb-0" id="payoutActionConfirmMsg">Are you sure you want to proceed?</p>
            </div>
            <div class="modal-footer border-top-0 pt-0">
                <button type="button" class="btn btn-link text-decoration-none text-muted" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-dark px-4" id="payoutConfirmActionBtn" style="border-radius: 0 !important;">
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
    const fetchUrl = "{{ route('admin.payouts.fetch') }}";

    // --- Data Loading ---
    function fetchPayoutData(extra = {}) {
        const params = $('#payoutFilterForm').serializeArray().reduce((obj, item) => {
            obj[item.name] = item.value;
            return obj;
        }, {});

        Object.assign(params, extra);

        $('#payoutTableBody').html('<tr><td colspan="5" class="text-center py-4 text-muted">Loading...</td></tr>');

        $.ajax({
            url: fetchUrl,
            data: params,
            dataType: 'json',
            success: function(res) {
                $('#payoutTableBody').html(res.table_html);
                $('#payoutPagination').html(res.pagination_html);
                
                // Update stats
                if(res.stats) {
                    $('#stat-total-held').text('₹' + Math.round(res.stats.total_held).toLocaleString('en-IN'));
                    $('#stat-need-to-pay').text('₹' + Math.round(res.stats.need_to_pay).toLocaleString('en-IN'));
                    $('#stat-paid-to-sellers').text('₹' + Math.round(res.stats.paid_to_sellers).toLocaleString('en-IN'));
                }
            },
            error: function() {
                $('#payoutTableBody').html('<tr><td colspan="5" class="text-center py-4 text-danger">Error loading data.</td></tr>');
            }
        });
    }

    // Confirm Seller Payout
    window.confirmSellerPayout = function(orderId, amount, sellerName) {
        $('#payoutActionConfirmTitle').text('Confirm Seller Payout');
        $('#payoutActionConfirmMsg').html(`Are you sure you want to mark the payout of <b>₹${amount}</b> for <b>${sellerName}</b> as COMPLETED? <br><small class="text-muted">This will record that you have transferred the amount to the seller.</small>`);
        
        $('#payoutConfirmActionBtn').off('click').on('click', function() {
            let btn = $(this);
            btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Processing...');
            
            $.post("{{ url('/admin/payouts/mark-paid') }}/" + orderId, {
                _token: "{{ csrf_token() }}"
            }, function(res) {
                $('#payoutActionConfirmModal').modal('hide');
                btn.prop('disabled', false).text('Confirm');
                if (res.success) {
                    fetchPayoutData();
                    showAlert('Payout marked as completed successfully.', 'success');
                }
            });
        });
        
        $('#payoutActionConfirmModal').modal('show');
    }

    // Initial load
    fetchPayoutData();

    // Filter change
    $('#statusFilter').on('change', function() {
        fetchPayoutData({ page: 1 });
    });

    // Search (Debounced)
    let searchTimeout;
    $('#searchInput').on('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            fetchPayoutData({ page: 1 });
        }, 500);
    });

    // Pagination
    $(document).on('click', '#payoutPagination a', function(e) {
        e.preventDefault();
        const page = new URL(this.href).searchParams.get('page');
        fetchPayoutData({ page: page });
    });
});
</script>
@endpush
