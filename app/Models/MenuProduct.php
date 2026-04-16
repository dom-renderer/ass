<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MenuProduct extends Model
{
    use SoftDeletes;

    protected $table = 'menu_products';

    protected $fillable = [
        'category_id',
        'name',
        'slug',
        'description',
        'base_price',
        'image',
        'status',
        'ordering',
    ];

    protected $casts = [
        'base_price' => 'decimal:2',
        'status' => 'boolean',
        'ordering' => 'integer',
    ];

    public function category()
    {
        return $this->belongsTo(MenuCategory::class, 'category_id');
    }

    public function productAttributes()
    {
        return $this->hasMany(MenuProductAttribute::class, 'product_id');
    }

    public function productAddons()
    {
        return $this->hasMany(MenuProductAddon::class, 'product_id');
    }
}
