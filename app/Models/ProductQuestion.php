<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductQuestion extends Model
{
    use HasFactory;

    protected $fillable = [
        'cloth_id',
        'user_id',
        'question',
        'answer',
        'answered_by',
        'answered_at',
    ];

    protected $casts = [
        'answered_at' => 'datetime',
    ];

    /**
     * Get the cloth that this question belongs to.
     */
    public function cloth()
    {
        return $this->belongsTo(Cloth::class);
    }

    /**
     * Get the user who asked this question.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the user who answered this question.
     */
    public function answerer()
    {
        return $this->belongsTo(User::class, 'answered_by');
    }
    /**
     * Get the replies for the question.
     */
    public function replies()
    {
        return $this->morphMany(Reply::class, 'repliable');
    }
}
