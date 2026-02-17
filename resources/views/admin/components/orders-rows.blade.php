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
        $shipmentMissing = !$order->shipments->where('type', 'forward')->first() && $order->status === 'Confirmed';
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
            @if($shipmentMissing)
                <i class="bi bi-exclamation-triangle-fill text-danger ms-1" data-bs-toggle="tooltip" title="Shipment missing"></i>
            @endif
        </td>
        <td>
            @if($latestPayment)
                <div class="fw-semibold {{ $latestPayment->payment_status === 'Paid' ? 'text-success' : (in_array($latestPayment->payment_status, ['Refunded', 'Partially Refunded']) ? 'text-info' : ($latestPayment->payment_status === 'Failed' ? 'text-danger' : 'text-muted')) }}">
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
                @if($order->invoices && $order->invoices->isNotEmpty())
                    <div class="dropdown">
                        <button class="btn btn-sm btn-outline-dark dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false" title="Download Invoices">
                            <i class="bi bi-file-earmark-pdf"></i>
                        </button>
                        <ul class="dropdown-menu">
                            @foreach($order->invoices as $inv)
                                <li><a class="dropdown-item" href="{{ route('invoices.download', $inv->id) }}" target="_blank">
                                    <small>{{ strtoupper(str_replace('_', ' ', $inv->type)) }}</small><br>
                                    {{ $inv->invoice_number }}
                                </a></li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                @if($order->buyer && $order->buyer->email)
                    <a href="mailto:{{ $order->buyer->email }}" class="btn btn-sm btn-outline-secondary" title="Email Buyer">
                        <i class="bi bi-envelope"></i>
                    </a>
                @endif
                
                @if($order->status === 'Pending' || $order->status === 'Confirmed' || $order->status === 'Order Confirmed & Shipment Created')
                    <button class="btn btn-sm btn-outline-success update-status-btn" 
                            data-order-id="{{ $order->id }}" 
                            data-status="{{ $order->status === 'Pending' ? 'Confirmed' : 'Delivered' }}"
                            title="Move to Next Status">
                        <i class="bi {{ $order->status === 'Pending' ? 'bi-check-circle' : 'bi-truck' }}"></i>
                    </button>
                @endif

                @if($order->has_rental_items && !in_array($order->status, ['Returned', 'Cancelled', 'Return Requested']) && in_array($order->status, ['Delivered', 'Return In Progress', 'Order Confirmed & Shipment Created', 'Confirmed', 'Shipped']))
                    <button class="btn btn-sm btn-outline-primary mark-returned-btn" 
                            data-order-id="{{ $order->id }}" 
                            title="Mark as Returned">
                        <i class="bi bi-box-arrow-in-left"></i>
                    </button>
                @endif

                @if($shipmentMissing)
                    <button class="btn btn-sm btn-outline-warning retry-shipment-btn" 
                            data-order-id="{{ $order->id }}" 
                            title="Retry Shipment Creation">
                        <i class="bi bi-arrow-clockwise"></i>
                    </button>
                @endif

                @if($order->status === 'Return Requested')
                    <button class="btn btn-sm btn-danger review-return-btn" 
                            data-order-id="{{ $order->id }}" 
                            data-reason="{{ $order->return_reason }}"
                            data-details="{{ $order->return_details }}"
                            data-images="{{ json_encode($order->return_images) }}"
                            title="Review Return Request">
                        <i class="bi bi-eye"></i>
                    </button>
                @endif

                @if($order->status === 'Returned' && $order->return_reason && !$order->is_security_returned)
                    <button class="btn btn-sm btn-dark process-issue-refund-btn" 
                            data-order-id="{{ $order->id }}" 
                            title="Process Full Refund (Rent + Security)">
                        <i class="bi bi-wallet2 me-1"></i>Refund All
                    </button>
                @endif

                @if($order->status === 'Returned' && !$order->has_rental_items && $latestPayment && in_array(strtolower($latestPayment->payment_status), ['paid', 'success']))
                    <button class="btn btn-sm btn-outline-danger refund-payment-btn" 
                            data-order-id="{{ $order->id }}" 
                            title="Refund Payment (Manual)">
                        <i class="bi bi-cash-stack"></i>
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
