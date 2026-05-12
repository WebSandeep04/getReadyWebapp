<?php
use App\Models\Order;
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->handle(Illuminate\Http\Request::capture());

$orders = Order::with('items.cloth')->orderBy('id', 'desc')->take(4)->get();
foreach ($orders as $order) {
    echo "Order #{$order->id} (Status: {$order->status})\n";
    foreach ($order->items as $item) {
        $cloth = $item->cloth;
        echo "  Item: {$cloth->title} (Paid: {$item->price}, Rent: {$cloth->rent_price}, Sell: {$cloth->selling_price})\n";
    }
}
