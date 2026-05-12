@extends('admin.layouts.app')

@section('title', 'Role Master')
@section('page_title', 'Role Master')

@section('content')
<div class="container-fluid p-0">
    <div class="row g-4">
        <!-- Roles List Sidebar -->
        <div class="col-lg-4">
            <div class="card h-100 shadow-sm border-0">
                <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-shield-lock me-2"></i>Select Role</h6>
                    <button class="btn btn-sm btn-dark px-2 py-0" style="font-size: 1.2rem; line-height: 1;" data-bs-toggle="modal" data-bs-target="#addNewRoleModal" title="Add New Role">
                        <i class="bi bi-plus"></i>
                    </button>
                </div>
                <div class="list-group list-group-flush" id="roleSelectorList">
                    @foreach($roles as $role)
                        <button type="button" 
                                class="list-group-item list-group-item-action border-0 py-3 role-item" 
                                id="role-item-{{ $role->id }}"
                                data-id="{{ $role->id }}" 
                                data-name="{{ $role->name }}">
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="text-capitalize fw-semibold">{{ $role->name }}</span>
                                <i class="bi bi-chevron-right small text-muted"></i>
                            </div>
                        </button>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Permissions Grid -->
        <div class="col-lg-8">
            <div class="card h-100 shadow-sm border-0 d-none" id="permissionsCard">
                <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-bold text-dark">
                        Permissions for: <span id="activeRoleName" class="text-capitalize text-primary"></span>
                    </h6>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="selectAllPermissions">
                        <label class="form-check-label small fw-bold text-muted text-uppercase" for="selectAllPermissions">
                            Select All
                        </label>
                    </div>
                </div>
                <div class="card-body p-4">
                    <form id="roleMasterForm">
                        @csrf
                        <input type="hidden" name="role_id" id="sourceRoleId">
                        
                        @foreach($permissions as $module => $modulePermissions)
                        <div class="mb-5">
                            <h6 class="text-uppercase small fw-bold text-muted border-bottom pb-2 mb-3">
                                <i class="bi bi-folder2-open me-2"></i>{{ $module }}
                            </h6>
                            <div class="row g-3">
                                @foreach($modulePermissions as $perm)
                                <div class="col-md-6 col-xl-4">
                                    <div class="p-3 border rounded bg-light-subtle h-100 permission-item">
                                        <div class="form-check">
                                            <input class="form-check-input permission-checkbox" 
                                                   type="checkbox" 
                                                   name="permission_ids[]" 
                                                   value="{{ $perm->id }}" 
                                                   id="perm_{{ $perm->id }}">
                                            <label class="form-check-label d-block ms-1" for="perm_{{ $perm->id }}">
                                                <span class="d-block fw-bold text-dark small">{{ $perm->label }}</span>
                                                <span class="d-block text-muted" style="font-size: 0.7rem;">{{ $perm->name }}</span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        @endforeach
                    </form>
                </div>
                <div class="card-footer bg-light border-top-0 py-3 text-end">
                    <button type="button" class="btn btn-dark px-5 py-2 fw-bold" id="saveRoleMaster">
                        Update Role Permissions
                    </button>
                </div>
            </div>

            <!-- Placeholder State -->
            <div class="card h-100 shadow-sm border-0 d-flex align-items-center justify-content-center py-5 bg-white" id="rolePlaceholder">
                <div class="text-center py-5">
                    <div class="mb-4 text-muted opacity-25">
                        <i class="bi bi-shield-check" style="font-size: 5rem;"></i>
                    </div>
                    <h5 class="fw-bold text-dark">Role Permission Master</h5>
                    <p class="text-muted small mx-auto" style="max-width: 300px;">
                        Select a role from the left sidebar to view and manage its access permissions across all system modules.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Add New Role Modal -->
    <div class="modal fade" id="addNewRoleModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 0 !important;">
                <div class="modal-header border-bottom-0 pb-0">
                    <h5 class="modal-title fw-bold">Create New Role</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body pt-4">
                    <form id="addNewRoleForm">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-uppercase text-muted">Role Name</label>
                            <input type="text" class="form-control" name="name" required placeholder="e.g. editor" style="border-radius: 0 !important;">
                        </div>
                    </form>
                </div>
                <div class="modal-footer border-top-0">
                    <button type="button" class="btn btn-sm btn-outline-secondary px-3" data-bs-dismiss="modal" style="border-radius: 0 !important;">Cancel</button>
                    <button type="button" class="btn btn-sm btn-dark px-4" id="submitNewRole" style="border-radius: 0 !important;">Create Role</button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
/* Global Monochrome Overrides */
*, ::before, ::after { border-radius: 0 !important; }

.role-item {
    transition: all 0.2s ease;
    border-left: 3px solid transparent !important;
}
.role-item:hover {
    background-color: #f8fafc;
    color: #000;
}
.role-item.active {
    background-color: #f1f5f9;
    color: #000;
    border-left-color: #000 !important;
}

