<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice</title>
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
        <h1>{{ isset($isExt) && $isExt ? 'Rental Extension Invoice' : 'Rent/Sale Invoice' }}</h1>
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
            <h3>Seller:</h3>
            <p>{{ $seller->name }}<br>
            {{ $seller->email }}<br>
            @if($seller->is_gst) GSTIN: {{ $seller->gst_number }} @endif</p>
        </div>
        <div style="float: right; width: 45%;">
            <h3>Buyer:</h3>
            <p>{{ $order->buyer->name }}<br>
            {{ $order->buyer->email }}</p>
        </div>
        <div style="clear: both;"></div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Item</th>
                <th>Base Amount</th>
                <th>GST (18%)</th>
                <th>Line Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($items as $item)
            @php
                $base = $item->base_rent;
                $tax = $item->is_seller_gst ? $item->rent_gst : 0;
            @endphp
            <tr>
                <td>{{ $item->cloth->title }}</td>
                <td>{{ number_format($base, 2) }}</td>
                <td>{{ number_format($tax, 2) }}</td>
                <td>{{ number_format($base + $tax, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <th colspan="3" style="text-align: right;">Total Amount:</th>
                <th>{{ number_format($totalAmount, 2) }}</th>
            </tr>
        </tfoot>
    </table>
    
    <p><em>This is a computer-generated invoice.</em></p>
</body>
</html>
