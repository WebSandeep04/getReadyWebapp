<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VirtualTryOn extends Model
{
    protected $fillable = [
        'user_id',
        'cloth_id',
        'job_id',
        'user_image_path',
        'result_image_url',
        'status',
        'error_message',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function cloth()
    {
        return $this->belongsTo(Cloth::class);
    }
}
