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
        'quantity',
        'seller_id',
        'seller_name',
        'seller_phone',
        'seller_address',
        'seller_city',
        'seller_state',
        'seller_pincode',
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
        'converted_to_purchase_at',
        'conversion_amount',
    ];

    protected $casts = [
        'converted_to_purchase_at' => 'datetime',
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
