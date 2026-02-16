@extends('admin.layouts.app')

@section('title', 'Financial Report')
@section('page_title', 'Financial Report')

@push('styles')
<style>
/* Reusing core styles */
*, ::before, ::after { border-radius: 0 !important; }
.report-card {
    background: #fff;
    box-shadow: 0 8px 25px rgba(0,0,0,0.05);
    padding: 1.5rem;
    height: 100%;
    border: none;
}
.stat-box {
    padding: 1rem;
    background: #f8fafc;
    border: 1px solid #eee;
}
.stat-box__label { font-size: 0.7rem; font-weight: 700; color: #64748b; text-transform: uppercase; margin-bottom: 0.25rem; }
.stat-box__value { font-size: 1.5rem; font-weight: 800; color: #0f172a; }
</style>
@endpush

@section('content')
<div class="container-fluid mt-4">
    <div class="row g-4">
        <div class="col-12">
            <div class="report-card">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="fw-bold mb-0">Financial Overview</h5>
                    <div class="d-flex gap-2">
                        <input type="date" class="form-control form-control-sm" id="date_from">
                        <input type="date" class="form-control form-control-sm" id="date_to">
                        <button class="btn btn-dark btn-sm px-3">Filter</button>
                    </div>
                </div>

            

                <div class="row g-3">
                    <div class="col-md-3">
                        <div class="stat-box">
                            <div class="stat-box__label">Total Platform Revenue</div>
                            <div class="stat-box__value">₹{{ number_format($stats['total_revenue'], 2) }}</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-box">
                            <div class="stat-box__label">Total Security Held</div>
                            <div class="stat-box__value">₹{{ number_format($stats['total_security'], 2) }}</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-box">
                            <div class="stat-box__label">Total Payouts</div>
                            <div class="stat-box__value">₹{{ number_format($stats['total_payouts'], 2) }}</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-box">
                            <div class="stat-box__label">Net Platform Profit</div>
                            <div class="stat-box__value text-success">₹{{ number_format($stats['net_profit'], 2) }}</div>
                        </div>
                    </div>
                </div>

                <div class="table-responsive mt-4 overflow-auto border">
                    <table class="table table-bordered table-sm mb-0 text-center align-middle" style="min-width: 1800px; font-size: 0.7rem;">
                        <thead class="bg-light">
                            <tr>
                                <th rowspan="2" class="align-middle bg-white">ORDER ID / Date</th>
                                <th rowspan="2" class="align-middle bg-white">Type</th>
                                <th colspan="8" class="bg-dark text-white border-bottom-0">Basic Info</th>
                                <th colspan="4" class="bg-primary text-white border-bottom-0">Revenue</th>
                                <th colspan="4" class="bg-danger text-white border-bottom-0">Expenses</th>
                                <th rowspan="2" class="align-middle bg-success text-white">Net Profit</th>
                            </tr>
                            <tr class="small fw-bold">
                                {{-- Basic Info --}}
                                <th>MRP</th>
                                <th>Base rent</th>
                                <th>Rent GST</th>
                                <th>Security</th>
                                <th>Rent payable to seller</th>
                                <th>Rent receivable from buyer</th>
                                <th>Date on which amount is payable to seller</th>
                                <th>Date on which security is payable to buyer</th>

                                {{-- Revenue --}}
                                <th>Commission from seller</th>
                                <th>Commission from buyer</th>
                                <th>Return handling</th>
                                <th>Total</th>

                                {{-- Expenses --}}
                                <th>Payment gateway expense</th>
                                <th>Delivery cost</th>
                                <th>Fraud cost</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($reportData as $order)
                                @foreach($order['items'] as $item)
                                    <tr>
                                        @if($loop->first)
                                            <td rowspan="{{ count($order['items']) }}" class="fw-bold bg-white">
                                                GR-{{ str_pad($order['order_id'], 5, '0', STR_PAD_LEFT) }}<br>
                                                <small class="text-muted">{{ $order['created_at'] }}</small>
                                            </td>
                                        @endif
                                        <td>
                                            @if($item['is_purchase'])
                                                <span class="badge bg-success" style="font-size: 0.5rem;">Purchase</span>
                                            @else
                                                <span class="badge bg-primary" style="font-size: 0.5rem;">Rental</span>
                                            @endif
                                        </td>
                                        {{-- Basic Info --}}
                                        <td>₹{{ number_format($item['mrp'], 2) }}</td>
                                        <td>₹{{ number_format($item['base_price'], 2) }}</td>
                                        <td>₹{{ number_format($item['rent_gst'], 2) }}</td>
                                        <td>₹{{ number_format($item['security'], 2) }}</td>
                                        <td class="bg-light">₹{{ number_format($item['payable_to_seller'], 2) }}</td>
                                        <td class="bg-light">₹{{ number_format($item['receivable_from_buyer'], 2) }}</td>
                                        <td class="text-muted">{{ $item['payable_to_seller_date'] }}</td>
                                        <td class="text-muted">{{ $item['security_payable_date'] }}</td>
                                        
                                        {{-- Revenue --}}
                                        <td>₹{{ number_format($item['revenue_seller_comm'], 2) }}</td>
                                        <td>₹{{ number_format($item['revenue_buyer_comm'], 2) }}</td>
                                        <td>₹{{ number_format($item['return_handling'], 2) }}</td>
                                        <td class="fw-bold bg-primary-subtle">₹{{ number_format($item['total_revenue'], 2) }}</td>

                                        {{-- Expenses --}}
                                        <td>₹{{ number_format($item['exp_pg'], 2) }}</td>
                                        <td>₹{{ number_format($item['exp_delivery'], 2) }}</td>
                                        <td>₹{{ number_format($item['exp_fraud'], 2) }}</td>
                                        <td class="fw-bold bg-danger-subtle">₹{{ number_format($item['total_exp'], 2) }}</td>

                                        <td class="bg-success-subtle fw-bold">₹{{ number_format($item['net_profit'], 2) }}</td>
                                    </tr>
                                @endforeach
                            @empty
                                <tr>
                                    <td colspan="19" class="py-4 text-muted">No financial data found for the selected range.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
