<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BodyTypeFit extends Model
{
    use HasFactory;

    protected $table = 'body_type_fits';
    protected $fillable = ['name'];

    public function clothes()
    {
        return $this->hasMany(Cloth::class, 'fit_type_id');
    }
}