@php
    use Illuminate\Support\Str;

    $config = $config ?? [];

    $slug = $config['slug'] ?? 'resource';
    $singular = $config['singular'] ?? Str::headline(Str::singular($slug));
    $plural = $config['plural'] ?? Str::headline(Str::plural($singular));
    $badge = $config['badge'] ?? 'Setup Suite';
    $heroTitle = $config['hero_title'] ?? "Curate every {$singular}";
    $heroSubtitle = $config['hero_subtitle'] ?? "Organize, refresh, and orchestrate {$plural} in seconds.";
    $ctaLabel = $config['cta_label'] ?? "New {$singular}";
    $refreshLabel = $config['refresh_label'] ?? 'Refresh';
    $searchPlaceholder = $config['search_placeholder'] ?? "Search {$plural}...";
    $emptyTitle = $config['empty_title'] ?? "No {$plural} yet";
    $emptyDescription = $config['empty_description'] ?? "Click “{$ctaLabel}” to add your first {$singular}.";
    $total = $config['total'] ?? 0;
    $illustration = $config['empty_illustration'] ?? 'https://cdn.jsdelivr.net/gh/ux-illustrations/undraw/void.svg';
    $routes = $config['routes'] ?? [];

    $routes = array_merge([
        'json' => '#',
        'store' => '#',
        'update' => '#',
        'delete' => '#',
    ], $routes);

    $modal = $config['modal'] ?? [];
    $modal = array_merge([
        'add_title' => "Add {$singular}",
        'edit_title' => "Edit {$singular}",
        'field_label' => "{$singular} name",
        'field_placeholder' => "Enter {$singular} name",
    ], $modal);

    $clientConfig = [
        'slug' => $slug,
        'singular' => $singular,
        'plural' => $plural,
        'routes' => $routes,
        'modal' => $modal,
    ];
@endphp

