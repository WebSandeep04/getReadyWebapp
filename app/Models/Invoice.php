<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function issuedTo()
    {
        return $this->belongsTo(User::class, 'issued_to_id');
    }

    public function issuedBy()
    {
        return $this->belongsTo(User::class, 'issued_by_id');
    }
}
