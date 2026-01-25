@extends('admin.layouts.app')

@section('title', 'Cloth Approval Hub')
@section('page_title', 'Cloth Approval')

@push('styles')
<style>
.approve-btn:disabled,
.reject-btn:disabled { opacity:.6; cursor:not-allowed; }
.table img { border:2px solid #e2e8f0; border-radius:8px; transition:.2s; }
.table img:hover { border-color:#3b82f6; transform:scale(1.05); }
.table th { background:#f8fafc; border-bottom:2px solid #e5e7eb; font-weight:600; text-transform:uppercase; font-size:.75rem; letter-spacing:.08em; }
.table td { vertical-align:middle; }
.badge { font-size:.75rem; border-radius:999px; padding:.35rem .75rem; font-weight:600; }
.badge.bg-success { background:linear-gradient(135deg,#10b981,#059669); }
.badge.bg-danger { background:linear-gradient(135deg,#fb7185,#dc2626); }
.badge.bg-warning { background:linear-gradient(135deg,#fcd34d,#f59e0b); color:#78350f; }
.badge.bg-info { background:linear-gradient(135deg,#60a5fa,#2563eb); }
.approval-hero { background:linear-gradient(135deg,#0f172a,#312e81); border-radius:20px; padding:1.75rem; color:#fff; margin-bottom:1.75rem; box-shadow:0 25px 40px rgba(15,23,42,.35); }
.approval-hero__title { font-size:1.35rem; font-weight:600; }
.approval-hero__subtitle { margin:0; opacity:.9; }
.stat-card { border-radius:20px; padding:1.35rem; color:#fff; position:relative; overflow:hidden; box-shadow:0 18px 32px rgba(15,23,42,.18); }
.stat-card__label { text-transform:uppercase; letter-spacing:.08em; font-size:.8rem; opacity:.85; }
.stat-card__value { font-size:2.15rem; font-weight:600; margin:.35rem 0; }
.stat-card__icon { position:absolute; top:1rem; right:1rem; font-size:2.4rem; opacity:.2; }
.stat-pending { background:linear-gradient(135deg,#fcd34d,#f97316); }
.stat-approved { background:linear-gradient(135deg,#34d399,#059669); }
.stat-reapproval { background:linear-gradient(135deg,#60a5fa,#2563eb); }
.stat-rejected { background:linear-gradient(135deg,#fb7185,#dc2626); }
.status-legend { background:#f8fafc; border-radius:18px; padding:1.25rem; border:1px solid #e5e7eb; box-shadow:inset 0 0 30px rgba(79,70,229,.04); }
.legend-pill { display:flex; align-items:center; gap:.5rem; padding:.45rem .7rem; border-radius:999px; margin-bottom:.4rem; font-size:.85rem; font-weight:500; }
.admin-table th,.admin-table td { font-size:.85rem; padding:.45rem .55rem; }
.admin-table .btn-icon { width:32px; height:32px; border-radius:50%; display:inline-flex; align-items:center; justify-content:center; margin-right:.2rem; }
</style>
@endpush

@section('content')
<div class="container mt-4">
    <div class="approval-hero d-flex flex-column flex-lg-row justify-content-between align-items-lg-center">
        <div class="mb-3 mb-lg-0">
            <div class="approval-hero__title">Approval Queue</div>
            <p class="approval-hero__subtitle">Audit listings, capture rejection notes, and publish qualified looks without leaving this workspace.</p>
        </div>
        <div class="text-end">
            <div class="text-uppercase small text-white-50">Reviewer</div>
            <div class="fs-5 fw-semibold">{{ Auth::user()->name ?? 'Admin' }}</div>
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

    <div class="row g-3 mb-4 align-items-stretch">
        <div class="col-xl-9">
            <div class="card h-100">
                <div class="card-header bg-primary text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="bi bi-shield-check me-2"></i>Clothes Approval Management</h5>
                        <div class="d-flex align-items-center">
                            <select id="statusFilter" class="form-select form-select-sm me-2" style="width:auto;">
                                <option value="">All Statuses</option>
                                <option value="pending">Pending</option>
                                <option value="approved">Approved</option>
                                <option value="re-approval">Re-approval</option>
                                <option value="rejected">Rejected</option>
                            </select>
                            <button class="btn btn-outline-light btn-sm" onclick="loadClothes()">
                                <i class="bi bi-arrow-clockwise me-1"></i>Refresh
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover admin-table" id="clothesTable">
                            <thead class="table-light">
                                <tr>
                                    <th>Image</th>
                                    <th>Title</th>
                                    <th>Category</th>
                                    <th>Owner</th>
                                    <th>User Type</th>
                                    <th>Size</th>
                                    <th>Condition</th>
                                    <th>Rent (₹)</th>
                                    <th>Deposit (₹)</th>
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
        <div class="col-xl-3">
            <div class="status-legend h-100">
                <div class="fw-semibold mb-3">Status legend</div>
                <div class="legend-pill" style="background:#FEF3C7;"><span class="badge bg-warning">&nbsp;</span> Pending review</div>
                <div class="legend-pill" style="background:#DCFCE7;"><span class="badge bg-success">&nbsp;</span> Approved</div>
                <div class="legend-pill" style="background:#DBEAFE;"><span class="badge bg-info">&nbsp;</span> Re-approval</div>
                <div class="legend-pill" style="background:#FEE2E2;"><span class="badge bg-danger">&nbsp;</span> Rejected</div>
                <hr>
                <div class="text-uppercase small text-muted mb-1">Totals</div>
                <div class="d-flex justify-content-between"><span>Listings</span><strong id="totalCount">0</strong></div>
                <div class="d-flex justify-content-between"><span>Rent volume</span><strong id="totalRentSum">₹0</strong></div>
                <div class="d-flex justify-content-between"><span>Security held</span><strong id="totalDepositSum">₹0</strong></div>
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

                        <h6 class="text-muted fw-bold mb-3">Financials</h6>
                        <div class="row g-2 mb-3">
                             <div class="col-4">
                                <small class="text-muted d-block">Rent Price</small>
                                <span class="fw-bold text-success" id="detailRent">-</span>
                            </div>
                             <div class="col-4">
                                <small class="text-muted d-block">Security Deposit</small>
                                <span class="fw-bold text-primary" id="detailDeposit">-</span>
                            </div>
                             <div class="col-4">
                                <small class="text-muted d-block">Purchase Value</small>
                                <span class="fw-bold" id="detailPurchase">-</span>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <small class="text-muted d-block">Defects/Notes</small>
                            <p class="small bg-light p-2 rounded mb-0" id="detailDefects">-</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer justify-content-between">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <div>
                     <button type="button" class="btn btn-danger reject-btn me-2" id="modalRejectBtn">
                        <i class="bi bi-x-circle me-1"></i>Reject
                    </button>
                    <button type="button" class="btn btn-success approve-btn" id="modalApproveBtn">
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
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="approveModalLabel"><i class="bi bi-check-circle me-2"></i>Confirm Approval</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="mb-0">Are you sure you want to approve this item? This will make it live on the storefront immediately.</p>
                <input type="hidden" id="approveClothId">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success" id="confirmApprove"><i class="bi bi-check-lg me-1"></i>Confirm Approve</button>
            </div>
        </div>
    </div>
</div>

<!-- Reject Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1" aria-labelledby="rejectModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="rejectModalLabel"><i class="bi bi-exclamation-triangle me-2"></i>Reject Item</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="rejectForm">
                    <div class="mb-3">
                        <label for="rejectReason" class="form-label fw-bold">Rejection Reason *</label>
                        <textarea class="form-control" id="rejectReason" name="reject_reason" rows="4" required placeholder="Please provide a detailed reason for rejection..."></textarea>
                        <div class="form-text">This reason will be sent to the item owner.</div>
                    </div>
                    <input type="hidden" id="rejectClothId" name="cloth_id">
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><i class="bi bi-x-circle me-1"></i>Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmReject"><i class="bi bi-check-circle me-1"></i>Reject Item</button>
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
    const approvalState = {
        data: [],
        page: 1,
        perPage: 5,
    };

    function loadClothes() {
        let status = $('#statusFilter').val();
        let url = "{{ route('clothes.fetch') }}";
        if (status) {
            url += `?status=${status}`;
        }

        $.get(url, function(clothes) {
            approvalState.data = clothes || [];
            approvalState.page = 1;
            updateApprovalStats(approvalState.data);
            renderClothes();
        }).fail(function() {
            $('#clothesTable tbody').html('<tr><td colspan="11" class="text-center text-danger">Error loading clothes data</td></tr>');
            $('#clothesPagination').addClass('d-none').empty();
        });
    }
    loadClothes();

    $('#statusFilter').on('change', loadClothes);

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
        const clothes = approvalState.data || [];

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
            let image = cloth.images && cloth.images.length > 0
                ? `<img src='/storage/${cloth.images[0].image_path}' alt='${cloth.title}' style='width:60px;height:60px;object-fit:cover;border-radius:6px;'>`
                : `<img src='${defaultImageUrl}' alt='${cloth.title}' style='width:60px;height:60px;object-fit:cover;border-radius:6px;'>`;

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

            return `<tr>
                <td>${image}</td>
                <td>${cloth.title}</td>
                <td>${cloth.category}</td>
                <td>${cloth.user ? cloth.user.name : ''}</td>
                <td>${cloth.gender}</td>
                <td>${cloth.size}</td>
                <td>${cloth.condition}</td>
                <td>₹${cloth.rent_price}</td>
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
        if (approvalState.data.length <= approvalState.perPage) {
            $pager.addClass('d-none').empty();
            return;
        }

        $pager.removeClass('d-none').html(`
            <button class="btn btn-sm btn-outline-secondary clothes-prev" ${approvalState.page === 1 ? 'disabled' : ''}>
                <i class="bi bi-chevron-left"></i>
            </button>
            <span class="text-muted small">Page ${approvalState.page} of ${totalPages}</span>
            <button class="btn btn-sm btn-outline-secondary clothes-next" ${approvalState.page === totalPages ? 'disabled' : ''}>
                <i class="bi bi-chevron-right"></i>
            </button>
        `);

        $pager.off('click').on('click', '.clothes-prev', function() {
            if (approvalState.page > 1) {
                approvalState.page--;
                renderClothes();
            }
        }).on('click', '.clothes-next', function() {
            if (approvalState.page < totalPages) {
                approvalState.page++;
                renderClothes();
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
        $('#detailRent').text(cloth.rent_price ? '₹' + cloth.rent_price : '-');
        $('#detailDeposit').text(cloth.security_deposit ? '₹' + cloth.security_deposit : '-');
        $('#detailPurchase').text(cloth.purchase_value ? '₹' + cloth.purchase_value : 'Not for sale');
        
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

