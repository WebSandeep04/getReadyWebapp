<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Shipment extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'courier_name',
        'waybill_number',
        'tracking_url',
        'label_url',
        'reference_id',
        'status',
        'courier_response',
        'delivered_at',
    ];

    protected $casts = [
        'courier_response' => 'array',
        'delivered_at' => 'datetime',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
