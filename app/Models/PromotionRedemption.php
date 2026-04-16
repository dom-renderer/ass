<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PromotionRedemption extends Model
{
    protected $fillable = [
        'promotion_id', 'store_id', 'user_id', 'order_reference', 'cart_amount', 'discount_amount', 'redeemed_at',
    ];

    protected $casts = [
        'cart_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'redeemed_at' => 'datetime',
    ];
}
