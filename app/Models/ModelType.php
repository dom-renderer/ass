<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class ModelType extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope('os', function (\Illuminate\Database\Eloquent\Builder $builder) {
            $builder->where('type', 0);
        });
    }

    public function scopeLoc($query)
    {
        return $query->where('type', 0);
    }

    public function scopeAss($query)
    {
        return $query->where('type', '!=', 0);
    }
}
