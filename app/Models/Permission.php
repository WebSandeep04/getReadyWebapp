<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    protected $fillable = ['name', 'label', 'module'];

    public function admins()
    {
        return $this->belongsToMany(AdminPanelUser::class, 'admin_user_permissions');
    }
}
