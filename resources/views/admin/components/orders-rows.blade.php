@forelse($orders as $order)
    @php
        $latestPayment = $order->payments->first();
        $isRental = (bool) $order->has_rental_items;
        $now = \Carbon\Carbon::now();
        $rentalEnd = $order->rental_to ? \Carbon\Carbon::parse($order->rental_to) : null;
        $isOverdue = $isRental && $rentalEnd && $rentalEnd->isPast() && !in_array($order->status, ['Returned', 'Cancelled']);
        $daysOverdue = $isOverdue ? (int) $rentalEnd->diffInDays($now) : null;
        $daysAhead = (!$isOverdue && $rentalEnd && $rentalEnd->isFuture()) ? (int) $now->diffInDays($rentalEnd) : null;
        $orderType = $order->has_rental_items && $order->has_purchase_items
            ? 'Mixed'
            : ($order->has_rental_items ? 'Rental' : 'Purchase');
    @endphp
    <tr class="{{ $isOverdue ? 'overdue-row' : '' }}">
        <td class="fw-semibold">GR-{{ str_pad($order->id, 5, '0', STR_PAD_LEFT) }}</td>
        <td>
            <div class="fw-semibold">{{ $order->buyer->name ?? 'Unknown' }}</div>
            <small class="text-muted">{{ $order->buyer->email ?? 'N/A' }}</small>
        </td>
        <td>
            <span class="order-type-badge badge {{ $orderType === 'Rental' ? 'bg-info text-dark' : ($orderType === 'Purchase' ? 'bg-success' : 'bg-primary') }}">
                {{ $orderType }}
            </span>
        </td>
        <td>₹{{ number_format($order->total_amount, 2) }}</td>
        <td>
            @if($order->has_rental_items)
                ₹{{ number_format($order->security_amount, 2) }}
            @else
                <span class="text-muted">—</span>
            @endif
        </td>
        <td>
            @if($order->has_rental_items && $order->rental_to)
                <div class="d-flex flex-column">
                    <span>{{ \Carbon\Carbon::parse($order->rental_from)->format('d/m/Y') }} – {{ \Carbon\Carbon::parse($order->rental_to)->format('d/m/Y') }}</span>
                    @if($isOverdue)
                        <span class="timeline-flag overdue"><i class="bi bi-exclamation-octagon"></i>Overdue by {{ $daysOverdue }}d</span>
                    @elseif($rentalEnd && $rentalEnd->isToday())
                        <span class="timeline-flag due-soon"><i class="bi bi-alarm"></i>Due today</span>
                    @elseif($daysAhead !== null)
                        <span class="timeline-flag due-soon"><i class="bi bi-hourglass-split"></i>Due in {{ $daysAhead }}d</span>
                    @else
                        <span class="timeline-flag completed"><i class="bi bi-check-circle"></i>Completed</span>
                    @endif
                </div>
            @else
                <span class="text-muted">N/A</span>
            @endif
        </td>
        <td>
            <span class="badge bg-{{ $order->status === 'Returned' ? 'success' : ($order->status === 'Cancelled' ? 'secondary' : 'warning text-dark') }}">
                {{ $order->status }}
            </span>
        </td>
        <td>
            @if($latestPayment)
                <div class="fw-semibold {{ $latestPayment->payment_status === 'Paid' ? 'text-success' : ($latestPayment->payment_status === 'Failed' ? 'text-danger' : 'text-muted') }}">
                    {{ $latestPayment->payment_status }}
                </div>
                <small class="text-muted">{{ $latestPayment->payment_method }}</small>
            @else
                <span class="text-muted">Unpaid</span>
            @endif
        </td>
        <td>{{ $order->created_at->format('d/m/Y, h:i A') }}</td>
        <td>
            <div class="d-flex gap-2">
                @if($order->buyer && $order->buyer->email)
                    <a href="mailto:{{ $order->buyer->email }}" class="btn btn-sm btn-outline-secondary" title="Email Buyer">
                        <i class="bi bi-envelope"></i>
                    </a>
                @endif
                
                @if($order->has_rental_items && $order->status !== 'Returned' && $order->status !== 'Cancelled')
                    <button class="btn btn-sm btn-outline-primary mark-returned-btn" 
                            data-order-id="{{ $order->id }}" 
                            title="Mark as Returned">
                        <i class="bi bi-box-arrow-in-left"></i>
                    </button>
                @endif
            </div>
        </td>
    </tr>
@empty
    <tr>
        <td colspan="10" class="text-center py-4 text-muted">
            No orders found for the selected filters.
        </td>
    </tr>
@endforelse

