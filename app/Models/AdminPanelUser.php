<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdminPanelUser extends Model
{
    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'role_id',
    ];

    protected $hidden = [
        'password',
    ];

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'admin_user_permissions');
    }

    public function hasPermission($permissionName)
    {
        // Check direct permissions
        if ($this->permissions()->where('name', $permissionName)->exists()) {
            return true;
        }

        // Check role-based permissions
        if ($this->role && $this->role->permissions()->where('name', $permissionName)->exists()) {
            return true;
        }

        return false;
    }
}
