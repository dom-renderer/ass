<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MenuCategory extends Model
{
    use SoftDeletes;

    protected $table = 'menu_categories';

    protected $fillable = [
        'name',
        'slug',
        'description',
        'image',
        'status',
        'ordering',
    ];

    protected $casts = [
        'status' => 'boolean',
        'ordering' => 'integer',
    ];

    public function products()
    {
        return $this->hasMany(MenuProduct::class, 'category_id');
    }
}
