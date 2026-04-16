<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StoreQrCode extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'store_id',
        'table_number',
        'qr_label',
        'qr_url',
    ];

    protected $casts = [
        'table_number' => 'integer',
    ];

    public function store()
    {
        return $this->belongsTo(Store::class, 'store_id');
    }
}
