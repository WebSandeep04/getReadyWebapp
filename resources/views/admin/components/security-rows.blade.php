@forelse($orders as $order)
<tr>
    <td>
        <a href="{{ route('admin.orders') }}?search={{ $order->id }}" class="fw-bold text-dark text-decoration-none">
            #{{ $order->id }}
        </a>
        <div class="small text-muted">{{ $order->created_at->format('d M Y') }}</div>
    </td>
    <td>
        <div class="fw-semibold">{{ $order->buyer ? $order->buyer->name : 'Unknown' }}</div>
        <div class="small text-muted">{{ $order->buyer ? $order->buyer->phone : '-' }}</div>
    </td>
    <td>
        {{ $order->items->where('purchase_type', 'rent')->count() }} Item(s)
        @if($order->return_reason)
            <div class="small text-danger fw-bold"><i class="bi bi-exclamation-circle me-1"></i>Dispute Return</div>
        @endif
    </td>
    <td class="fw-bold">
        <div>₹{{ number_format($order->security_amount) }} <small class="text-muted fw-normal">(Deposit)</small></div>
        @if($order->return_reason)
            <div class="text-danger">+ ₹{{ number_format($order->total_amount - $order->security_amount) }} <small class="fw-normal">(Rental)</small></div>
            <div class="border-top mt-1 pt-1">Total: ₹{{ number_format($order->total_amount) }}</div>
        @endif
    </td>
    <td>
        @if($order->is_security_returned)
            <span class="badge badge-returned">Refunded</span>
            <div class="small text-muted">{{ $order->security_returned_at ? $order->security_returned_at->format('d M Y') : '-' }}</div>
        @elseif($order->status == 'Returned')
            <span class="badge badge-due">Pending Return</span>
        @else
            <span class="badge badge-held">Held</span>
        @endif
    </td>
    <td>
        <span class="badge bg-light text-dark border">{{ $order->status }}</span>
    </td>
    <td class="text-end">
        @if(!$order->is_security_returned)
            @if($order->status == 'Returned')
            <button class="btn btn-dark btn-sm btn-action mark-returned-btn" data-id="{{ $order->id }}">
                Mark Returned
            </button>
            @else
            <button class="btn btn-outline-secondary btn-sm btn-action" disabled>
                Wait for Return
            </button>
            @endif
        @else
            <span class="text-success small fw-bold"><i class="bi bi-check-all me-1"></i>Completed</span>
        @endif
    </td>
</tr>
@empty
<tr>
    <td colspan="7" class="text-center py-4 text-muted">No security deposits found matching criteria.</td>
</tr>
@endforelse
