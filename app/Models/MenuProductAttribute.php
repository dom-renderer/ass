<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MenuProductAttribute extends Model
{
    use SoftDeletes;

    protected $table = 'menu_product_attributes';

    protected $fillable = [
        'product_id',
        'attribute_id',
        'attribute_value_id',
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

    public function attribute()
    {
        return $this->belongsTo(MenuAttribute::class, 'attribute_id');
    }

    public function attributeValue()
    {
        return $this->belongsTo(MenuAttributeValue::class, 'attribute_value_id');
    }
}
