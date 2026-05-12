<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Color extends Model
{
    use HasFactory;

    protected $fillable = ['name'];

    public function clothes()
    {
        return $this->hasMany(Cloth::class, 'color_id');
    }
}