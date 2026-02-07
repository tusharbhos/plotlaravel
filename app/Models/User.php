<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Hash;

class User extends Model
{
    use SoftDeletes;

    protected $table = 'users';
    protected $guarded = [];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'last_login_at' => 'datetime',
    ];

    /**
     * Hash password automatically on set
     */
    public function setPasswordAttribute($value)
    {
        $this->attributes['password'] = Hash::make($value);
    }

    /**
     * Check password
     */
    public function checkPassword($password)
    {
        return Hash::check($password, $this->password);
    }

    /**
     * Plots created by this user
     */
    public function plots()
    {
        return $this->hasMany(Plot::class, 'created_by');
    }

    /**
     * Get full name with role badge
     */
    public function getRoleLabel()
    {
        return match($this->role) {
            'admin' => 'Administrator',
            'user'  => 'User',
            default => ucfirst($this->role),
        };
    }
}