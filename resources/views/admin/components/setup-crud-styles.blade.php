@push('styles')
    @once('setup-crud-styles')
        <style>
            .category-dashboard {
                background: linear-gradient(180deg, #f3f8ff 0%, #ffffff 100%);
                min-height: calc(100vh - 120px);
            }
            .glass-card {
                background: rgba(255, 255, 255, 0.9);
                border-radius: 24px;
                border: 1px solid rgba(255,255,255,0.6);
                box-shadow: 0 20px 50px rgba(82, 106, 194, 0.15);
                backdrop-filter: blur(8px);
            }
            .hero-card {
                background: linear-gradient(135deg, #2f57ef, #7b61ff);
            }
            .hero-card .btn-light {
                color: #2f57ef;
            }
            .btn-gradient {
                background: linear-gradient(135deg, #ff7a18, #af002d 71%);
                color: #fff;
                border: none;
                padding: 0.75rem 1.5rem;
                border-radius: 999px;
                box-shadow: 0 15px 30px rgba(255, 122, 24, 0.35);
                transition: all 0.2s ease;
            }
            .btn-gradient:hover {
                color: #fff;
                opacity: 0.9;
            }
            .modern-table thead {
                border-bottom: 1px solid rgba(0,0,0,0.05);
            }
            .modern-table tbody tr {
                transition: all 0.25s ease;
            }
            .modern-table tbody tr:hover {
                transform: translateX(4px);
                box-shadow: 0 8px 18px rgba(90,115,255,0.15);
            }
            .badge-pill-soft {
                border-radius: 999px;
                padding: 0.35rem 0.85rem;
                font-weight: 600;
            }
            .filters-wrapper .btn {
                border-radius: 999px;
                padding: 0.45rem 1.25rem;
                font-weight: 500;
                transition: all 0.2s ease;
            }
            .filters-wrapper .btn.active {
                background: linear-gradient(135deg, #2f57ef, #7b61ff);
                color: #fff;
            }
            .empty-illustration {
                width: 180px;
                opacity: 0.8;
            }
            .modal-modern .modal-content {
                border-radius: 24px;
                border: none;
                box-shadow: 0 30px 60px rgba(47, 87, 239, 0.25);
            }
            .modal-modern .modal-header {
                border-bottom: none;
                padding-bottom: 0;
            }
            .modal-modern .modal-footer {
                border-top: none;
            }
        </style>
    @endonce
@endpush

