<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MenuAttribute extends Model
{
    use SoftDeletes;

    protected $table = 'menu_attributes';

    protected $fillable = [
        'name',
        'status',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    public function values()
    {
        return $this->hasMany(MenuAttributeValue::class, 'attribute_id');
    }
}
