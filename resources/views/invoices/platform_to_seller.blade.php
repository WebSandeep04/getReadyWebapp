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
            <p><strong>Extension Period:</strong> {{ $extension->extra_days }} days (Until {{ \Carbon\Carbon::parse($extension->new_rental_to)->format('d M Y') }})</p>
            <p><strong>New Return Date:</strong> {{ \Carbon\Carbon::parse($extension->new_rental_to)->addDay()->format('d M Y') }}</p>
        @else
            <p><strong>Rental Period:</strong> {{ \Carbon\Carbon::parse($order->rental_from)->format('d M Y') }} - {{ \Carbon\Carbon::parse($order->rental_to)->format('d M Y') }}</p>
            <p><strong>Return Date:</strong> {{ ($order->return_date ?: \Carbon\Carbon::parse($order->rental_to)->addDay())->format('d M Y') }}</p>
        @endif
    </div>
    
    <div class="invoice-details">
        <div style="float: left; width: 45%;">
            <h3>From:</h3>
            <p>GetReady Platform<br>GetReady Ltd.<br>GSTIN: GR-GST-12345</p>
        </div>
        <div style="float: right; width: 45%;">
            <h3>To:</h3>
            <p>{{ $seller->name }}<br>{{ $seller->email }}<br>
            @if($seller->is_gst) GSTIN: {{ $seller->gst_number }} @endif</p>
        </div>
        <div style="clear: both;"></div>
    </div>
    
    <table>
        <thead>
            <tr>
                <th>Description</th>
                <th>Fee Amount</th>
                <th>GST (18%)</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($items as $item)
            <tr>
                <td>Commission for Item: {{ $item->cloth->title }}</td>
                <td>{{ number_format($item->seller_commission, 2) }}</td>
                <td>{{ number_format($item->seller_commission_gst, 2) }}</td>
                <td>{{ number_format($item->seller_commission + $item->seller_commission_gst, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <th colspan="3" style="text-align: right;">Total Invoice Amount:</th>
                <th>{{ number_format($totalAmount, 2) }}</th>
            </tr>
        </tfoot>
    </table>
    
    @if($totalTcs > 0)
    <div style="border: 1px solid #ddd; padding: 10px; background: #f9f9f9;">
        <strong>Note:</strong> TCS of ₹{{ number_format($totalTcs, 2) }} has also been deducted from your payout as per government regulations.
    </div>
    @endif
    
    <p><em>This is a computer-generated invoice.</em></p>
</body>
</html>
