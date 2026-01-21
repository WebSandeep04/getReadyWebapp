@extends('admin.layouts.app')

@section('title', 'Admin Dashboard')
@section('page_title', 'Admin Dashboard')

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
.dashboard-hero { background:linear-gradient(135deg,#4338ca,#6366f1); border-radius:18px; padding:1.5rem; color:#fff; margin-bottom:1.5rem; box-shadow:0 25px 45px rgba(67,56,202,.25); }
.dashboard-hero__title { font-size:1.25rem; font-weight:600; }
.dashboard-hero__subtitle { margin:0; opacity:.9; }
.stat-card { position:relative; border-radius:18px; padding:1.2rem; color:#fff; overflow:hidden; box-shadow:0 18px 30px rgba(15,23,42,.18); }
.stat-card__label { text-transform:uppercase; letter-spacing:.08em; font-size:.8rem; opacity:.85; }
.stat-card__value { font-size:2rem; font-weight:600; margin:.35rem 0; }
.stat-card__icon { position:absolute; top:1rem; right:1rem; font-size:2.5rem; opacity:.25; }
.stat-pending { background:linear-gradient(135deg,#fcd34d,#f97316); }
.stat-approved { background:linear-gradient(135deg,#34d399,#059669); }
.stat-reapproval { background:linear-gradient(135deg,#60a5fa,#2563eb); }
.stat-rejected { background:linear-gradient(135deg,#fb7185,#dc2626); }
.quick-action-card { border-radius:14px; background:#fff; border:1px solid #e0e7ff; padding:1rem 1.25rem; box-shadow:0 16px 30px rgba(15,23,42,.08); height:100%; display:flex; flex-direction:column; }
.quick-action-card .action-title { font-weight:600; font-size:1rem; color:#111827; }
.quick-action-card p { flex:1; color:#6b7280; font-size:.9rem; margin:.4rem 0 .8rem; }
.status-legend { background:#f8fafc; border-radius:16px; padding:1rem; border:1px solid #e5e7eb; box-shadow:inset 0 0 30px rgba(79,70,229,.04); }
.legend-pill { display:flex; align-items:center; gap:.5rem; padding:.4rem .6rem; border-radius:999px; margin-bottom:.4rem; font-size:.85rem; font-weight:500; }
.admin-table th,.admin-table td { font-size:.85rem; padding:.4rem .5rem; }
.admin-table .btn-icon { width:30px; height:30px; border-radius:50%; display:inline-flex; align-items:center; justify-content:center; margin-right:.2rem; }
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
                <div class="stat-icon"><i class="bi bi-hourglass-split"></i></div>
                <div class="stat-card__label">Pending</div>
                <div class="stat-card__value" id="pendingCount">-</div>
                <small>Awaiting review</small>
            </div>
        </div>
        <div class="col-lg-3 col-sm-6">
            <div class="stat-card stat-approved">
                <div class="stat-icon"><i class="bi bi-check2-circle"></i></div>
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
                <div class="stat-card__label">Rejected</div>
                <div class="stat-card__value" id="rejectedCount">-</div>
                <small>Require revisions</small>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-lg-3 col-md-6">
            <div class="quick-action-card">
                <div class="action-title">Invite a Seller</div>
                <p>Grow your inventory by onboarding new verified owners.</p>
                <a href="{{ route('user.index') }}" class="btn btn-outline-primary btn-sm">Manage Users</a>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="quick-action-card">
                <div class="action-title">Curate Categories</div>
                <p>Keep categories and filters fresh for better discovery.</p>
                <a href="{{ route('categories.index') }}" class="btn btn-outline-secondary btn-sm">Update Categories</a>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="quick-action-card">
                <div class="action-title">Hero &amp; Banners</div>
                <p>Refresh homepage visuals for campaigns and seasons.</p>
                <a href="{{ route('admin.frontend') }}" class="btn btn-outline-success btn-sm">Edit Frontend</a>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="status-legend">
                <div class="fw-semibold mb-3">Status Legend</div>
                <div class="legend-pill" style="background:#FEF3C7;"><span class="badge bg-warning">&nbsp;</span> Pending review</div>
                <div class="legend-pill" style="background:#DCFCE7;"><span class="badge bg-success">&nbsp;</span> Approved</div>
                <div class="legend-pill" style="background:#DBEAFE;"><span class="badge bg-info">&nbsp;</span> Re-approval</div>
                <div class="legend-pill" style="background:#FEE2E2;"><span class="badge bg-danger">&nbsp;</span> Rejected</div>
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
                    <select id="statusFilter" class="form-select form-select-sm me-2" style="width: auto;">
                        <option value="">All Statuses</option>
                        <option value="pending">Pending</option>
                        <option value="approved">Approved</option>
                        <option value="re-approval">Re-approval</option>
                        <option value="rejected">Rejected</option>
                    </select>
                    <button class="btn btn-outline-light btn-sm" onclick="loadClothes()">
                        <i class="bi bi-arrow-clockwise"></i> Refresh
                    </button>
                </div>
            </div>
        </div>
        <div class="card-body">
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
                <tbody>
                    <!-- Data loaded by jQuery AJAX -->
                </tbody>
            </table>
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
        
        $.get(url, function(clothes) {
            let rows = '';
            let pendingCount = 0;
            let approvedCount = 0;
            let reapprovalCount = 0;
            let rejectedCount = 0;
            let totalRent = 0;
            let totalDeposit = 0;
            const totalCount = clothes ? clothes.length : 0;
            
            if (!clothes || clothes.length === 0) {
                $('#clothesTable tbody').html('<tr><td colspan="11" class="text-center">No clothes found</td></tr>');
                $('#totalCount').text('0');
                $('#totalRentSum').text('₹0');
                $('#totalDepositSum').text('₹0');
                return;
            }
            
            clothes.forEach(function(cloth) {
                totalRent += parseFloat(cloth.rent_price ?? 0);
                totalDeposit += parseFloat(cloth.security_deposit ?? 0);

                let image = cloth.images && cloth.images.length > 0
                    ? `<img src='/storage/${cloth.images[0].image_path}' alt='${cloth.title}' style='width:60px;height:60px;object-fit:cover;border-radius:6px;'>`
                    : `<img src='${defaultImageUrl}' alt='${cloth.title}' style='width:60px;height:60px;object-fit:cover;border-radius:6px;'>`;
                
                // Determine status badge and button states
                let status = '';
                let approveDisabled = false;
                let rejectDisabled = false;
                
                if (cloth.is_approved === 1 || cloth.is_approved === true) {
                    status = '<span class="badge bg-success">Approved</span>';
                    approveDisabled = true;
                    rejectDisabled = true; // Disable reject for approved items
                    approvedCount++;
                } else if (cloth.is_approved === 0 || cloth.is_approved === false) {
                    status = '<span class="badge bg-danger">Rejected</span>';
                    approveDisabled = false;
                    rejectDisabled = false; // Allow rejecting rejected items (for re-approval)
                    rejectedCount++;
                } else if (cloth.is_approved === null) {
                    // Check if this is a resubmission using resubmission_count
                    if (cloth.resubmission_count > 0) {
                        status = '<span class="badge bg-info">Re-approval</span>';
                        reapprovalCount++;
                    } else {
                        status = '<span class="badge bg-warning text-dark">Pending</span>';
                        pendingCount++;
                    }
                    approveDisabled = false;
                    rejectDisabled = false; // Both buttons enabled for pending/re-approval
                }
                
                rows += `<tr>
                    <td>${image}</td>
                    <td>${cloth.title}</td>
                    <td>${cloth.category}</td>
                    <td>${cloth.user ? cloth.user.name : ''}</td>
                    <td>${cloth.gender}</td>
                    <td>${cloth.size}</td>
                    <td>${cloth.condition}</td>
                    <td>₹${cloth.rent_price}</td>
                    <td>₹${cloth.security_deposit}</td>
                    <td>${status}</td>
                    <td class="text-center">
                        <button class="btn btn-success btn-icon approve-btn" data-id="${cloth.id}" ${approveDisabled ? 'disabled' : ''} title="${approveDisabled ? 'Approved' : 'Approve'}">
                            <i class="bi bi-check"></i>
                        </button>
                        <button class="btn btn-danger btn-icon reject-btn" data-id="${cloth.id}" ${rejectDisabled ? 'disabled' : ''} title="${rejectDisabled ? 'Rejected' : 'Reject'}">
                            <i class="bi bi-x"></i>
                        </button>
                        <button class="btn btn-outline-secondary btn-icon view-reason-btn" data-id="${cloth.id}" title="View Reason">
                            <i class="bi bi-eye"></i>
                        </button>
                    </td>
                </tr>`;
            });
            
            $('#clothesTable tbody').html(rows);
            
            // Update statistics
            $('#pendingCount').text(pendingCount);
            $('#approvedCount').text(approvedCount);
            $('#reapprovalCount').text(reapprovalCount);
            $('#rejectedCount').text(rejectedCount);
            $('#totalCount').text(totalCount);
            $('#totalRentSum').text(`₹${Math.round(totalRent).toLocaleString('en-IN')}`);
            $('#totalDepositSum').text(`₹${Math.round(totalDeposit).toLocaleString('en-IN')}`);
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
});
</script>
@endpush
