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
        'is_security_returned',
        'is_seller_paid',
        'seller_paid_at',
        'return_reason',
        'return_details',
        'return_images',
        'admin_rejection_reason',
    ];

    protected $casts = [
        'rental_from' => 'date',
        'rental_to' => 'date',
        'has_rental_items' => 'boolean',
        'has_purchase_items' => 'boolean',
        'security_returned_at' => 'datetime',
        'is_security_returned' => 'boolean',
        'is_seller_paid' => 'boolean',
        'seller_paid_at' => 'datetime',
        'return_images' => 'array',
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

    public function shipments()
    {
        return $this->hasMany(Shipment::class);
    }

    public function extensions()
    {
        return $this->hasMany(OrderExtension::class);
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }
}
