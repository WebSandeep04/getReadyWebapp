<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AvailabilityBlock extends Model
{
    use HasFactory;

    protected $fillable = [
        'cloth_id',
        'start_date',
        'end_date',
        'type',
        'reason'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function cloth()
    {
        return $this->belongsTo(Cloth::class);
    }
} 