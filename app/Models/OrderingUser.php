<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderingUser extends Model
{
    protected $fillable = ['phone', 'email', 'name', 'is_verified', 'last_login_at'];

    protected $casts = [
        'is_verified' => 'boolean',
        'last_login_at' => 'datetime',
    ];
}
