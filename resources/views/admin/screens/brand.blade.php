@extends('admin.layouts.app')

@section('title', 'Manage Brands')
@section('page_title', 'Manage Brands')

@section('content')
<div class="container-fluid py-4 category-dashboard setup-crud" id="brands-crud">
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div>
                            <h4 class="mb-1 fw-bold">Brands</h4>
                            <p class="text-muted mb-0">Manage brand logos and information</p>
                        </div>
                        <button class="btn btn-gradient" data-bs-toggle="modal" data-bs-target="#brandsAddModal">
                            <i class="bi bi-plus-circle me-2"></i>New Brand
                        </button>
                    </div>

                    <div id="brandsList" class="row">
                        <div class="col-12 text-center py-5">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Modal -->
    <div class="modal fade modal-modern" id="brandsAddModal" tabindex="-1" aria-labelledby="brandsAddLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="brandsAddLabel">Add Brand</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="brandsAddForm" enctype="multipart/form-data">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="brandsAddInput" class="form-label">Brand Name *</label>
                            <input type="text" class="form-control" id="brandsAddInput" name="name" placeholder="Enter brand name" required>
                            <div class="invalid-feedback" id="brandsAddError"></div>
                        </div>
                        <div class="mb-3">
                            <label for="brandsAddLogo" class="form-label">Brand Logo</label>
                            <input type="file" class="form-control" id="brandsAddLogo" name="logo" accept="image/*">
                            <small class="text-muted">Max size: 2MB. Supported formats: JPEG, PNG, JPG, GIF, SVG</small>
                            <div id="brandsAddLogoPreview" class="mt-2" style="display:none;">
                                <img id="brandsAddLogoPreviewImg" src="" alt="Logo Preview" style="max-width: 150px; max-height: 150px; border-radius: 8px;">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-gradient text-uppercase fw-semibold px-4">Add Brand</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Modal -->
    <div class="modal fade modal-modern" id="brandsEditModal" tabindex="-1" aria-labelledby="brandsEditLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="brandsEditLabel">Edit Brand</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="brandsEditForm" enctype="multipart/form-data">
                    <div class="modal-body">
                        <input type="hidden" id="brandsEditId">
                        <div class="mb-3">
                            <label for="brandsEditInput" class="form-label">Brand Name *</label>
                            <input type="text" class="form-control" id="brandsEditInput" name="name" placeholder="Enter brand name" required>
                            <div class="invalid-feedback" id="brandsEditError"></div>
                        </div>
                        <div class="mb-3">
                            <label for="brandsEditLogo" class="form-label">Brand Logo</label>
                            <input type="file" class="form-control" id="brandsEditLogo" name="logo" accept="image/*">
                            <small class="text-muted">Max size: 2MB. Supported formats: JPEG, PNG, JPG, GIF, SVG</small>
                            <div id="brandsEditLogoPreview" class="mt-2">
                                <img id="brandsEditLogoPreviewImg" src="" alt="Current Logo" style="max-width: 150px; max-height: 150px; border-radius: 8px; display:none;">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-gradient text-uppercase fw-semibold px-4">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@include('admin.components.setup-crud-styles')

