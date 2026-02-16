<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'cloth_id',
        'price',
        'base_rent',
        'buyer_commission',
        'seller_commission',
        'rent_gst',
        'buyer_commission_gst',
        'seller_commission_gst',
        'tcs_amount',
        'is_seller_gst',
        'purchase_type',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function cloth()
    {
        return $this->belongsTo(Cloth::class);
    }
}
