<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BottomType extends Model
{
    use HasFactory;

    protected $table = 'bottom_types';
    protected $fillable = ['name'];
} 