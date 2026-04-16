<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MenuAttributeValue extends Model
{
    use SoftDeletes;

    protected $table = 'menu_attribute_values';

    protected $fillable = [
        'attribute_id',
        'value',
        'extra_price',
        'ordering',
    ];

    protected $casts = [
        'extra_price' => 'decimal:2',
        'ordering' => 'integer',
    ];

    public function attribute()
    {
        return $this->belongsTo(MenuAttribute::class, 'attribute_id');
    }
}
