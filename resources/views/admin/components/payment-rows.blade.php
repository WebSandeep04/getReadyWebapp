@forelse($payments as $payment)
<tr>
    <td>
        <a href="{{ route('admin.orders') }}?search={{ $payment->order_id }}" class="fw-bold text-dark text-decoration-none">
            #{{ $payment->order_id }}
        </a>
        <div class="small text-muted">{{ $payment->created_at->format('d M Y, h:i A') }}</div>
    </td>
    <td>
        <div class="fw-semibold">{{ $payment->order && $payment->order->buyer ? $payment->order->buyer->name : 'Unknown' }}</div>
        <div class="small text-muted">{{ $payment->order && $payment->order->buyer ? $payment->order->buyer->phone : '-' }}</div>
    </td>
    <td>
        <div class="fw-bold">₹{{ number_format($payment->amount, 2) }}</div>
        <div class="small text-muted">{{ $payment->transaction_id ?? 'N/A' }}</div>
    </td>
    <td>
        <span class="badge bg-light text-dark border">{{ $payment->payment_method }}</span>
    </td>
    <td>
        @if(strtolower($payment->payment_status) == 'paid' || strtolower($payment->payment_status) == 'success')
            <span class="badge bg-success">Paid</span>
            @if($payment->paid_at)
                <div class="small text-muted mt-1">{{ $payment->paid_at->format('d M Y') }}</div>
            @endif
        @elseif(strtolower($payment->payment_status) == 'pending')
            <span class="badge bg-warning text-dark">Pending</span>
        @elseif(strtolower($payment->payment_status) == 'refunded')
            <span class="badge bg-info text-dark">Refunded</span>
        @elseif(strtolower($payment->payment_status) == 'cancelled')
            <span class="badge bg-secondary">Cancelled</span>
        @elseif(strtolower($payment->payment_status) == 'failed')
            <span class="badge bg-danger">Failed</span>
        @else
            <span class="badge bg-secondary">{{ $payment->payment_status }}</span>
        @endif
    </td>
    <td class="text-end">
        <!-- Actions: View Details (Link to Order) -->
        <a href="{{ route('admin.orders') }}?search={{ $payment->order_id }}" class="btn btn-sm btn-outline-dark border-0">
             <i class="bi bi-eye"></i> View Order
        </a>
    </td>
</tr>
@empty
<tr>
    <td colspan="6" class="text-center py-4 text-muted">No payments found matching criteria.</td>
</tr>
@endforelse
