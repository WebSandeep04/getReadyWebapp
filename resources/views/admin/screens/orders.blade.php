@extends('admin.layouts.app')

@section('title', 'Orders')
@section('page_title', 'Orders & Returns')

@push('styles')
<style>
.orders-hero {
    background: linear-gradient(135deg, #0f172a, #312e81);
    border-radius: 20px;
    padding: 1.75rem;
    color: #fff;
    margin-bottom: 1.75rem;
    box-shadow: 0 25px 40px rgba(15,23,42,.35);
}
.orders-hero__title { font-size: 1.35rem; font-weight: 600; }
.orders-hero__subtitle { margin: 0; opacity: .9; }
.stat-card {
    border-radius: 20px;
    padding: 1.35rem;
    color: #fff;
    position: relative;
    overflow: hidden;
    box-shadow: 0 18px 32px rgba(15,23,42,.18);
}
.stat-card__label {
    text-transform: uppercase;
    letter-spacing: .08em;
    font-size: .8rem;
    opacity: .85;
}
.stat-card__value {
    font-size: 2.05rem;
    font-weight: 600;
    margin: .35rem 0;
}
.stat-card__icon {
    position: absolute;
    top: 1rem;
    right: 1rem;
    font-size: 2.4rem;
    opacity: .2;
}
.stat-total { background: linear-gradient(135deg,#6366f1,#4338ca); }
.stat-overdue { background: linear-gradient(135deg,#fb7185,#dc2626); }
.stat-due { background: linear-gradient(135deg,#fcd34d,#f97316); color:#111; }
.stat-purchase { background: linear-gradient(135deg,#34d399,#059669); }
.table th {
    background: #f8fafc;
    border-bottom: 2px solid #e5e7eb;
    font-weight: 600;
    text-transform: uppercase;
    font-size: .75rem;
    letter-spacing: .08em;
}
.table td { vertical-align: middle; }
.order-type-badge {
    font-size: .8rem;
    border-radius: 999px;
    padding: .25rem .7rem;
}
.overdue-row { border-left: 4px solid #dc2626; background: #fef2f2; }
.timeline-flag {
    display: inline-flex;
    align-items: center;
    gap: .35rem;
    border-radius: 999px;
    padding: .25rem .75rem;
    font-size: .85rem;
}
.timeline-flag.overdue { background:#fee2e2; color:#b91c1c; }
.timeline-flag.due-soon { background:#fef9c3; color:#854d0e; }
.timeline-flag.completed { background:#dcfce7; color:#166534; }
.filter-card {
    border-radius: 20px;
    box-shadow: 0 12px 25px rgba(15,23,42,.08);
    border: 1px solid #e5e7eb;
    background: #ffffff;
}
.filter-form .form-control,
.filter-form .form-select {
    border-radius: 999px;
    padding-left: 1rem;
}
</style>
@endpush

@section('content')
<div class="container mt-4">
    <div class="orders-hero d-flex flex-column flex-lg-row justify-content-between align-items-lg-center mb-4">
        <div class="mb-3 mb-lg-0">
            <div class="orders-hero__title">Order Operations</div>
            <p class="orders-hero__subtitle mb-0">Track checkout flows, rental returns, and payments in one workspace.</p>
        </div>
        <div class="text-end">
            <div class="text-uppercase small text-white-50">Supervisor</div>
            <div class="fs-5 fw-semibold">{{ Auth::user()->name ?? 'Admin' }}</div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-3 col-sm-6">
            <div class="stat-card stat-total">
                <div class="stat-card__icon"><i class="bi bi-graph-up"></i></div>
                <div class="stat-card__label">Total orders</div>
                <div class="stat-card__value" id="statTotal">{{ number_format($stats['total']) }}</div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="stat-card stat-overdue">
                <div class="stat-card__icon"><i class="bi bi-exclamation-octagon"></i></div>
                <div class="stat-card__label">Overdue returns</div>
                <div class="stat-card__value" id="statOverdue">{{ number_format($stats['overdue']) }}</div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="stat-card stat-due">
                <div class="stat-card__icon"><i class="bi bi-alarm"></i></div>
                <div class="stat-card__label text-dark">Due today</div>
                <div class="stat-card__value text-dark" id="statDueToday">{{ number_format($stats['due_today']) }}</div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="stat-card stat-purchase">
                <div class="stat-card__icon"><i class="bi bi-bag-check"></i></div>
                <div class="stat-card__label">Purchase orders</div>
                <div class="stat-card__value" id="statPurchase">{{ number_format($stats['purchase']) }}</div>
            </div>
        </div>
    </div>

    <div class="card mb-4 filter-card">
        <div class="card-body">
            <form method="GET" class="filter-form js-order-filter-form">
                <div class="row g-3 align-items-end">
                    <div class="col-lg-3 col-md-6">
                        <label class="form-label small text-uppercase text-muted">Search</label>
                        <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" class="form-control" placeholder="Order ID / buyer / amount">
                    </div>
                    <div class="col-lg-2 col-md-3">
                        <label class="form-label small text-uppercase text-muted">Status</label>
                        <select name="status" class="form-select">
                            <option value="">All</option>
                            @foreach($statuses as $status)
                                <option value="{{ $status }}" {{ ($filters['status'] ?? '') === $status ? 'selected' : '' }}>{{ $status }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-lg-2 col-md-3">
                        <label class="form-label small text-uppercase text-muted">Order Type</label>
                        <select name="type" class="form-select">
                            <option value="">All</option>
                            <option value="rental" {{ ($filters['type'] ?? '') === 'rental' ? 'selected' : '' }}>Rental only</option>
                            <option value="purchase" {{ ($filters['type'] ?? '') === 'purchase' ? 'selected' : '' }}>Purchase only</option>
                            <option value="mixed" {{ ($filters['type'] ?? '') === 'mixed' ? 'selected' : '' }}>Mixed</option>
                        </select>
                    </div>
                    <div class="col-lg-2 col-md-3">
                        <label class="form-label small text-uppercase text-muted">Return State</label>
                        <select name="return_state" class="form-select">
                            <option value="">All</option>
                            <option value="overdue" {{ ($filters['return_state'] ?? '') === 'overdue' ? 'selected' : '' }}>Overdue</option>
                            <option value="due_soon" {{ ($filters['return_state'] ?? '') === 'due_soon' ? 'selected' : '' }}>Due soon</option>
                            <option value="completed" {{ ($filters['return_state'] ?? '') === 'completed' ? 'selected' : '' }}>Returned</option>
                        </select>
                    </div>
                    <div class="col-lg-2 col-md-3">
                        <label class="form-label small text-uppercase text-muted">Payment</label>
                        <select name="payment_status" class="form-select">
                            <option value="">All</option>
                            @foreach($paymentStatuses as $paymentStatus)
                                <option value="{{ $paymentStatus }}" {{ ($filters['payment_status'] ?? '') === $paymentStatus ? 'selected' : '' }}>
                                    {{ ucfirst($paymentStatus) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <label class="form-label small text-uppercase text-muted">Placed From</label>
                        <input type="date" class="form-control" name="placed_from" value="{{ $filters['placed_from'] ?? '' }}">
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <label class="form-label small text-uppercase text-muted">Placed To</label>
                        <input type="date" class="form-control" name="placed_to" value="{{ $filters['placed_to'] ?? '' }}">
                    </div>
                    <div class="col-lg-3 col-md-6 d-flex">
                        <button type="button" class="btn btn-outline-secondary w-100" id="ordersResetFilters">
                            Reset Filters
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card order-card">
        <div class="card-body p-0">
            <div class="table-responsive">
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
</div>
@endsection

@push('scripts')
<script>
$(function() {
    const $form = $('.js-order-filter-form');
    const $tableBody = $('#ordersTableBody');
    const $pagination = $('#ordersPagination');
    const $resetBtn = $('#ordersResetFilters');
    const statsEls = {
        total: $('#statTotal'),
        overdue: $('#statOverdue'),
        dueToday: $('#statDueToday'),
        purchase: $('#statPurchase')
    };
    const endpoint = "{{ route('admin.orders.data') }}";

    let activeRequest = null;

    function serializeFilters(extra = {}) {
        const data = {};
        $form.serializeArray().forEach(({ name, value }) => {
            data[name] = value;
        });
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
    $form.find('input[name="search"]').on('input', function() {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(fetchOrders, 500);
    });

    $form.on('submit', function(e) {
        e.preventDefault();
        fetchOrders();
    });

    $resetBtn.on('click', function() {
        $form[0].reset();
        fetchOrders();
    });

    $(document).on('click', '#ordersPagination a', function(e) {
        e.preventDefault();
        const page = new URL(this.href).searchParams.get('page') || 1;
        fetchOrders({ page });
    });
});
</script>
@endpush

