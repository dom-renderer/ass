<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MenuOrderItem extends Model
{
    protected $fillable = [
        'menu_order_id', 'product_id', 'product_name', 'quantity', 'unit_price', 'line_total', 'addons', 'attributes',
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'line_total' => 'decimal:2',
        'addons' => 'array',
        'attributes' => 'array',
    ];

    public function order()
    {
        return $this->belongsTo(MenuOrder::class, 'menu_order_id');
    }

    public function product()
    {
        return $this->belongsTo(MenuProduct::class, 'product_id');
    }
}
