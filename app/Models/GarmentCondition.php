<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GarmentCondition extends Model
{
    use HasFactory;

    protected $table = 'garment_conditions';
    protected $fillable = ['name'];

    public function clothes()
    {
        return $this->hasMany(Cloth::class, 'condition_id');
    }
} 