<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'buyer_id',
        'total_amount',
        'security_amount',
        'status',
        'delivery_address',
        'rental_from',
        'rental_to',
        'has_rental_items',
        'has_purchase_items',
    ];

    protected $casts = [
        'rental_from' => 'date',
        'rental_to' => 'date',
        'has_rental_items' => 'boolean',
        'has_purchase_items' => 'boolean',
    ];

    public function buyer()
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function shipment()
    {
        return $this->hasOne(Shipment::class);
    }
}

