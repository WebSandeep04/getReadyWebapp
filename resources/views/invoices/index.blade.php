@extends('layouts.app')

@section('title', 'My Invoices')

@section('content')
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1 text-warning">
                <i class="bi bi-file-earmark-text me-2"></i>My Invoices
            </h2>
            <p class="text-muted mb-0">View and download all your order and extension invoices.</p>
        </div>
        <a href="{{ route('orders.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-bag me-1"></i>My Orders
        </a>
    </div>

    @if($invoices->isEmpty())
        <div class="card shadow-sm">
            <div class="card-body text-center py-5">
                <i class="bi bi-file-earmark-x text-muted" style="font-size:3rem;"></i>
                <h4 class="mt-3">No invoices yet</h4>
                <p class="text-muted">Invoices are generated once your orders are confirmed and delivered.</p>
                <a href="{{ route('home') }}" class="btn btn-warning">
                    <i class="bi bi-bag-plus me-1"></i>Start Shopping
                </a>
            </div>
        </div>
    @else
        <div class="card shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle">
                        <thead class="table-light text-uppercase small text-muted">
                            <tr>
                                <th>Date</th>
                                <th>Invoice #</th>
                                <th>Order ID</th>
                                <th>Type</th>
                                <th>Amount</th>
                                <th class="text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($invoices as $invoice)
                                <tr>
                                    <td>{{ $invoice->created_at->format('d M Y') }}</td>
                                    <td class="font-weight-bold">{{ $invoice->invoice_number }}</td>
                                    <td>
                                        <a href="{{ route('orders.index') }}?search=GR-{{ str_pad($invoice->order_id, 5, '0', STR_PAD_LEFT) }}" class="text-dark">
                                            GR-{{ str_pad($invoice->order_id, 5, '0', STR_PAD_LEFT) }}
                                        </a>
                                    </td>
                                    <td>
                                        @if($invoice->type == 'rent_sale')
                                            <span class="badge badge-info">Tax Invoice (Items)</span>
                                        @elseif($invoice->type == 'platform_fee_buyer')
                                            <span class="badge badge-secondary">Service Fee (Platform)</span>
                                        @elseif($invoice->type == 'platform_fee_seller')
                                            <span class="badge badge-dark">Seller Fee</span>
                                        @else
                                            <span class="badge badge-light">{{ ucfirst(str_replace('_', ' ', $invoice->type)) }}</span>
                                        @endif
                                        
                                        @if($invoice->order_extension_id)
                                            <span class="badge badge-warning ml-1">Extension</span>
                                        @endif
                                    </td>
                                    <td class="font-weight-bold">₹{{ number_format($invoice->amount, 2) }}</td>
                                    <td class="text-right">
                                        <a href="{{ route('invoices.download', $invoice->id) }}" class="btn btn-sm btn-outline-dark">
                                            <i class="bi bi-download mr-1"></i>Download PDF
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @if($invoices->hasPages())
                <div class="card-footer bg-white">
                    {{ $invoices->links() }}
                </div>
            @endif
        </div>
    @endif
</div>
@endsection