@push('scripts')
<script>
$(document).ready(function() {
    const brandsUrl = {
        json: "{{ route('brands.json') }}",
        store: "{{ route('brands.store') }}",
        update: "{{ url('/admin/brands') }}",
        delete: "{{ url('/admin/brands') }}",
    };

    // Load brands
    function loadBrands() {
        $.ajax({
            url: brandsUrl.json,
            method: 'GET',
            success: function(brands) {
                const brandsList = $('#brandsList');
                brandsList.empty();
                
                if (brands.length === 0) {
                    brandsList.html(`
                        <div class="col-12 text-center py-5">
                            <i class="bi bi-inbox" style="font-size: 4rem; color: #dee2e6;"></i>
                            <h5 class="mt-3 text-muted">No brands added</h5>
                            <p class="text-muted">Click "New Brand" to add your first brand.</p>
                        </div>
                    `);
                    return;
                }

                brands.forEach(function(brand) {
                    const brandCard = `
                        <div class="col-md-4 col-lg-3 mb-4" data-id="${brand.id}">
                            <div class="card h-100 shadow-sm">
                                <div class="card-body text-center">
                                    ${brand.logo ? `<img src="${brand.logo}" alt="${brand.name}" class="img-fluid mb-3" style="max-height: 100px; object-fit: contain;">` : '<div class="mb-3" style="height: 100px; display: flex; align-items: center; justify-content: center;"><i class="bi bi-image" style="font-size: 3rem; color: #dee2e6;"></i></div>'}
                                    <h6 class="mb-2">${brand.name}</h6>
                                    <div class="btn-group btn-group-sm">
                                        <button class="btn btn-outline-primary edit-brand" data-id="${brand.id}" data-name="${brand.name}" data-logo="${brand.logo || ''}">
                                            <i class="bi bi-pencil"></i> Edit
                                        </button>
                                        <button class="btn btn-outline-danger delete-brand" data-id="${brand.id}">
                                            <i class="bi bi-trash"></i> Delete
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                    brandsList.append(brandCard);
                });
            },
            error: function() {
                $('#brandsList').html('<div class="col-12 text-center py-5 text-danger">Error loading brands</div>');
            }
        });
    }

    // Logo preview for add form
    $('#brandsAddLogo').on('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                $('#brandsAddLogoPreviewImg').attr('src', e.target.result);
                $('#brandsAddLogoPreview').show();
            };
            reader.readAsDataURL(file);
        } else {
            $('#brandsAddLogoPreview').hide();
        }
    });

    // Logo preview for edit form
    $('#brandsEditLogo').on('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                $('#brandsEditLogoPreviewImg').attr('src', e.target.result).show();
            };
            reader.readAsDataURL(file);
        }
    });

    // Add brand form
    $('#brandsAddForm').on('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        const submitBtn = $(this).find('button[type="submit"]');
        
        submitBtn.prop('disabled', true).text('Adding...');
        $('#brandsAddError').text('').hide();

        $.ajax({
            url: brandsUrl.store,
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    $('#brandsAddModal').modal('hide');
                    $('#brandsAddForm')[0].reset();
                    $('#brandsAddLogoPreview').hide();
                    loadBrands();
                }
            },
            error: function(xhr) {
                const error = xhr.responseJSON;
                if (error && error.errors) {
                    const firstError = Object.values(error.errors)[0];
                    $('#brandsAddError').text(Array.isArray(firstError) ? firstError[0] : firstError).show();
                } else {
                    $('#brandsAddError').text(error?.message || 'Failed to add brand').show();
                }
            },
            complete: function() {
                submitBtn.prop('disabled', false).text('Add Brand');
            }
        });
    });

    // Edit brand
    $(document).on('click', '.edit-brand', function() {
        const id = $(this).data('id');
        const name = $(this).data('name');
        const logo = $(this).data('logo');
        
        $('#brandsEditId').val(id);
        $('#brandsEditInput').val(name);
        
        if (logo) {
            $('#brandsEditLogoPreviewImg').attr('src', logo).show();
        } else {
            $('#brandsEditLogoPreviewImg').hide();
        }
        
        $('#brandsEditModal').modal('show');
    });

    // Update brand form
    $('#brandsEditForm').on('submit', function(e) {
        e.preventDefault();
        const id = $('#brandsEditId').val();
        const formData = new FormData(this);
        const submitBtn = $(this).find('button[type="submit"]');
        
        submitBtn.prop('disabled', true).text('Saving...');
        $('#brandsEditError').text('').hide();

        formData.append('_method', 'PUT');
        
        $.ajax({
            url: brandsUrl.update + '/' + id,
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    $('#brandsEditModal').modal('hide');
                    $('#brandsEditForm')[0].reset();
                    loadBrands();
                }
            },
            error: function(xhr) {
                const error = xhr.responseJSON;
                if (error && error.errors) {
                    const firstError = Object.values(error.errors)[0];
                    $('#brandsEditError').text(Array.isArray(firstError) ? firstError[0] : firstError).show();
                } else {
                    $('#brandsEditError').text(error?.message || 'Failed to update brand').show();
                }
            },
            complete: function() {
                submitBtn.prop('disabled', false).text('Save Changes');
            }
        });
    });

    // Delete brand
    $(document).on('click', '.delete-brand', function() {
        const id = $(this).data('id');
        const brandName = $(this).closest('.card').find('h6').text();
        
        if (confirm(`Are you sure you want to delete "${brandName}"?`)) {
            $.ajax({
                url: brandsUrl.delete + '/' + id,
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                    'X-HTTP-Method-Override': 'DELETE'
                },
                success: function(response) {
                    if (response.success) {
                        loadBrands();
                    }
                },
                error: function() {
                    alert('Failed to delete brand');
                }
            });
        }
    });

    // Reset modals on close
    $('#brandsAddModal, #brandsEditModal').on('hidden.bs.modal', function() {
        $(this).find('form')[0].reset();
        $(this).find('.invalid-feedback').text('').hide();
        $('#brandsAddLogoPreview, #brandsEditLogoPreviewImg').hide();
    });

    // Initial load
    loadBrands();
});
</script>
@endpush
@endsection

