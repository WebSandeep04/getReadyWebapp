<div class="pagination-wrapper mt-5 mb-5 d-flex justify-content-center">
    <nav aria-label="Product Pagination">
        <ul class="pagination pagination-pro">
            {{-- Previous Page Link --}}
            @if ($clothes->onFirstPage())
                <li class="page-item disabled"><span class="page-link"><i class="bi bi-arrow-left-short"></i></span></li>
            @else
                <li class="page-item"><a class="page-link" href="{{ $clothes->previousPageUrl() }}" rel="prev"><i class="bi bi-arrow-left-short"></i></a></li>
            @endif

            {{-- Pagination Elements (Mocking if only 1 page) --}}
            @php 
                $lastPage = $clothes->lastPage() > 1 ? $clothes->lastPage() : 1;
                $urlRange = $clothes->lastPage() > 1 
                    ? $clothes->getUrlRange(max(1, $clothes->currentPage() - 2), min($clothes->lastPage(), $clothes->currentPage() + 2))
                    : [1 => '#'];
            @endphp

            @foreach ($urlRange as $page => $url)
                @if ($page == $clothes->currentPage())
                    <li class="page-item active"><span class="page-link">{{ $page }}</span></li>
                @else
                    <li class="page-item"><a class="page-link" href="{{ $url }}">{{ $page }}</a></li>
                @endif
            @endforeach

            {{-- Next Page Link --}}
            @if ($clothes->hasMorePages())
                <li class="page-item"><a class="page-link" href="{{ $clothes->nextPageUrl() }}" rel="next"><i class="bi bi-arrow-right-short"></i></a></li>
            @else
                <li class="page-item disabled"><span class="page-link"><i class="bi bi-arrow-right-short"></i></span></li>
            @endif
        </ul>
    </nav>
</div>

