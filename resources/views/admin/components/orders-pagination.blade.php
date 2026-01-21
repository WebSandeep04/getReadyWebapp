@if($orders instanceof \Illuminate\Pagination\LengthAwarePaginator)
    {{ $orders->links() }}
@endif

