<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Brand extends Model
{
    protected $fillable = [
        'name',
        'logo',
    ];

    public function clothes()
    {
        return $this->hasMany(Cloth::class, 'brand_id');
    }
}
