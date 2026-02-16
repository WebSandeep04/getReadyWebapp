@forelse($orders as $order)
<tr>
    <td>
        #{{ $order->id }}<br>
        <small class="text-muted">{{ $order->created_at->format('d M Y') }}</small>
    </td>
    <td>
        <span class="fw-bold">{{ $order->seller_display_name }}</span><br>
        <small class="text-muted">Buyer: {{ $order->buyer ? $order->buyer->name : 'Unknown' }}</small>
    </td>
    <td class="fw-bold text-dark">
        ₹{{ number_format($order->total_seller_net, 2) }}
    </td>
    <td>
        @if($order->is_seller_paid)
            <span class="badge bg-success">Paid</span><br>
            <small class="text-muted">{{ $order->seller_paid_at->format('d M Y') }}</small>
        @elseif($order->status === 'Returned' || ($order->has_purchase_items && $order->status === 'Delivered'))
            <span class="badge bg-warning text-dark">Eligible for Payout</span>
        @else
            <span class="badge bg-light text-dark border">Order {{ $order->status }}</span>
        @endif
    </td>
    <td class="text-end">
        @if(!$order->is_seller_paid && ($order->status === 'Returned' || ($order->has_purchase_items && $order->status === 'Delivered')))
            <button class="btn btn-sm btn-dark" onclick="confirmSellerPayout({{ $order->id }}, {{ $order->total_seller_net }}, '{{ $order->seller_display_name }}')">
                Mark as Paid
            </button>
        @endif
        <a href="{{ route('admin.orders') }}?search={{ $order->id }}" class="btn btn-sm btn-outline-dark border-0">
            <i class="bi bi-eye"></i>
        </a>
    </td>
</tr>
@empty
<tr>
    <td colspan="5" class="text-center py-4 text-muted">No payouts found matching criteria.</td>
</tr>
@endforelse
