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
        <div class="card-footer bg-white border-top-0 text-center py-2">
            <a href="{{ route('admin.cloth-approval') }}" class="btn btn-sm px-4" 
               style="background: #000; color: #fff; box-shadow: 0 4px 10px rgba(0,0,0,0.15);">
                View More <i class="bi bi-arrow-right ms-1"></i>
            </a>
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
            
            // Ensure sorted by ID DESC for "last 5"
            clothes.sort((a, b) => b.id - a.id);
            
            clothes.forEach(function(cloth, index) {
                totalRent += parseFloat(cloth.rent_price ?? 0);
                totalDeposit += parseFloat(cloth.security_deposit ?? 0);

                // Determine status badge and button states
                let statusBadge = '';
                let approveDisabled = false;
                let rejectDisabled = false;
                
                if (cloth.is_approved === 1 || cloth.is_approved === true) {
                    statusBadge = '<span class="badge bg-success">Approved</span>';
                    approveDisabled = true;
                    rejectDisabled = true;
                    approvedCount++;
                } else if (cloth.is_approved === -1) {
                    statusBadge = '<span class="badge bg-danger">Rejected</span>';
                    approveDisabled = false;
                    rejectDisabled = false;
                    rejectedCount++;
                } else if (cloth.is_approved === null) {
                    if (cloth.resubmission_count > 0) {
                        statusBadge = '<span class="badge bg-info">Re-approval</span>';
                        reapprovalCount++;
                    } else {
                        statusBadge = '<span class="badge bg-warning text-dark">Pending</span>';
                        pendingCount++;
                    }
                    approveDisabled = false;
                    rejectDisabled = false;
                }
                
                // Only show first 5 in the table rows
                if (index < 5) {
                    rows += `<tr>
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
                }
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