<div class="container-fluid py-4 category-dashboard setup-crud" id="{{ $slug }}-crud" data-crud-config='@json($clientConfig)'>
    <div class="row g-4">
        <div class="col-12">
            <div class="glass-card hero-card d-flex flex-column flex-lg-row align-items-start align-items-lg-center justify-content-between p-4 p-lg-5">
                <div>
                    <p class="text-uppercase fw-semibold text-white-50 small mb-1">{{ $badge }}</p>
                    <h2 class="display-6 fw-bold text-white mb-3">{{ $heroTitle }}</h2>
                    <p class="text-white-50 mb-4 mb-lg-0">{{ $heroSubtitle }}</p>
                </div>
                    <div class="d-flex flex-column flex-sm-row gap-2">
                    <button class="btn btn-light btn-lg shadow-sm" data-bs-toggle="modal" data-bs-target="#{{ $slug }}AddModal">
                        <i class="bi bi-plus-circle me-2"></i>{{ $ctaLabel }}
                    </button>
                    <button class="btn btn-outline-light btn-lg shadow-sm" id="{{ $slug }}RefreshBtn">
                        <i class="bi bi-arrow-repeat me-2"></i>{{ $refreshLabel }}
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mt-1">
        <div class="col-12 col-xl-4">
            <div class="glass-card p-4 h-100">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <p class="text-uppercase text-muted small mb-0">Total {{ Str::lower($plural) }}</p>
                    <span class="badge rounded-pill bg-gradient text-white">Live</span>
                </div>
                <h1 class="display-4 fw-bold text-primary mb-3" id="{{ $slug }}Count">{{ number_format($total) }}</h1>
                <p class="text-muted mb-0">Every change updates instantly. Use the quick actions to keep your {{ Str::lower($plural) }} polished.</p>
            </div>
        </div>

        <div class="col-12 col-xl-8">
            <div class="glass-card p-4 h-100 d-flex flex-column">
                <div class="input-group shadow-sm mb-4">
                    <span class="input-group-text bg-white border-0 text-muted"><i class="bi bi-search"></i></span>
                    <input type="search" id="{{ $slug }}Search" class="form-control border-0" placeholder="{{ $searchPlaceholder }}">
                </div>
                <div class="table-responsive flex-grow-1">
                    <table class="table table-hover align-middle mb-0 modern-table" id="{{ $slug }}Table">
                        <thead>
                            <tr>
                                <th class="text-uppercase small fw-semibold text-muted">ID</th>
                                <th class="text-uppercase small fw-semibold text-muted">{{ $singular }} name</th>
                                <th class="text-uppercase small fw-semibold text-muted text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                    <div class="d-flex justify-content-between align-items-center mt-3 d-none" id="{{ $slug }}Pagination"></div>
                </div>
                <div class="text-center mt-4 d-none" id="{{ $slug }}EmptyState">
                    <img src="{{ $illustration }}" alt="Empty state" class="empty-illustration mb-3">
                    <h5 class="fw-semibold">{{ $emptyTitle }}</h5>
                    <p class="text-muted mb-3">{{ $emptyDescription }}</p>
                    <button class="btn btn-gradient" data-bs-toggle="modal" data-bs-target="#{{ $slug }}AddModal">{{ $ctaLabel }}</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Modal -->
    <div class="modal fade modal-modern" id="{{ $slug }}AddModal" tabindex="-1" aria-labelledby="{{ $slug }}AddLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="{{ $slug }}AddLabel">{{ $modal['add_title'] }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="{{ $slug }}AddForm">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="{{ $slug }}AddInput" class="form-label">{{ $modal['field_label'] }}</label>
                            <input type="text" class="form-control" id="{{ $slug }}AddInput" name="name" placeholder="{{ $modal['field_placeholder'] }}" required>
                            <div class="invalid-feedback" id="{{ $slug }}AddError"></div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-gradient text-uppercase fw-semibold px-4">Add</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Modal -->
    <div class="modal fade modal-modern" id="{{ $slug }}EditModal" tabindex="-1" aria-labelledby="{{ $slug }}EditLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="{{ $slug }}EditLabel">{{ $modal['edit_title'] }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="{{ $slug }}EditForm">
                    <div class="modal-body">
                        <input type="hidden" id="{{ $slug }}EditId">
                        <div class="mb-3">
                            <label for="{{ $slug }}EditInput" class="form-label">{{ $modal['field_label'] }}</label>
                            <input type="text" class="form-control" id="{{ $slug }}EditInput" name="name" placeholder="{{ $modal['field_placeholder'] }}" required>
                            <div class="invalid-feedback" id="{{ $slug }}EditError"></div>
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
    @once('setup-crud-script')
        <script>
            (function () {
                class SetupCrud {
                    constructor(config) {
                        this.config = config;
                        this.state = {
                            items: [],
                            search: '',
                            page: 1,
                            perPage: 5,
                        };
                        this.csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
                        this.cacheDom();
                        this.bindEvents();
                        this.fetch();
                    }

                    cacheDom() {
                        const slug = this.config.slug;
                        this.$table = $(`#${slug}Table`);
                        this.$tbody = this.$table.find('tbody');
                        this.$count = $(`#${slug}Count`);
                        this.$empty = $(`#${slug}EmptyState`);
                        this.$search = $(`#${slug}Search`);
                        this.$refresh = $(`#${slug}RefreshBtn`);
                        this.$pagination = $(`#${slug}Pagination`);
                        this.$addForm = $(`#${slug}AddForm`);
                        this.$addInput = $(`#${slug}AddInput`);
                        this.$addError = $(`#${slug}AddError`);
                        this.$editForm = $(`#${slug}EditForm`);
                        this.$editInput = $(`#${slug}EditInput`);
                        this.$editError = $(`#${slug}EditError`);
                        this.$editId = $(`#${slug}EditId`);
                        this.addModalEl = document.getElementById(`${slug}AddModal`);
                        this.editModalEl = document.getElementById(`${slug}EditModal`);
                    }

                    bindEvents() {
                        this.$search.on('input', () => {
                            this.state.search = this.$search.val().toLowerCase();
                            this.state.page = 1;
                            this.render();
                        });

                        this.$refresh.on('click', () => this.fetch(false));

                        this.$addForm.on('submit', (event) => {
                            event.preventDefault();
                            this.submitForm({
                                url: this.config.routes.store,
                                method: 'POST',
                                payload: { name: this.$addInput.val() },
                                $form: this.$addForm,
                                beforeSend: (btn) => btn.html('<span class="spinner-border spinner-border-sm me-2"></span>Saving').prop('disabled', true),
                                onSuccess: () => {
                                    bootstrap.Modal.getInstance(this.addModalEl)?.hide();
                                    this.$addForm[0].reset();
                                    this.$addInput.removeClass('is-invalid');
                                    this.$addError.hide();
                                    this.fetch(false);
                                },
                                onError: (message) => {
                                    this.$addError.text(message).show();
                                    this.$addInput.addClass('is-invalid');
                                },
                                onComplete: (btn) => btn.text('Add').prop('disabled', false),
                            });
                        });

                        $(this.addModalEl).on('hidden.bs.modal', () => {
                            this.$addForm[0].reset();
                            this.$addInput.removeClass('is-invalid');
                            this.$addError.hide();
                        });

                        this.$tbody.on('click', '.edit-btn', (event) => {
                            const $btn = $(event.currentTarget);
                            this.$editId.val($btn.data('id'));
                            this.$editInput.val($btn.data('name'));
                            this.$editError.hide();
                            bootstrap.Modal.getOrCreateInstance(this.editModalEl).show();
                        });

                        this.$editForm.on('submit', (event) => {
                            event.preventDefault();
                            const id = this.$editId.val();
                            this.submitForm({
                                url: `${this.config.routes.update}/${id}`,
                                method: 'PUT',
                                payload: { name: this.$editInput.val() },
                                $form: this.$editForm,
                                beforeSend: (btn) => btn.html('<span class="spinner-border spinner-border-sm me-2"></span>Updating').prop('disabled', true),
                                onSuccess: () => {
                                    bootstrap.Modal.getInstance(this.editModalEl)?.hide();
                                    this.fetch(false);
                                },
                                onError: (message) => {
                                    this.$editError.text(message).show();
                                    this.$editInput.addClass('is-invalid');
                                },
                                onComplete: (btn) => btn.text('Save Changes').prop('disabled', false),
                            });
                        });

                        $(this.editModalEl).on('hidden.bs.modal', () => {
                            this.$editForm[0].reset();
                            this.$editInput.removeClass('is-invalid');
                            this.$editError.hide();
                        });

                        this.$tbody.on('click', '.delete-btn', (event) => {
                            if (!confirm(`Delete this ${this.config.singular.toLowerCase()}?`)) {
                                return;
                            }
                            const $btn = $(event.currentTarget);
                            const id = $btn.data('id');
                            const originalHtml = $btn.html();
                            $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span>');

                            $.ajax({
                                url: `${this.config.routes.delete}/${id}`,
                                type: 'DELETE',
                                data: { _token: this.csrf },
                            }).done(() => {
                                this.fetch(false);
                            }).fail(() => {
                                alert('Unable to delete right now. Please try again.');
                            }).always(() => {
                                $btn.prop('disabled', false).html(originalHtml);
                            });
                        });
                    }

                    submitForm({ url, method, payload, beforeSend, onSuccess, onError, onComplete, $form }) {
                        const $button = $form.find('button[type="submit"]');
                        beforeSend($button);

                        $.ajax({
                            url,
                            type: method,
                            data: { ...payload, _token: this.csrf },
                        }).done((response) => {
                            if (response?.success) {
                                onSuccess(response);
                            } else {
                                onError(response?.message || 'Something went wrong');
                            }
                        }).fail((xhr) => {
                            const message = xhr.responseJSON?.errors?.name?.[0] || 'Something went wrong';
                            onError(message);
                        }).always(() => {
                            onComplete($button);
                        });
                    }

                    fetch(showSpinner = true) {
                        if (showSpinner) {
                            this.$refresh.addClass('disabled').html('<span class="spinner-border spinner-border-sm me-2"></span>Refreshing');
                        }

                        $.getJSON(this.config.routes.json)
                            .done((items) => {
                                this.state.items = items || [];
                                this.state.page = 1;
                                this.render();
                            })
                            .fail(() => alert('Unable to load data right now. Please try again.'))
                            .always(() => {
                                this.$refresh.removeClass('disabled').html(`<i class="bi bi-arrow-repeat me-2"></i>${this.config.refreshLabel || 'Refresh'}`);
                            });
                    }

                    render() {
                        const filtered = this.state.items.filter(item =>
                            (item.name || '').toLowerCase().includes(this.state.search)
                        );

                        this.$count.text(new Intl.NumberFormat().format(this.state.items.length));

                        if (!filtered.length) {
                            this.$table.addClass('d-none');
                            this.$empty.removeClass('d-none');
                            this.$pagination.addClass('d-none');
                            return;
                        }

                        this.$table.removeClass('d-none');
                        this.$empty.addClass('d-none');

                        const totalPages = Math.max(1, Math.ceil(filtered.length / this.state.perPage));
                        if (this.state.page > totalPages) {
                            this.state.page = totalPages;
                        }
                        const start = (this.state.page - 1) * this.state.perPage;
                        const pageItems = filtered.slice(start, start + this.state.perPage);

                        this.$tbody.html(pageItems.map(item => this.renderRow(item)).join(''));
                        this.renderPagination(filtered.length, totalPages);
                    }

                    renderPagination(totalItems, totalPages) {
                        if (totalItems <= this.state.perPage) {
                            this.$pagination.addClass('d-none');
                            this.$pagination.empty();
                            return;
                        }

                        this.$pagination.removeClass('d-none').html(`
                            <button class="btn btn-sm btn-outline-secondary crud-prev" ${this.state.page === 1 ? 'disabled' : ''}>
                                <i class="bi bi-chevron-left"></i>
                            </button>
                            <span class="text-muted small">Page ${this.state.page} of ${totalPages}</span>
                            <button class="btn btn-sm btn-outline-secondary crud-next" ${this.state.page === totalPages ? 'disabled' : ''}>
                                <i class="bi bi-chevron-right"></i>
                            </button>
                        `);

                        this.$pagination.off('click').on('click', '.crud-prev', () => {
                            if (this.state.page > 1) {
                                this.state.page--;
                                this.render();
                            }
                        }).on('click', '.crud-next', () => {
                            if (this.state.page < totalPages) {
                                this.state.page++;
                                this.render();
                            }
                        });
                    }

                    renderRow(item) {
                        return `
                            <tr>
                                <td class="fw-semibold text-muted">#${item.id}</td>
                                <td class="fw-semibold text-dark">${item.name}</td>
                                <td class="text-end">
                                    <div class="btn-group" role="group">
                                        <button class="btn btn-sm btn-outline-secondary edit-btn btn-icon" data-id="${item.id}" data-name="${item.name}" title="Edit">
                                            <i class="bi bi-pencil-square"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-danger delete-btn btn-icon" data-id="${item.id}" title="Delete">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        `;
                    }

                }

                window.SetupCrud = SetupCrud;
            })();
        </script>
    @endonce
@endpush

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const root = document.querySelector('#{{ $slug }}-crud');
            if (!root) {
                return;
            }
            const config = JSON.parse(root.dataset.crudConfig);
            config.refreshLabel = '{{ $refreshLabel }}';
            new window.SetupCrud(config);
        });
    </script>
@endpush

