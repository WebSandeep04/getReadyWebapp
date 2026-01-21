<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClothImage extends Model
{
    use HasFactory;

    protected $fillable = [
        'cloth_id',
        'image_path',
    ];

    /**
     * Get the cloth that owns the image.
     */
    public function cloth()
    {
        return $this->belongsTo(Cloth::class);
    }
} 