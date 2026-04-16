<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class LocationAsset extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    public function store()
    {
        return $this->belongsTo(Store::class, 'location_id');
    }

    public function asset()
    {
        return $this->belongsTo(Store::class, 'asset_id')->withoutGlobalScope('os')->ass();
    }
}