<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CartItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'cloth_id',
        'quantity',
        'purchase_type',
        'rental_start_date',
        'rental_end_date',
        'total_rental_cost',
        'total_purchase_cost',
        'rental_days',
    ];

    protected $casts = [
        'rental_start_date' => 'date',
        'rental_end_date' => 'date',
        'total_rental_cost' => 'decimal:2',
        'total_purchase_cost' => 'decimal:2',
    ];

    /**
     * Get the user that owns the cart item.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the cloth that is in the cart.
     */
    public function cloth()
    {
        return $this->belongsTo(Cloth::class);
    }
}
