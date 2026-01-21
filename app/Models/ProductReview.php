<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductReview extends Model
{
    use HasFactory;

    protected $fillable = [
        'cloth_id',
        'user_id',
        'rating',
        'review',
    ];

    protected $casts = [
        'rating' => 'integer',
    ];

    /**
     * Get the cloth that this review belongs to.
     */
    public function cloth()
    {
        return $this->belongsTo(Cloth::class);
    }

    /**
     * Get the user who wrote this review.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    /**
     * Get the replies for the review.
     */
    public function replies()
    {
        return $this->morphMany(Reply::class, 'repliable');
    }
}
