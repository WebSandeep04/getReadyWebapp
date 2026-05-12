@if($orders instanceof \Illuminate\Pagination\LengthAwarePaginator && $orders->total() > 0)
    <div class="d-flex align-items-center justify-content-between">
        <span class="text-muted small fw-bold">
            Showing {{ $orders->firstItem() }}-{{ $orders->lastItem() }} of {{ $orders->total() }}
        </span>
        <div class="btn-group shadow-sm">
            @if ($orders->onFirstPage())
                <button class="btn btn-sm btn-outline-dark border-0 bg-white" disabled style="width: 32px;">
                    <i class="bi bi-chevron-left"></i>
                </button>
            @else
                <a href="{{ $orders->previousPageUrl() }}" class="btn btn-sm btn-outline-dark border-0 bg-white" style="width: 32px;">
                    <i class="bi bi-chevron-left"></i>
                </a>
            @endif

            <span class="btn btn-sm btn-outline-dark border-0 bg-white disabled px-3 fw-bold">
                {{ $orders->currentPage() }} / {{ $orders->lastPage() }}
            </span>

            @if ($orders->hasMorePages())
                <a href="{{ $orders->nextPageUrl() }}" class="btn btn-sm btn-outline-dark border-0 bg-white" style="width: 32px;">
                    <i class="bi bi-chevron-right"></i>
                </a>
            @else
                <button class="btn btn-sm btn-outline-dark border-0 bg-white" disabled style="width: 32px;">
                    <i class="bi bi-chevron-right"></i>
                </button>
            @endif
        </div>
    </div>
@endif
