<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reply extends Model
{
    use \Illuminate\Database\Eloquent\Factories\HasFactory;

    protected $fillable = [
        'user_id',
        'message',
        'repliable_id',
        'repliable_type',
    ];

    /**
     * Get the parent repliable model (question or review).
     */
    public function repliable()
    {
        return $this->morphTo();
    }

    /**
     * Get the user who posted the reply.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
