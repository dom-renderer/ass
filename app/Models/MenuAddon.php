<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MenuAddon extends Model
{
    use SoftDeletes;

    protected $table = 'menu_addons';

    protected $fillable = [
        'name',
        'price',
        'description',
        'status',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'status' => 'boolean',
    ];
}