.permission-item {
    transition: all 0.2s ease;
}
.permission-item:hover {
    background-color: #fff !important;
    border-color: #000 !important;
}
.permission-item.checked {
    background-color: #fff !important;
    border-color: #000 !important;
}

.form-check-input:checked {
    background-color: #000;
    border-color: #000;
}

.btn-dark {
    background-color: #000;
    border: none;
    transition: all 0.3s ease;
}
.btn-dark:hover {
    background-color: #222;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}
</style>
@endpush

@push('scripts')
<script>
$(function() {
    const csrf = $('meta[name="csrf-token"]').attr('content');
    const $roleList = $('.role-item');
    const $permissionsCard = $('#permissionsCard');
    const $placeholder = $('#rolePlaceholder');
    const $roleIdInput = $('#sourceRoleId');
    const $roleNameDisplay = $('#activeRoleName');
    const $checkboxes = $('.permission-checkbox');
    const $selectAll = $('#selectAllPermissions');

    // Select Role
    $(document).on('click', '.role-item', function() {
        const id = $(this).data('id');
        const name = $(this).data('name');

        $('.role-item').removeClass('active');
        $(this).addClass('active');

        $placeholder.addClass('d-none');
        $permissionsCard.removeClass('d-none');
        
        $roleIdInput.val(id);
        $roleNameDisplay.text(name);

        // Fetch current permissions
        $permissionsCard.addClass('opacity-50 pointer-events-none');
        $.getJSON(`/admin/role-master/permissions/${id}`)
            .done(data => {
                $checkboxes.prop('checked', false).closest('.permission-item').removeClass('checked');
                if (data.permission_ids) {
                    data.permission_ids.forEach(pid => {
                        $(`#perm_${pid}`).prop('checked', true).closest('.permission-item').addClass('checked');
                    });
                }
                updateSelectAllState();
            })
            .always(() => {
                $permissionsCard.removeClass('opacity-50 pointer-events-none');
            });
    });

    // Handle New Role Creation
    $('#submitNewRole').click(function() {
        const $btn = $(this);
        const originalText = $btn.text();
        const roleName = $('#addNewRoleForm input[name="name"]').val();

        if (!roleName) {
            if (window.showAlert) window.showAlert('Please enter a role name', 'danger');
            return;
        }

        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span>');

        $.ajax({
            url: "{{ route('role_master.store') }}",
            type: "POST",
            data: {
                _token: csrf,
                name: roleName
            }
        })
        .done(response => {
            if (response.success) {
                const role = response.role;
                // Add to list
                $('#roleSelectorList').append(`
                    <button type="button" 
                            class="list-group-item list-group-item-action border-0 py-3 role-item" 
                            id="role-item-${role.id}"
                            data-id="${role.id}" 
                            data-name="${role.name}">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-capitalize fw-semibold">${role.name}</span>
                            <i class="bi bi-chevron-right small text-muted"></i>
                        </div>
                    </button>
                `);
                
                $('#addNewRoleModal').modal('hide');
                $('#addNewRoleForm')[0].reset();
                if (window.showAlert) window.showAlert('Role created successfully', 'success');
                
                // Automatically click the new role
                $(`#role-item-${role.id}`).click();
            }
        })
        .fail(xhr => {
            const error = xhr.responseJSON?.message || 'Failed to create role';
            if (window.showAlert) window.showAlert(error, 'danger');
        })
        .always(() => {
            $btn.prop('disabled', false).text(originalText);
        });
    });

    // Select All
    $selectAll.change(function() {
        const checked = $(this).prop('checked');
        $checkboxes.prop('checked', checked);
        if (checked) {
            $('.permission-item').addClass('checked');
        } else {
            $('.permission-item').removeClass('checked');
        }
    });

    // Individual Checkbox Click
    $checkboxes.change(function() {
        $(this).closest('.permission-item').toggleClass('checked', $(this).prop('checked'));
        updateSelectAllState();
    });

    function updateSelectAllState() {
        const total = $checkboxes.length;
        const checked = $checkboxes.filter(':checked').length;
        $selectAll.prop('checked', total === checked && total > 0);
    }

    // Save
    $('#saveRoleMaster').click(function() {
        const $btn = $(this);
        const originalText = $btn.text();
        
        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Saving Context...');

        const formData = $('#roleMasterForm').serializeArray();

        $.ajax({
            url: "{{ route('role_master.save') }}",
            type: "POST",
            data: formData,
        })
        .done(response => {
            if (window.showAlert) window.showAlert('Role permissions updated successfully', 'success');
        })
        .fail(xhr => {
            if (window.showAlert) window.showAlert('Failed to update permissions', 'danger');
        })
        .always(() => {
            $btn.prop('disabled', false).text(originalText);
        });
    });
});
</script>
@endpush
