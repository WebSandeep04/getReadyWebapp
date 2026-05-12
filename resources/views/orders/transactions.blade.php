@extends('layouts.app')

@section('title', 'My Transactions')

@section('styles')
<style>
    .badge-soft-success {
        background-color: rgba(40, 167, 69, 0.1);
        padding: 0.35rem 0.6rem;
        border-radius: 6px;
    }
    .badge-soft-danger {
        background-color: rgba(220, 53, 69, 0.1);
        padding: 0.35rem 0.6rem;
        border-radius: 6px;
    }
    .font-weight-bold {
        font-weight: 600;
    }
    .table th {
        font-weight: 600;
        background-color: #f8f9fa;
    }
</style>
@endsection

@section('content')
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1 text-warning">
                <i class="bi bi-credit-card me-2"></i>My Transactions
            </h2>
            <p class="text-muted mb-0">Review all payment records and transaction statuses.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('invoices.index') }}" class="btn btn-outline-dark btn-sm mr-2">
                <i class="bi bi-file-earmark-text me-1"></i>My Invoices
            </a>
            <a href="{{ route('orders.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-bag me-1"></i>My Orders
            </a>
        </div>
    </div>

    @if($payments->isEmpty())
        <div class="card shadow-sm">
            <div class="card-body text-center py-5">
                <i class="bi bi-payment text-muted" style="font-size:3rem;"></i>
                <h4 class="mt-3">No transactions found</h4>
                <p class="text-muted">Completed payments for orders and extensions will appear here.</p>
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
                                <th>Order ID</th>
                                <th>Type</th>
                                <th>Description</th>
                                <th>Transaction ID</th>
                                <th>Method</th>
                                <th>Status</th>
                                <th>Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($payments as $payment)
                                <tr>
                                    <td>{{ $payment->date->format('d M Y, h:i A') }}</td>
                                    <td>
                                        <a href="{{ route('orders.index') }}?search=GR-{{ str_pad($payment->order_id, 5, '0', STR_PAD_LEFT) }}" class="text-dark font-weight-bold">
                                            GR-{{ str_pad($payment->order_id, 5, '0', STR_PAD_LEFT) }}
                                        </a>
                                    </td>
                                    <td>
                                        @if($payment->type == 'credit')
                                            <span class="badge badge-soft-success text-success border-success">
                                                <i class="bi bi-arrow-down-left-circle mr-1"></i>Credit
                                            </span>
                                        @else
                                            <span class="badge badge-soft-danger text-danger border-danger">
                                                <i class="bi bi-arrow-up-right-circle mr-1"></i>Debit
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="small text-muted">{{ $payment->description ?? 'N/A' }}</span>
                                    </td>
                                    <td>
                                        <code class="text-muted small">{{ $payment->transaction_id ?? 'N/A' }}</code>
                                    </td>
                                    <td>
                                        <span class="text-capitalize small">
                                            @if(str_contains($payment->method, 'razorpay'))
                                                <i class="bi bi-shield-check text-primary mr-1"></i>
                                                {{ str_replace('_', ' ', $payment->method) }}
                                            @else
                                                {{ str_replace('_', ' ', $payment->method) }}
                                            @endif
                                        </span>
                                    </td>
                                    <td>
                                        @if($payment->status == 'Paid' || $payment->status == 'Success' || $payment->status == 'Confirmed')
                                            <span class="badge badge-success px-3 py-1 small">
                                                <i class="bi bi-check-circle mr-1"></i>{{ $payment->status }}
                                            </span>
                                        @elseif($payment->status == 'Refunded')
                                            <span class="badge badge-info px-3 py-1 small">
                                                <i class="bi bi-arrow-counterclockwise mr-1"></i>Refunded
                                            </span>
                                        @elseif($payment->status == 'Failed')
                                            <span class="badge badge-danger px-3 py-1 small">
                                                <i class="bi bi-x-circle mr-1"></i>Failed
                                            </span>
                                        @else
                                            <span class="badge badge-secondary px-3 py-1 small">{{ $payment->status }}</span>
                                        @endif
                                    </td>
                                    <td class="font-weight-bold {{ $payment->type == 'credit' ? 'text-success' : 'text-danger' }}">
                                        {{ $payment->type == 'credit' ? '+' : '-' }} ₹{{ number_format($payment->amount, 2) }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @if($payments->hasPages())
                <div class="card-footer bg-white">
                    {{ $payments->links() }}
                </div>
            @endif
        </div>
    @endif
</div>
@endsection

