<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MenuProductAddon extends Model
{
    use SoftDeletes;

    protected $table = 'menu_product_addons';

    protected $fillable = [
        'product_id',
        'addon_id',
        'price_override',
        'is_available',
        'is_default',
    ];

    protected $casts = [
        'price_override' => 'decimal:2',
        'is_available' => 'boolean',
        'is_default' => 'boolean',
    ];

    public function product()
    {
        return $this->belongsTo(MenuProduct::class, 'product_id');
    }

    public function addon()
    {
        return $this->belongsTo(MenuAddon::class, 'addon_id');
    }
}
