<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StoreMenuProductAddon extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'store_id',
        'product_id',
        'product_addon_id',
        'is_available',
        'is_default',
    ];

    protected $casts = [
        'is_available' => 'boolean',
        'is_default' => 'boolean',
    ];
}
