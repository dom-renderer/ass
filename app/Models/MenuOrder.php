<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MenuOrder extends Model
{
    public const STATUSES = [
        'received',
        'confirmed',
        'preparing',
        'ready',
        'served',
        'cancelled',
    ];

    protected $fillable = [
        'store_id', 'store_qr_code_id', 'ordering_user_id', 'table_number', 'order_number',
        'status', 'payment_method', 'coupon_code', 'promotion_id', 'sub_total', 'discount_total',
        'grand_total', 'meta',
    ];

    protected $casts = [
        'sub_total' => 'decimal:2',
        'discount_total' => 'decimal:2',
        'grand_total' => 'decimal:2',
        'meta' => 'array',
    ];

    public function items()
    {
        return $this->hasMany(MenuOrderItem::class);
    }

    public function store()
    {
        return $this->belongsTo(Store::class, 'store_id');
    }

    public function customer()
    {
        return $this->belongsTo(OrderingUser::class, 'ordering_user_id');
    }

    public function promotion()
    {
        return $this->belongsTo(Promotion::class, 'promotion_id');
    }
}
