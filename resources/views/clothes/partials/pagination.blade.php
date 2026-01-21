@if($clothes->hasPages())
    <div class="pagination-wrapper mt-4">
        {{ $clothes->links() }}
    </div>
@endif

