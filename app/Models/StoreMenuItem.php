<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StoreMenuItem extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'store_id',
        'category_id',
        'product_id',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function store()
    {
        return $this->belongsTo(Store::class, 'store_id');
    }

    public function category()
    {
        return $this->belongsTo(MenuCategory::class, 'category_id');
    }

    public function product()
    {
        return $this->belongsTo(MenuProduct::class, 'product_id');
    }
}
