<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderExtension extends Model
{
    protected $fillable = [
        'order_id',
        'old_rental_to',
        'new_rental_to',
        'extra_days',
        'additional_amount',
        'base_rent_amount',
        'buyer_commission',
        'seller_commission',
        'rent_gst',
        'buyer_commission_gst',
        'seller_commission_gst',
        'seller_net_amount',
        'payment_id',
        'status',
        'is_admin_override',
    ];

    protected $casts = [
        'old_rental_to' => 'date',
        'new_rental_to' => 'date',
        'is_admin_override' => 'boolean',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function payment()
    {
        return $this->belongsTo(Payment::class);
    }
}
