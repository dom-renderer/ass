<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Promotion extends Model
{
    use SoftDeletes;

    public const TYPES = [
        'cart_flat',
        'cart_percent',
        'category_flat',
        'category_percent',
        'product_flat',
        'product_percent',
        'bxgy',
        'free_delivery',
        'first_order',
    ];

    protected $fillable = [
        'name', 'code', 'type', 'description', 'is_auto_apply', 'is_active', 'is_stackable',
        'is_global', 'priority', 'discount_value', 'max_discount_amount', 'min_cart_amount',
        'total_usage_limit', 'per_user_limit', 'buy_product_id', 'buy_quantity', 'get_product_id',
        'get_quantity', 'get_discount_percent', 'applicable_category_ids', 'applicable_product_ids',
        'starts_at', 'ends_at', 'meta',
    ];

    protected $casts = [
        'is_auto_apply' => 'boolean',
        'is_active' => 'boolean',
        'is_stackable' => 'boolean',
        'is_global' => 'boolean',
        'discount_value' => 'decimal:2',
        'max_discount_amount' => 'decimal:2',
        'min_cart_amount' => 'decimal:2',
        'get_discount_percent' => 'decimal:2',
        'applicable_category_ids' => 'array',
        'applicable_product_ids' => 'array',
        'meta' => 'array',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
    ];

    public function stores()
    {
        return $this->belongsToMany(Store::class, 'promotion_stores');
    }

    public function redemptions()
    {
        return $this->hasMany(PromotionRedemption::class);
    }
}
