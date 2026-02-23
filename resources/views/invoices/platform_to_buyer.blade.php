<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Platform Fee Invoice</title>
    <style>
        body { font-family: sans-serif; }
        .invoice-header { margin-bottom: 20px; }
        .invoice-details { margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
    </style>
</head>
<body>
    <div class="invoice-header">
        <h1>{{ isset($isExt) && $isExt ? 'Platform Service Fee (Extension)' : 'Platform Service Fee Invoice' }}</h1>
        <p><strong>Invoice Number:</strong> {{ $invoiceNumber }}</p>
        <p><strong>Date:</strong> {{ date('Y-m-d') }}</p>
        @if(isset($isExt) && $isExt)
            <p><strong>Extension Period:</strong> {{ $extension->extra_days }} days (Until {{ $extension->new_rental_to->format('d M Y') }})</p>
        @endif
    </div>
    
    <div class="invoice-details">
        <div style="float: left; width: 45%;">
            <h3>From:</h3>
            <p>GetReady Platform<br>GetReady Ltd.<br>GSTIN: GR-GST-12345</p>
        </div>
        <div style="float: right; width: 45%;">
            <h3>To:</h3>
            <p>{{ $buyer->name }}<br>{{ $buyer->email }}</p>
        </div>
        <div style="clear: both;"></div>
    </div>
    
    <table>
        <thead>
            <tr>
                <th>Description</th>
                <th>Fee Amount</th>
                <th>Additional Service Fee</th>
                <th>GST (18%)</th>
                <th>Line Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($items as $item)
            @php
                $comm = $item->buyer_commission;
                $comm_gst = $item->buyer_commission_gst;
                $other_fee = !$item->is_seller_gst ? $item->rent_gst : 0;
            @endphp
            <tr>
                <td>Convenience Fee: {{ $item->cloth->title }}</td>
                <td>{{ number_format($comm, 2) }}</td>
                <td>{{ number_format($other_fee, 2) }}</td>
                <td>{{ number_format($comm_gst, 2) }}</td>
                <td>{{ number_format($comm + $comm_gst + $other_fee, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <th colspan="4" style="text-align: right;">Total Invoice Amount:</th>
                <th>{{ number_format($totalAmount, 2) }}</th>
            </tr>
        </tfoot>
    </table>
    
    <p><em>This is a computer-generated invoice.</em></p>
</body>
</html>
