<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Size extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'chest_bust', 'waist', 'length', 'shoulder', 'sleeve_length'];

    public function clothes()
    {
        return $this->hasMany(Cloth::class, 'size_id');
    }
}