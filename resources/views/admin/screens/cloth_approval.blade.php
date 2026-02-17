@extends('admin.layouts.app')

@section('title', 'Cloth Approval Hub')
@section('page_title', 'Cloth Approval')

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
.approval-hero { background:#fff; padding:1.5rem; color:#000; margin-bottom:1.5rem; border: none; box-shadow: 0 15px 45px rgba(0,0,0,0.12); }
.approval-hero__title { font-size:1.2rem; font-weight:700; color: #000; }
.approval-hero__subtitle { margin:0; opacity:0.8; font-size: .85rem; color: #000; }
.stat-card { position:relative; padding:1.25rem; color:#000; background: #fff; border: none; box-shadow: 0 8px 25px rgba(0,0,0,0.1); overflow:hidden; height: 100%; display: flex; flex-direction: column; justify-content: center; min-height: 110px; transition: all 0.3s ease; }
.stat-card:hover { transform: translateY(-4px); box-shadow: 0 15px 35px rgba(0,0,0,0.15); }
.stat-card__label { text-transform:none; letter-spacing:normal; font-size:.7rem; font-weight: 700; color: #333; }
.stat-card__value { font-size:2rem; font-weight:800; margin:.1rem 0; color: #000; }
.stat-card__icon { position:absolute; top:0.75rem; right:0.75rem; font-size:1.5rem; opacity:1; color: #000; }
.stat-pending, .stat-approved, .stat-reapproval, .stat-rejected { background:#fff; }
.status-legend { background:#fff; padding:1.25rem; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.05); }
.legend-pill { display:flex; align-items:center; gap:.5rem; padding:.45rem .7rem; margin-bottom:.4rem; font-size:.8rem; font-weight:600; background: #fbfbfb; box-shadow: 0 2px 4px rgba(0,0,0,0.02); }
.admin-table th,.admin-table td { font-size:.65rem; padding:.35rem .5rem !important; }
.admin-table tr:hover { background-color: #fbfbfb !important; }
.admin-table .btn-icon { width:24px; height:24px; border: none; box-shadow: 0 4px 8px rgba(0,0,0,0.12); background: #fff; color: #000; font-size: 0.75rem; margin-right: 4px; transition: all 0.2s; display:inline-flex; align-items:center; justify-content:center; }
.admin-table .btn-icon:hover { transform: translateY(-1px); box-shadow: 0 6px 12px rgba(0,0,0,0.18); }
.admin-table .btn-success { background: #000 !important; color: #fff !important; }
.admin-table .btn-danger { background: #fff !important; color: #000 !important; border: 1px solid #000 !important; }
.admin-table .btn-outline-secondary { background: #fff !important; color: #000 !important; border: 1px solid #eee !important; }
.admin-table .btn-info { background: #f1f1f1 !important; color: #000 !important; border: 1px solid #ddd !important; }
.card { border: none; box-shadow: 0 20px 50px rgba(0,0,0,0.1); background: #fff; }
.card-header { padding: 1rem; background: #fff !important; color: #000 !important; border-bottom: 1px solid #eee; }
.card-header h5 { font-size: 0.95rem; color: #000; font-weight: 700; }
.form-select, .form-control { border: 1px solid #eee !important; background: #fff !important; box-shadow: 0 2px 4px rgba(0,0,0,0.03); color: #000 !important; font-size: 0.75rem; transition: all 0.2s; }
.form-select:focus, .form-control:focus { box-shadow: 0 5px 15px rgba(0,0,0,0.08); border-color: #000 !important; outline: none; }
.btn { border: none; box-shadow: 0 4px 10px rgba(0,0,0,0.1); transition: all 0.2s; }
.btn:hover { transform: translateY(-1px); box-shadow: 0 8px 18px rgba(0,0,0,0.15); }
.modal-content, .modal-header, .btn-close { border-radius: 0 !important; }
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
</style>
@endpush

@section('content')
<div class="container mt-4">
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

    </div>

    <!-- Filter Card -->
    <div class="card mb-4 filter-card">
        <div class="card-body">
            <form class="filter-form">
                <div class="row g-2 align-items-end">
                    <div class="col-lg-2 col-md-3 col-6">
                        <label class="form-label small text-uppercase fw-bold text-dark">Status</label>
                        <select id="filterStatus" class="form-select">
                            <option value="">All Statuses</option>
                            <option value="pending">Pending</option>
                            <option value="approved">Approved</option>
                            <option value="re-approval">Re-approval</option>
                            <option value="rejected">Rejected</option>
                        </select>
                    </div>
                    <div class="col-lg-2 col-md-3 col-6">
                        <label class="form-label small text-uppercase fw-bold text-dark">Category</label>
                        <select id="filterCategory" class="form-select">
                            <option value="">All</option>
                        </select>
                    </div>
                    <div class="col-auto">
                        <button type="button" class="btn btn-outline-secondary" id="resetFilters" title="Reset Filters">
                            <i class="bi bi-arrow-counterclockwise"></i>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Search Card -->
    <div class="card mb-4 border-0 shadow-sm" style="background: #fff;">
        <div class="card-body p-1">
            <div class="input-group">
                <span class="input-group-text bg-white border-0 ps-3"><i class="bi bi-search text-muted small"></i></span>
                <input type="text" id="clothSearchInput" class="form-control border-0 shadow-none ps-2" 
                       style="font-size: 0.9rem;"
                       placeholder="Search Cloth Title, Owner Name, or Category...">
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4 align-items-stretch">
        <div class="col-12">
            <div class="card h-100">
                <div class="card-header bg-primary text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="bi bi-shield-check me-2"></i>Clothes Approval Management</h5>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
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
                            <tbody></tbody>
                        </table>
                    <div class="d-flex justify-content-between align-items-center mt-3 d-none" id="clothesPagination"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

<!-- Details Modal -->
<div class="modal fade" id="detailModal" tabindex="-1" aria-labelledby="detailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-light">
                <h5 class="modal-title" id="detailModalLabel">Cloth Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-5">
                        <h6 class="text-muted fw-bold mb-3">Images</h6>
                        <div id="detailImages" class="mb-3"></div>
                        
                        <h6 class="text-muted fw-bold mb-2 mt-4">Status</h6>
                        <div id="detailStatus" class="mb-3"></div>

                        <h6 class="text-muted fw-bold mb-2 mt-4">Owner Info</h6>
                        <div class="d-flex align-items-center mb-3">
                             <img id="detailUserImage" src="" class="rounded-circle me-3" style="width:50px;height:50px;object-fit:cover;border:1px solid #dee2e6;">
                             <div>
                                 <div class="fw-bold" id="detailOwnerName">-</div>
                                 <div class="small text-muted" id="detailOwnerEmail">-</div>
                             </div>
                        </div>
                        <table class="table table-sm table-borderless small">
                            <tr>
                                <td class="text-muted ps-0" style="width:70px;">Phone:</td>
                                <td class="fw-semibold" id="detailOwnerPhone">-</td>
                            </tr>
                            <tr>
                                <td class="text-muted ps-0">Details:</td>
                                <td class="fw-semibold" id="detailOwnerBio">-</td>
                            </tr>
                            <tr>
                                <td class="text-muted ps-0">Location:</td>
                                <td class="fw-semibold" id="detailOwnerAddress">-</td>
                            </tr>
                            <tr>
                                <td class="text-muted ps-0">GSTIN:</td>
                                <td class="fw-semibold" id="detailOwnerGST">-</td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-7 border-start">
                        <h6 class="text-muted fw-bold mb-3">Product Information</h6>
                        <div class="row g-2 mb-4">
                            <div class="col-6">
                                <small class="text-muted d-block">Title</small>
                                <span class="fw-semibold" id="detailTitle">-</span>
                            </div>
                            <div class="col-6">
                                <small class="text-muted d-block">Brand</small>
                                <span class="fw-semibold" id="detailBrand">-</span>
                            </div>
                            <div class="col-6">
                                <small class="text-muted d-block">Category</small>
                                <span class="fw-semibold" id="detailCategory">-</span>
                            </div>
                            <div class="col-6">
                                <small class="text-muted d-block">Target Audience</small>
                                <span class="fw-semibold" id="detailGender">-</span>
                            </div>
                            <div class="col-6">
                                <small class="text-muted d-block">Size</small>
                                <span class="fw-semibold" id="detailSize">-</span>
                            </div>
                            <div class="col-6">
                                <small class="text-muted d-block">Fit Type</small>
                                <span class="fw-semibold" id="detailFitType">-</span>
                            </div>
                            <div class="col-6">
                                <small class="text-muted d-block">Condition</small>
                                <span class="fw-semibold" id="detailCondition">-</span>
                            </div>
                             <div class="col-6">
                                <small class="text-muted d-block">Fabric</small>
                                <span class="fw-semibold" id="detailFabric">-</span>
                            </div>
                            <div class="col-6">
                                <small class="text-muted d-block">Color</small>
                                <span class="fw-semibold" id="detailColor">-</span>
                            </div>
                            <div class="col-6">
                                <small class="text-muted d-block">Cleaned?</small>
                                <span class="fw-semibold" id="detailCleaned">-</span>
                            </div>
                        </div>

                        <h6 class="text-muted fw-bold mb-3">Measurements (Inches)</h6>
                        <div class="row g-2 mb-4 bg-light p-2 rounded">
                            <div class="col-4">
                                <small class="text-muted d-block">Chest/Bust</small>
                                <span class="fw-semibold" id="detailChest">-</span>
                            </div>
                            <div class="col-4">
                                <small class="text-muted d-block">Waist</small>
                                <span class="fw-semibold" id="detailWaist">-</span>
                            </div>
                             <div class="col-4">
                                <small class="text-muted d-block">Length</small>
                                <span class="fw-semibold" id="detailLength">-</span>
                            </div>
                             <div class="col-4">
                                <small class="text-muted d-block">Shoulder</small>
                                <span class="fw-semibold" id="detailShoulder">-</span>
                            </div>
                             <div class="col-4">
                                <small class="text-muted d-block">Sleeve</small>
                                <span class="fw-semibold" id="detailSleeve">-</span>
                            </div>
                        </div>

                        <h6 class="text-muted fw-bold mb-3 border-bottom pb-2">Rent Financials</h6>
                        <div class="row g-2 mb-3">
                             <div class="col-4">
                                <small class="text-muted d-block">Base Rent</small>
                                <span class="fw-bold" id="detailRent">-</span>
                            </div>
                            <div class="col-4">
                                <small class="text-muted d-block">Buyer See</small>
                                <span class="fw-bold text-primary" id="detailBuyerSeeRent">-</span>
                            </div>
                            <div class="col-4">
                                <small class="text-muted d-block">Seller See</small>
                                <span class="fw-bold text-success" id="detailSellerSeeRent">-</span>
                            </div>
                             <div class="col-4">
                                <small class="text-muted d-block">Security Deposit</small>
                                <span class="fw-bold text-primary" id="detailDeposit">-</span>
                            </div>
                        </div>

                        <h6 class="text-muted fw-bold mb-3 border-bottom pb-2">Purchase Financials</h6>
                        <div class="row g-2 mb-3">
                             <div class="col-4">
                                <small class="text-muted d-block">Base Price</small>
                                <span class="fw-bold" id="detailBasePrice">-</span>
                            </div>
                            <div class="col-4">
                                <small class="text-muted d-block">Buyer See</small>
                                <span class="fw-bold text-primary" id="detailBuyerSeePrice">-</span>
                            </div>
                            <div class="col-4">
                                <small class="text-muted d-block">Seller See</small>
                                <span class="fw-bold text-success" id="detailSellerSeePrice">-</span>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <small class="text-muted d-block">Defects/Notes</small>
                            <p class="small bg-light p-2 rounded mb-0" id="detailDefects">-</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer justify-content-between border-top-0">
                <button type="button" class="btn btn-outline-dark" data-bs-dismiss="modal">Close</button>
                <div>
                     <button type="button" class="btn btn-outline-danger reject-btn me-2" id="modalRejectBtn">
                        <i class="bi bi-x-circle me-1"></i>Reject
                    </button>
                    <button type="button" class="btn btn-dark approve-btn" id="modalApproveBtn">
                        <i class="bi bi-check-circle me-1"></i>Approve
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

</div>

<!-- Approve Confirmation Modal -->
<div class="modal fade" id="approveModal" tabindex="-1" aria-labelledby="approveModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header border-bottom-0 pb-0">
                <h5 class="modal-title fw-bold" id="approveModalLabel">Confirm Approval</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body pt-2">
                <p class="text-muted mb-0">Are you sure you want to approve this item? This will make it live on the storefront immediately.</p>
                <input type="hidden" id="approveClothId">
            </div>
            <div class="modal-footer border-top-0 pt-0">
                <button type="button" class="btn btn-link text-decoration-none text-muted" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-dark px-4" id="confirmApprove">
                    Confirm Approve
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Reject Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1" aria-labelledby="rejectModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header border-bottom-0 pb-0">
                <h5 class="modal-title fw-bold text-danger" id="rejectModalLabel">Reject Item</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body pt-2">
                <form id="rejectForm">
                    <div class="mb-3">
                        <label for="rejectReason" class="form-label fw-bold small text-uppercase">Rejection Reason</label>
                        <textarea class="form-control" id="rejectReason" name="reject_reason" rows="4" required placeholder="Describe why this item is being rejected..."></textarea>
                        <div class="form-text small">This feedback will be sent to the owner.</div>
                    </div>
                    <input type="hidden" id="rejectClothId" name="cloth_id">
                </form>
            </div>
            <div class="modal-footer border-top-0 pt-0">
                <button type="button" class="btn btn-link text-decoration-none text-muted" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger px-4" id="confirmReject">
                    Reject Item
                </button>
            </div>
        </div>
    </div>
</div>

<!-- View Reason Modal -->
<div class="modal fade" id="reasonModal" tabindex="-1" aria-labelledby="reasonModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header border-bottom-0 pb-0">
                <h5 class="modal-title fw-bold" id="reasonModalLabel">Rejection History</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body pt-2">
                <div id="reasonList" class="small text-muted"></div>
            </div>
            <div class="modal-footer border-top-0 pt-0">
                <button type="button" class="btn btn-dark btn-sm px-3" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const defaultImageUrl = "{{ asset('images/1.jpg') }}";
$(function() {
    const approvalState = {
        data: [],
        page: 1,
        perPage: 20,
    };

    function loadClothes() {
        let status = $('#filterStatus').val();
        let url = "{{ route('clothes.fetch') }}";
        if (status) {
            url += `?status=${status}`;
        }

        $.get(url, function(response) {
            approvalState.data = response.clothes || [];
            approvalState.page = 1;
            updateApprovalStats(approvalState.data);
            populateCategories(approvalState.data);
            renderClothes();
        }).fail(function() {
            $('#clothesTable tbody').html('<tr><td colspan="11" class="text-center text-danger">Error loading clothes data</td></tr>');
            $('#clothesPagination').addClass('d-none').empty();
        });
    }
    loadClothes();

    $('#filterStatus').on('change', loadClothes);
    $('#filterCategory').on('change', function() {
        approvalState.page = 1;
        renderClothes();
    });
    $('#clothSearchInput').on('input', function() {
        approvalState.page = 1;
        renderClothes();
    });
    $('#resetFilters').on('click', function() {
        $('#filterStatus').val('');
        $('#filterCategory').val('');
        $('#clothSearchInput').val('');
        loadClothes();
    });

    function populateCategories(clothes) {
        if (!clothes || !clothes.length) return;
        let categories = [...new Set(clothes.map(item => item.category).filter(Boolean))].sort();
        let $select = $('#filterCategory');
        let currentVal = $select.val();
        $select.html('<option value="">All</option>');
        categories.forEach(cat => {
            $select.append(`<option value="${cat}">${cat}</option>`);
        });
        if (currentVal && categories.includes(currentVal)) {
            $select.val(currentVal);
        }
    }

    function updateApprovalStats(clothes) {
        if (!clothes || !clothes.length) {
            $('#pendingCount, #approvedCount, #reapprovalCount, #rejectedCount, #totalCount').text('0');
            $('#totalRentSum').text('₹0');
            $('#totalDepositSum').text('₹0');
            return;
        }

        let pendingCount = 0;
        let approvedCount = 0;
        let reapprovalCount = 0;
        let rejectedCount = 0;
        let totalRent = 0;
        let totalDeposit = 0;

        clothes.forEach(function(cloth) {
            totalRent += parseFloat(cloth.rent_price ?? 0);
            totalDeposit += parseFloat(cloth.security_deposit ?? 0);

            if (cloth.is_approved === 1 || cloth.is_approved === true) {
                approvedCount++;
            } else if (cloth.is_approved === -1) {
                rejectedCount++;
            } else if (cloth.is_approved === null) {
                if (cloth.resubmission_count > 0) {
                    reapprovalCount++;
                } else {
                    pendingCount++;
                }
            }
        });

        $('#pendingCount').text(pendingCount);
        $('#approvedCount').text(approvedCount);
        $('#reapprovalCount').text(reapprovalCount);
        $('#rejectedCount').text(rejectedCount);
        $('#totalCount').text(clothes.length);
        $('#totalRentSum').text(`₹${Math.round(totalRent).toLocaleString('en-IN')}`);
        $('#totalDepositSum').text(`₹${Math.round(totalDeposit).toLocaleString('en-IN')}`);
    }

    function renderClothes() {
        let clothes = approvalState.data || [];

        // Client-side filtering
        const catFilter = $('#filterCategory').val();
        const searchFilter = $('#clothSearchInput').val().toLowerCase().trim();

        if (catFilter) {
            clothes = clothes.filter(c => c.category === catFilter);
        }
        if (searchFilter) {
            clothes = clothes.filter(c => {
                const title = (c.title || '').toLowerCase();
                const owner = (c.user && c.user.name ? c.user.name : '').toLowerCase();
                const cat = (c.category || '').toLowerCase();
                return title.includes(searchFilter) || owner.includes(searchFilter) || cat.includes(searchFilter);
            });
        }

        if (!clothes.length) {
            $('#clothesTable tbody').html('<tr><td colspan="11" class="text-center">No clothes found</td></tr>');
            $('#clothesPagination').addClass('d-none').empty();
            return;
        }

        const totalPages = Math.max(1, Math.ceil(clothes.length / approvalState.perPage));
        if (approvalState.page > totalPages) {
            approvalState.page = totalPages;
        }

        const start = (approvalState.page - 1) * approvalState.perPage;
        const pageItems = clothes.slice(start, start + approvalState.perPage);

        const rows = pageItems.map(function(cloth) {


            let statusBadge = '';
            let approveDisabled = false;
            let rejectDisabled = false;

            if (cloth.is_approved === 1 || cloth.is_approved === true) {
                statusBadge = '<span class="badge bg-success">Approved</span>';
                approveDisabled = true;
                rejectDisabled = true;
            } else if (cloth.is_approved === -1) {
                statusBadge = '<span class="badge bg-danger">Rejected</span>';
                approveDisabled = true; // Cannot approve rejected items until resubmission
            } else if (cloth.is_approved === null) {
                if (cloth.resubmission_count > 0) {
                    statusBadge = '<span class="badge bg-info">Re-approval</span>';
                } else {
                    statusBadge = '<span class="badge bg-warning text-dark">Pending</span>';
                }
            }

            
            // Accessor for Display Prices
            const rentPrice = cloth.rent_price ? `<div><small class="text-muted">Rent:</small> ₹${cloth.rent_price}</div>` : '';
            const buyPrice = cloth.base_selling_price ? `<div><small class="text-primary">Buy:</small> ₹${cloth.base_selling_price}</div>` : '';
            
            const buyerRent = cloth.buyer_see_rent ? `<div><small class="text-muted">Rent:</small> ₹${cloth.buyer_see_rent}</div>` : '';
            const buyerBuy = cloth.display_selling_price ? `<div><small class="text-primary">Buy:</small> ₹${cloth.display_selling_price}</div>` : '';
            
            const sellerRent = cloth.seller_see_rent ? `<div><small class="text-muted">Rent:</small> ₹${cloth.seller_see_rent}</div>` : '';
            const sellerBuy = cloth.seller_selling_price ? `<div><small class="text-primary">Buy:</small> ₹${cloth.seller_selling_price}</div>` : '';

            return `<tr>
                <td>${cloth.title}</td>
                <td>${cloth.category}</td>
                <td>${cloth.user ? cloth.user.name : ''}</td>
                <td>${cloth.gender}</td>
                <td>${cloth.size}</td>
                <td>${cloth.condition}</td>
                <td>${rentPrice}${buyPrice}</td>
                <td class="fw-bold">${buyerRent}${buyerBuy}</td>
                <td class="fw-bold">${sellerRent}${sellerBuy}</td>
                <td>₹${cloth.security_deposit}</td>
                    <td>${statusBadge}</td>
                    <td class="text-center">
                        <button class="btn btn-success btn-icon approve-btn" data-id="${cloth.id}" ${approveDisabled ? 'disabled' : ''} title="Approve">
                            <i class="bi bi-check"></i>
                        </button>
                        <button class="btn btn-danger btn-icon reject-btn" data-id="${cloth.id}" ${rejectDisabled ? 'disabled' : ''} title="Reject">
                            <i class="bi bi-x"></i>
                        </button>
                        <button class="btn btn-outline-secondary btn-icon view-reason-btn" data-id="${cloth.id}" title="View reason">
                            <i class="bi bi-eye"></i>
                        </button>
                        <button class="btn btn-info btn-icon view-details-btn" data-id="${cloth.id}" title="View Details">
                            <i class="bi bi-info-circle"></i>
                        </button>
                    </td>
            </tr>`;
        }).join('');

        $('#clothesTable tbody').html(rows);
        renderClothesPagination(totalPages);
    }

    function renderClothesPagination(totalPages) {
        const $pager = $('#clothesPagination');
        const totalEntries = approvalState.data.length;
        
        if (totalEntries === 0) {
            $pager.addClass('d-none').empty();
            return;
        }

        const startEntry = ((approvalState.page - 1) * approvalState.perPage) + 1;
        const endEntry = Math.min(approvalState.page * approvalState.perPage, totalEntries);

        $pager.removeClass('d-none').html(`
            <span class="text-muted small fw-bold">
                Showing ${startEntry}-${endEntry} of ${totalEntries}
            </span>
            <div class="btn-group shadow-sm">
                <button class="btn btn-sm btn-outline-dark border-0 bg-white clothes-prev" ${approvalState.page === 1 ? 'disabled' : ''} style="width: 32px;">
                    <i class="bi bi-chevron-left"></i>
                </button>
                <span class="btn btn-sm btn-outline-dark border-0 bg-white disabled px-3 fw-bold">
                    ${approvalState.page} / ${totalPages}
                </span>
                <button class="btn btn-sm btn-outline-dark border-0 bg-white clothes-next" ${approvalState.page === totalPages ? 'disabled' : ''} style="width: 32px;">
                    <i class="bi bi-chevron-right"></i>
                </button>
            </div>
        `);

        $pager.off('click').on('click', '.clothes-prev', function() {
            if (approvalState.page > 1) {
                approvalState.page--;
                renderClothes();
                window.scrollTo(0, 0); 
            }
        }).on('click', '.clothes-next', function() {
            if (approvalState.page < totalPages) {
                approvalState.page++;
                renderClothes();
                window.scrollTo(0, 0); 
            }
        });
    }

    $(document).on('click', '.approve-btn', function() {
        let id = $(this).data('id');
        let $row = $(this).closest('tr');
        // If triggered from modal, $row might not exist, but we just need ID.
        
        // Prevent approving already approved items (though buttons should be disabled/hidden)
        
        $('#approveClothId').val(id);
        $('#approveModal').modal('show');
    });

    $('#confirmApprove').click(function() {
        let clothId = $('#approveClothId').val();
        let $btn = $('#confirmApprove');
        $btn.prop('disabled', true).html('<i class="bi bi-hourglass-split me-1"></i>Processing...');
        
        // Also disable original buttons in table if possible to prevent double clicks, 
        // but modal covers them.
        
        $.post(`{{ url('/admin/clothes/approve') }}/${clothId}`, {_token: '{{ csrf_token() }}'}, function(res) {
            if (res.success) {
                $('#approveModal').modal('hide');
                // Also close detail modal if open
                $('#detailModal').modal('hide');
                
                loadClothes();
                showAlert('Item approved successfully!', 'success');
            }
        }).fail(function() {
            showAlert('Failed to approve item. Please try again.', 'danger');
        }).always(function() {
             $btn.prop('disabled', false).html('<i class="bi bi-check-lg me-1"></i>Confirm Approve');
        });
    });

    $(document).on('click', '.reject-btn', function() {
        let id = $(this).data('id');
        let $row = $(this).closest('tr');
        let statusCell = $row.find('td:eq(9)').text().trim();

        if (statusCell === 'Approved') {
            showAlert('Cannot reject an approved item. Please approve it first.', 'warning');
            return;
        }

        $('#rejectClothId').val(id);
        $('#rejectReason').val('');
        $('#rejectModal').modal('show');
    });

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

    // View Details Modal Trigger
    $(document).on('click', '.view-details-btn', function() {
        const id = $(this).data('id');
        const cloth = approvalState.data.find(c => c.id == id);
        
        if (!cloth) return;

        // Populate Basic Info
        $('#detailTitle').text(cloth.title || '-');
        $('#detailBrand').text(cloth.brand || '-');
        $('#detailCategory').text(cloth.category || '-');
        $('#detailGender').text(cloth.gender || '-');
        $('#detailSize').text(cloth.size || '-');
        $('#detailFitType').text(cloth.fit_type || '-');
        $('#detailCondition').text(cloth.condition || '-');
        $('#detailFabric').text(cloth.fabric || '-');
        $('#detailColor').text(cloth.color || '-');
        $('#detailCleaned').text(cloth.is_cleaned ? 'Yes' : 'No');
        
        // Populate Measurements
        $('#detailChest').text(cloth.chest_bust || '-');
        $('#detailWaist').text(cloth.waist || '-');
        $('#detailLength').text(cloth.length || '-');
        $('#detailShoulder').text(cloth.shoulder || '-');
        $('#detailSleeve').text(cloth.sleeve_length || '-');
        
        // Populate Financials
        $('#detailRent').text(cloth.base_rent ? '₹' + cloth.base_rent : '-');
        $('#detailBuyerSeeRent').text(cloth.buyer_see_rent ? '₹' + cloth.buyer_see_rent : '-');
        $('#detailSellerSeeRent').text(cloth.seller_see_rent ? '₹' + cloth.seller_see_rent : '-');
        $('#detailDeposit').text(cloth.security_deposit ? '₹' + cloth.security_deposit : '-');
        
        // Purchase Financials
        $('#detailBasePrice').text(cloth.base_selling_price ? '₹' + cloth.base_selling_price : 'N/A');
        $('#detailBuyerSeePrice').text(cloth.display_selling_price ? '₹' + cloth.display_selling_price : 'N/A');
        $('#detailSellerSeePrice').text(cloth.seller_selling_price ? '₹' + cloth.seller_selling_price : 'N/A');
        
        // Defects
        $('#detailDefects').text(cloth.defects || 'None');
        
        // Owner
        if (cloth.user) {
            $('#detailOwnerName').text(cloth.user.name || '-');
            $('#detailOwnerEmail').text(cloth.user.email || '-');
            $('#detailOwnerPhone').text(cloth.user.phone || '-');
            
            let bio = [];
            if (cloth.user.gender) bio.push(cloth.user.gender);
            if (cloth.user.age) bio.push(cloth.user.age + ' yrs');
            $('#detailOwnerBio').text(bio.length ? bio.join(', ') : '-');
            
            let addr = [];
            if (cloth.user.address) addr.push(cloth.user.address);
            if (cloth.user.city) addr.push(cloth.user.city);
            $('#detailOwnerAddress').text(addr.length ? addr.join(', ') : '-');
            
            $('#detailOwnerGST').text(cloth.user.gstin || '-');
            
            if (cloth.user.profile_image) {
                $('#detailUserImage').attr('src', '/storage/' + cloth.user.profile_image);
            } else {
                $('#detailUserImage').attr('src', 'https://ui-avatars.com/api/?name=' + encodeURIComponent(cloth.user.name || 'User') + '&background=random');
            }
        } else {
            $('#detailOwnerName').text('Unknown');
            $('#detailOwnerEmail').text('-');
            $('#detailOwnerPhone').text('-');
            $('#detailOwnerBio').text('-');
            $('#detailOwnerAddress').text('-');
            $('#detailOwnerGST').text('-');
            $('#detailUserImage').attr('src', 'https://ui-avatars.com/api/?name=Unknown&background=random');
        }

        // Status & Buttons
        $('#modalApproveBtn').data('id', cloth.id).prop('disabled', false).html('<i class="bi bi-check-circle me-1"></i>Approve');
        $('#modalRejectBtn').data('id', cloth.id).prop('disabled', false).html('<i class="bi bi-x-circle me-1"></i>Reject');

        if (cloth.is_approved === 1 || cloth.is_approved === true) {
            $('#detailStatus').html('<span class="badge bg-success">Approved</span>');
            $('#modalApproveBtn').hide();
            $('#modalRejectBtn').hide();
        } else if (cloth.is_approved === -1) {
             $('#detailStatus').html('<span class="badge bg-danger">Rejected</span>');
             $('#modalApproveBtn').show().prop('disabled', true).html('<i class="bi bi-clock-history me-1"></i>Wait for Resubmission');
             $('#modalRejectBtn').hide();
        } else {
            $('#modalApproveBtn').show();
            $('#modalRejectBtn').show();
            if (cloth.resubmission_count > 0) {
                $('#detailStatus').html('<span class="badge bg-info">Re-approval</span>');
            } else {
                $('#detailStatus').html('<span class="badge bg-warning text-dark">Pending</span>');
            }
        }

        // Images
        let imagesHtml = '';
        if (cloth.images && cloth.images.length > 0) {
            imagesHtml = '<div class="row g-2">';
            cloth.images.forEach(img => {
                imagesHtml += `<div class="col-4">
                    <a href="/storage/${img.image_path}" target="_blank">
                        <img src="/storage/${img.image_path}" class="img-fluid rounded border w-100" style="height: 100px; object-fit: cover;">
                    </a>
                </div>`;
            });
            imagesHtml += '</div>';
        } else {
            imagesHtml = '<p class="text-muted fst-italic">No images uploaded.</p>';
        }
        $('#detailImages').html(imagesHtml);

        $('#detailModal').modal('show');
    });

    $('#rejectReason').on('input', function() {
        $(this).removeClass('is-invalid');
    });

    function showAlert(message, type) {
        const alertHtml = `
            <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        `;
        $('#alertBox').html(alertHtml);
        setTimeout(function() {
            $('.alert').fadeOut();
        }, 5000);
    }
});
</script>
@endpush

