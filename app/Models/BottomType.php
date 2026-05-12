<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BottomType extends Model
{
    use HasFactory;

    protected $fillable = ['name'];

    public function clothes()
    {
        return $this->hasMany(Cloth::class, 'bottom_type_id');
    }
}